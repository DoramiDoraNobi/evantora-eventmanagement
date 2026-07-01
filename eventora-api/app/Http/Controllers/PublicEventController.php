<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Order;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendOrderConfirmationJob;
use Illuminate\Support\Str;

class PublicEventController extends Controller
{
            public function index(Request $request)
    {
        $query = Event::with(['organization', 'tickets'])
            ->where('status', 'published')
            ->whereHas('organization', function ($query) {
                $query->where('is_active', true);
            });

        // Filters
        $filter = $request->input('filter');
        $type = $request->input('type');
        $sort = $request->input('sort');

        if ($filter == 'today') {
            $query->whereDate('start_date', now()->toDateString());
        } elseif ($filter == '14_days') {
            $query->whereDate('start_date', '>=', now()->toDateString())
                  ->whereDate('start_date', '<=', now()->addDays(14)->toDateString());
        } elseif ($filter == '30_days') {
            $query->whereDate('start_date', '>=', now()->toDateString())
                  ->whereDate('start_date', '<=', now()->addDays(30)->toDateString());
        } else {
            $query->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($sort == 'latest') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('start_date', 'asc');
        }

        $events = $query->paginate(6)->withQueryString();

        $stats = [
            'events' => Event::where('status', 'published')->count(),
            'organizers' => Organization::count(),
            'tickets' => Attendee::count(),
            'orders' => Order::count(),
        ];

        return view('public.event.index', compact('events', 'stats'));
    }

    public function organizationProfile($organizationSlug)
    {
        $organization = Organization::where('slug', $organizationSlug)->where('is_active', true)->firstOrFail();
        $events = $organization->events()->where('status', 'published')->orderBy('start_date', 'asc')->paginate(12);
        return view('public.organization.show', compact('organization', 'events'));
    }

    public function show($organizationSlug, $eventSlug)
    {
        $organization = Organization::where('slug', $organizationSlug)->where('is_active', true)->firstOrFail();
        $event = $organization->events()->where('slug', $eventSlug)->where('status', 'published')->firstOrFail();
        
        $tickets = $event->tickets()->where('is_active', true)->orderBy('sort_order')->get();
        
        return view('public.event.show', compact('organization', 'event', 'tickets'));
    }

    public function checkout(Request $request, $organizationSlug, $eventSlug)
    {
        $organization = Organization::where('slug', $organizationSlug)->where('is_active', true)->firstOrFail();
        $event = $organization->events()->where('slug', $eventSlug)->where('status', 'published')->firstOrFail();
        
        // Process selected tickets from the landing page
        $selectedTickets = $request->input('tickets', []);
        $ticketsToBuy = [];
        $totalAmount = 0;
        
        foreach ($selectedTickets as $ticketId => $quantity) {
            if ($quantity > 0) {
                $ticket = $event->tickets()->find($ticketId);
                if ($ticket) {
                    $ticketsToBuy[] = [
                        'ticket' => $ticket,
                        'quantity' => $quantity
                    ];
                    $totalAmount += ($ticket->price * $quantity);
                }
            }
        }
        
        if (empty($ticketsToBuy)) {
            return redirect()->back()->with('error', 'Please select at least one ticket.');
        }

        return view('public.event.checkout', compact('organization', 'event', 'ticketsToBuy', 'totalAmount'));
    }

    public function processCheckout(Request $request, $organizationSlug, $eventSlug)
    {
        $organization = Organization::where('slug', $organizationSlug)->where('is_active', true)->firstOrFail();
        $event = $organization->events()->where('slug', $eventSlug)->where('status', 'published')->firstOrFail();
        
        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'nullable|string|max:50',
            'tickets' => 'required|array',
            'tickets.*' => 'integer|min:0|max:10', // Max 10 per ticket type
            'attendees' => 'required|array',
            'attendees.*.name' => 'required|string|max:255',
            'attendees.*.email' => 'required|email|max:255',
            'payment_method' => 'nullable|string|in:midtrans,stripe,paypal',
        ]);

        $totalRequestedTickets = 0;
        foreach ($validated['tickets'] as $quantity) {
            $totalRequestedTickets += $quantity;
        }

        if ($totalRequestedTickets === 0) {
            return redirect()->back()->with('error', 'Please select at least one ticket.');
        }
        
        if ($totalRequestedTickets > 10) {
            return redirect()->back()->with('error', 'You can only purchase a maximum of 10 tickets per transaction.');
        }

        if (count($validated['attendees']) !== $totalRequestedTickets) {
            return redirect()->back()->with('error', 'The number of attendees provided does not match the number of tickets requested.');
        }

        $userId = null;
        if (auth()->check()) {
            $userId = auth()->id();
        } elseif ($request->create_account) {
            $request->validate([
                'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            ]);
            $user = \App\Models\User::create([
                'name' => $validated['buyer_name'],
                'email' => $validated['buyer_email'],
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            ]);
            \Illuminate\Support\Facades\Auth::login($user);
            $userId = $user->id;
        }

        try {
            $checkoutData = \Illuminate\Support\Facades\DB::transaction(function () use ($organization, $event, $validated, $userId) {
                $totalAmount = 0;
                $orderTickets = [];
                $expiresAt = now()->addMinutes(30);

                foreach ($validated['tickets'] as $ticketId => $quantity) {
                    if ($quantity > 0) {
                        // Lock the row for update to prevent overselling
                        $ticket = $event->tickets()->lockForUpdate()->findOrFail($ticketId);
                        
                        if ($ticket->quantity !== null) {
                            $available = $ticket->quantity - $ticket->quantity_sold;
                            if ($available < $quantity) {
                                throw new \Exception("Sorry, only {$available} tickets remaining for {$ticket->name}.");
                            }
                        }

                        $totalAmount += ($ticket->price * $quantity);
                        $orderTickets[] = ['ticket' => $ticket, 'quantity' => $quantity];
                        
                        // Increment sold quantity (reserve it)
                        $ticket->increment('quantity_sold', $quantity);
                    }
                }

                // Create Order
                $order = Order::create([
                    'organization_id' => $organization->id,
                    'event_id' => $event->id,
                    'user_id' => $userId,
                    'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                    'buyer_name' => $validated['buyer_name'],
                    'buyer_email' => $validated['buyer_email'],
                    'buyer_phone' => $validated['buyer_phone'] ?? null,
                    'subtotal' => $totalAmount,
                    'total' => $totalAmount,
                    'status' => $totalAmount > 0 ? 'pending' : 'paid',
                    'currency' => $organization->currency,
                    'expires_at' => $totalAmount > 0 ? $expiresAt : null,
                ]);

                // Create Attendees
                $attendeeIndex = 0;
                foreach ($orderTickets as $ot) {
                    for ($i = 0; $i < $ot['quantity']; $i++) {
                        $attendeeData = $validated['attendees'][$attendeeIndex];
                        
                        Attendee::create([
                            'organization_id' => $organization->id,
                            'event_id' => $event->id,
                            'order_id' => $order->id,
                            'ticket_id' => $ot['ticket']->id,
                            'ticket_number' => 'TKT-' . strtoupper(Str::random(12)),
                            'qr_code' => Str::uuid()->toString(),
                            'name' => $attendeeData['name'],
                            'email' => $attendeeData['email'],
                            'status' => $order->status === 'paid' ? 'confirmed' : 'registered',
                        ]);
                        
                        $attendeeIndex++;
                    }
                }

                return [
                    'order' => $order,
                    'orderTickets' => $orderTickets,
                    'totalAmount' => $totalAmount
                ];
            });
            
            $order = $checkoutData['order'];
            $orderTickets = $checkoutData['orderTickets'];
            $totalAmount = $checkoutData['totalAmount'];

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        // If total is 0 (Free tickets), redirect to success
        if ($totalAmount == 0) {
            return redirect()->route('public.order.success', $order->order_number);
        }

        // --- PAYMENT GATEWAY INTEGRATION ---
        $paymentMethod = $validated['payment_method'] ?? 'midtrans';

        if ($paymentMethod === 'midtrans') {
            try {
                $midtransService = app(\App\Services\MidtransService::class);
                
                $customerDetails = [
                    'first_name' => $validated['buyer_name'],
                    'email' => $validated['buyer_email'],
                    'phone' => $validated['buyer_phone'] ?? '08111111111',
                ];
                
                $itemDetails = [];
                foreach ($orderTickets as $ot) {
                    $itemDetails[] = [
                        'id' => $ot['ticket']->id,
                        'price' => round($ot['ticket']->price),
                        'quantity' => $ot['quantity'],
                        'name' => substr($ot['ticket']->name, 0, 50),
                    ];
                }

                $snap = $midtransService->createTransaction($order, $customerDetails, $itemDetails);
                
                return redirect($snap['redirect_url']);
            } catch (\Exception $e) {
                // Un-reserve tickets manually
                foreach ($orderTickets as $ot) {
                    $ot['ticket']->decrement('quantity_sold', $ot['quantity']);
                }
                $order->delete();
                return redirect()->back()->with('error', 'Midtrans Error: ' . $e->getMessage());
            }
        } elseif ($paymentMethod === 'paypal') {
             try {
                $paypalService = app(\App\Services\PayPalService::class);
                $paypalOrder = $paypalService->createOrder($order->total, $organization->currency);
                // Redirect user to PayPal approve link
                return redirect($paypalOrder['approve_link']);
             } catch (\Exception $e) {
                 foreach ($orderTickets as $ot) {
                    $ot['ticket']->decrement('quantity_sold', $ot['quantity']);
                }
                $order->delete();
                return redirect()->back()->with('error', 'PayPal Error: ' . $e->getMessage());
             }
        }

        // --- STRIPE CONNECT INTEGRATION ---
        if (!$organization->stripe_account_id) {
            // Un-reserve the tickets manually since we're outside transaction and aborting
            foreach ($orderTickets as $ot) {
                $ot['ticket']->decrement('quantity_sold', $ot['quantity']);
            }
            $order->delete();
            return redirect()->back()->with('error', 'The event organizer cannot accept payments via Stripe at this time. Please contact them.');
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];
        foreach ($orderTickets as $ot) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($organization->currency),
                    'product_data' => [
                        'name' => $ot['ticket']->name . ' - ' . $event->title,
                    ],
                    'unit_amount' => (int) ($ot['ticket']->price * 100), // Stripe expects cents
                ],
                'quantity' => $ot['quantity'],
            ];
        }

        // Calculate Application Fee
        $platformFeePercent = env('PLATFORM_FEE_PERCENT', 5);
        $feeAmountCents = (int) ($totalAmount * ($platformFeePercent / 100) * 100);

        try {
            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('public.order.success', $order->order_number) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('public.event.show', ['organizationSlug' => $organization->slug, 'eventSlug' => $event->slug]),
                'payment_intent_data' => [
                    'application_fee_amount' => $feeAmountCents,
                ],
                'expires_at' => $order->expires_at->timestamp, // Set Stripe Expiration
                'metadata' => [
                    'order_id' => $order->id,
                    'event_id' => $event->id,
                ],
            ], [
                'stripe_account' => $organization->stripe_account_id,
            ]);

            return redirect($checkoutSession->url);

        } catch (\Exception $e) {
            // Un-reserve tickets manually
            foreach ($orderTickets as $ot) {
                $ot['ticket']->decrement('quantity_sold', $ot['quantity']);
            }
            $order->delete();
            return redirect()->back()->with('error', 'Payment gateway error: ' . $e->getMessage());
        }
    }

    public function orderSuccess(Request $request, $orderNumber)
    {
        $order = Order::with('attendees.ticket', 'event.organization')->where('order_number', $orderNumber)->firstOrFail();
        
        $sessionId = $request->query('session_id');
        $midtransOrderId = $request->query('order_id');
        $paypalToken = $request->query('token');

        if ($sessionId && $order->status === 'pending') {
            // Stripe Verification
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                
                $stripeAccount = $order->event->organization->stripe_account_id;
                
                if ($stripeAccount) {
                    $session = \Stripe\Checkout\Session::retrieve($sessionId, [
                        'stripe_account' => $stripeAccount
                    ]);
                } else {
                    $session = \Stripe\Checkout\Session::retrieve($sessionId);
                }

                if ($session && $session->payment_status === 'paid') {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order, $session) {
                        $order->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        
                        foreach ($order->attendees as $attendee) {
                            $attendee->update(['status' => 'confirmed']);
                        }
                        
                        if (!$order->payments()->exists()) {
                            $order->payments()->create([
                                'organization_id' => $order->organization_id,
                                'gateway' => 'stripe',
                                'gateway_payment_id' => $session->payment_intent,
                                'amount' => $order->total,
                                'currency' => $order->currency,
                                'status' => 'completed',
                                'paid_at' => now(),
                            ]);
                        }
                    });
                    
                    // Send confirmation email
                    dispatch(new SendOrderConfirmationJob($order));
                    
                    $order->refresh();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Stripe Sync Error: ' . $e->getMessage());
            }
        }
        
        if ($order->status === 'pending' && $midtransOrderId) {
            // Midtrans Verification
            try {
                $midtransService = app(\App\Services\MidtransService::class);
                $status = $midtransService->getTransactionStatus($order->order_number);
                
                if ($status && in_array($status['transaction_status'], ['capture', 'settlement'])) {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($order, $status) {
                        $order->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        
                        foreach ($order->attendees as $attendee) {
                            $attendee->update(['status' => 'confirmed']);
                        }
                        
                        if (!$order->payments()->exists()) {
                            $order->payments()->create([
                                'organization_id' => $order->organization_id,
                                'gateway' => 'midtrans',
                                'gateway_payment_id' => $status['transaction_id'] ?? $order->order_number,
                                'amount' => $order->total,
                                'currency' => $order->currency,
                                'status' => 'completed',
                                'paid_at' => now(),
                            ]);
                        }
                    });
                    
                    dispatch(new SendOrderConfirmationJob($order));
                    $order->refresh();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Midtrans Sync Error: ' . $e->getMessage());
            }
        }

        if ($order->status === 'pending' && $paypalToken) {
            // PayPal Verification
            try {
                $paypalService = app(\App\Services\PayPalService::class);
                $captureResult = $paypalService->capturePayment($paypalToken);
                
                if ($captureResult['status'] === 'COMPLETED') {
                     \Illuminate\Support\Facades\DB::transaction(function () use ($order, $paypalToken) {
                        $order->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        
                        foreach ($order->attendees as $attendee) {
                            $attendee->update(['status' => 'confirmed']);
                        }
                        
                        if (!$order->payments()->exists()) {
                            $order->payments()->create([
                                'organization_id' => $order->organization_id,
                                'gateway' => 'paypal',
                                'gateway_payment_id' => $paypalToken,
                                'amount' => $order->total,
                                'currency' => $order->currency,
                                'status' => 'completed',
                                'paid_at' => now(),
                            ]);
                        }
                    });
                    
                    dispatch(new SendOrderConfirmationJob($order));
                    $order->refresh();
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PayPal Sync Error: ' . $e->getMessage());
            }
        }
        
        return view('public.event.success', compact('order'));
    }

    public function verifyTicket($qrCode)
    {
        $attendee = Attendee::with('event.organization', 'ticket')->where('qr_code', $qrCode)->firstOrFail();
        
        $isOrganizer = false;
        $checkinSuccess = false;
        $message = '';
        
        if (auth()->check()) {
            $user = auth()->user();
            $isOrganizer = \Illuminate\Support\Facades\DB::table('organization_user')
                ->where('organization_id', $attendee->organization_id)
                ->where('user_id', $user->id)
                ->exists() || $user->role === 'admin';
                
            if ($isOrganizer && $attendee->status === 'confirmed') {
                \Illuminate\Support\Facades\DB::transaction(function () use ($attendee, $user) {
                    $attendee->update([
                        'status' => 'checked_in',
                        'checked_in_at' => now(),
                    ]);
                    
                    \Illuminate\Support\Facades\DB::table('checkin_logs')->insert([
                        'attendee_id' => $attendee->id,
                        'event_id' => $attendee->event_id,
                        'organization_id' => $attendee->organization_id,
                        'checked_in_by' => $user->id,
                        'action' => 'checkin',
                        'method' => 'qr_scan',
                        'device_info' => request()->header('User-Agent'),
                    ]);
                });
                
                $attendee->refresh();
                $checkinSuccess = true;
                $message = 'Check-in Berhasil!';
            }
        }
        
        return view('public.event.verify', compact('attendee', 'isOrganizer', 'checkinSuccess', 'message'));
    }
    
    public function downloadTicket($ticketNumber)
    {
        $attendee = Attendee::with('event.organization', 'ticket')->where('ticket_number', $ticketNumber)->firstOrFail();
        return view('public.event.ticket', compact('attendee'));
    }
}
