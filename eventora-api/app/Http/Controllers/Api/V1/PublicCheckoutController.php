<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicCheckoutController extends Controller
{
    /**
     * POST /api/v1/events/{slug}/validate-coupon
     */
    public function validateCoupon(Request $request, $slug)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'buyer_email' => 'required|email'
        ]);

        $event = Event::where(function ($q) use ($slug) {
            $q->where('id', $slug)->orWhere('slug', $slug);
        })->firstOrFail();

        $coupon = Coupon::where('organization_id', $event->organization_id)
            ->where('code', strtoupper($request->coupon_code))
            ->first();

        if (!$coupon) {
            return response()->json(['message' => 'Invalid promo code.'], 422);
        }

        if (!$coupon->is_active) {
            return response()->json(['message' => 'Promo code is inactive.'], 422);
        }

        $now = now();
        if ($coupon->starts_at && $coupon->starts_at > $now) {
            return response()->json(['message' => 'Promo code is not yet active.'], 422);
        }

        if ($coupon->expires_at && $coupon->expires_at < $now) {
            return response()->json(['message' => 'Promo code has expired.'], 422);
        }

        if ($coupon->event_id && $coupon->event_id !== $event->id) {
            return response()->json(['message' => 'Promo code is not valid for this event.'], 422);
        }

        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['message' => 'Promo code has reached its usage limit.'], 422);
        }

        if ($coupon->min_order_amount && $request->subtotal < $coupon->min_order_amount) {
            return response()->json(['message' => 'Minimum order amount for this promo code is ' . number_format($coupon->min_order_amount, 2)], 422);
        }

        // Per-email validation
        $hasUsed = Order::where('buyer_email', $request->buyer_email)
            ->where('coupon_id', $coupon->id)
            ->whereIn('status', ['pending', 'paid'])
            ->exists();

        if ($hasUsed) {
            return response()->json(['message' => 'You have already used this promo code.'], 422);
        }

        $discount = 0;
        if ($coupon->type === 'percentage') {
            $discount = ($request->subtotal * $coupon->value) / 100;
        } else {
            $discount = $coupon->value;
        }

        if ($discount > $request->subtotal) {
            $discount = $request->subtotal;
        }

        return response()->json([
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount_amount' => (float) $discount,
            'new_total' => max(0, $request->subtotal - $discount)
        ]);
    }

    /**
     * POST /api/v1/events/{slug}/checkout — Purchase tickets
     */
    public function checkout(Request $request, $slug)
    {
        $event = Event::with('organization')
            ->where(function ($q) use ($slug) {
                $q->where('id', $slug)->orWhere('slug', $slug);
            })
            ->where('status', 'published')
            ->whereHas('organization', fn($q) => $q->where('is_active', true))
            ->firstOrFail();

        $organization = $event->organization;

        $validated = $request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'nullable|string|max:50',
            'coupon_code' => 'nullable|string|max:50',
            'tickets' => 'required|array',
            'tickets.*.ticket_id' => 'required|integer|exists:tickets,id',
            'tickets.*.quantity' => 'required|integer|min:1',
            'attendees' => 'required|array',
            'attendees.*.name' => 'required|string|max:255',
            'attendees.*.email' => 'required|email|max:255',
            'payment_method' => 'nullable|string|in:stripe,paypal,midtrans',
        ]);

        // Calculate total tickets
        $totalTickets = collect($validated['tickets'])->sum('quantity');
        
        if ($totalTickets > 100) {
            return response()->json(['message' => 'Maximum 100 tickets per transaction overall limit exceeded.'], 422);
        }
        if (count($validated['attendees']) !== $totalTickets) {
            return response()->json(['message' => 'Number of attendees must match total tickets.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($event, $organization, $validated, $request) {
                $totalAmount = 0;
                $orderTickets = [];

                // Sort tickets by ID to prevent deadlocks when locking multiple rows
                $sortedTickets = $validated['tickets'];
                usort($sortedTickets, fn($a, $b) => $a['ticket_id'] <=> $b['ticket_id']);

                $expiryTime = now()->addMinutes(30);
                $eventStart = \Carbon\Carbon::parse($event->start_date);
                if ($eventStart < $expiryTime) {
                    $expiryTime = $eventStart;
                }

                $now = now();

                foreach ($sortedTickets as $item) {
                    $ticket = $event->tickets()->lockForUpdate()->findOrFail($item['ticket_id']);
                    $qty = $item['quantity'];

                    // Validate Min/Max limits
                    if ($qty < $ticket->min_per_order || $qty > $ticket->max_per_order) {
                        throw new \Exception("Quantity for {$ticket->name} must be between {$ticket->min_per_order} and {$ticket->max_per_order}.");
                    }

                    // Validate Sales Dates
                    if ($ticket->sales_start && \Carbon\Carbon::parse($ticket->sales_start) > $now) {
                        throw new \Exception("Ticket sales for {$ticket->name} have not started yet.");
                    }

                    if ($ticket->sales_end && \Carbon\Carbon::parse($ticket->sales_end) < $now) {
                        throw new \Exception("Ticket sales for {$ticket->name} have ended.");
                    }

                    // Update expiry if ticket sales end earlier
                    $ticketSalesEnd = $ticket->sales_end ? \Carbon\Carbon::parse($ticket->sales_end) : null;
                    if ($ticketSalesEnd && $ticketSalesEnd < $expiryTime) {
                        $expiryTime = $ticketSalesEnd;
                    }

                    // Validate Availability
                    if ($ticket->quantity !== null) {
                        $available = $ticket->quantity - $ticket->quantity_sold;
                        if ($available < $qty) {
                            throw new \Exception("Only {$available} tickets remaining for {$ticket->name}.");
                        }
                    }

                    $totalAmount += ($ticket->price * $qty);
                    $orderTickets[] = ['ticket' => $ticket, 'quantity' => $qty];
                    $ticket->increment('quantity_sold', $qty);
                }

                // Coupon Validation & Discount Calculation
                $discountAmount = 0;
                $couponId = null;

                if (!empty($validated['coupon_code'])) {
                    $coupon = Coupon::where('organization_id', $organization->id)
                        ->where('code', strtoupper($validated['coupon_code']))
                        ->lockForUpdate()
                        ->first();

                    if (!$coupon) throw new \Exception('Invalid promo code.');
                    if (!$coupon->is_active) throw new \Exception('Promo code is inactive.');
                    if ($coupon->starts_at && $coupon->starts_at > $now) throw new \Exception('Promo code is not yet active.');
                    if ($coupon->expires_at && $coupon->expires_at < $now) throw new \Exception('Promo code has expired.');
                    if ($coupon->event_id && $coupon->event_id !== $event->id) throw new \Exception('Promo code is not valid for this event.');
                    if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) throw new \Exception('Promo code has reached its usage limit.');
                    if ($coupon->min_order_amount && $totalAmount < $coupon->min_order_amount) throw new \Exception('Minimum order amount for this promo code is ' . number_format($coupon->min_order_amount, 2));

                    // Per-email validation
                    $hasUsed = Order::where('buyer_email', $validated['buyer_email'])
                        ->where('coupon_id', $coupon->id)
                        ->whereIn('status', ['pending', 'paid'])
                        ->exists();

                    if ($hasUsed) throw new \Exception('You have already used this promo code.');

                    if ($coupon->type === 'percentage') {
                        $discountAmount = ($totalAmount * $coupon->value) / 100;
                    } else {
                        $discountAmount = $coupon->value;
                    }

                    if ($discountAmount > $totalAmount) {
                        $discountAmount = $totalAmount;
                    }

                    $couponId = $coupon->id;
                    $coupon->increment('used_count');
                }

                $taxAmount = 0;
                if ($event->is_taxable) {
                    $taxAmount = ($totalAmount - $discountAmount) * ($event->tax_rate / 100);
                }

                $serviceFee = 0;
                if ($totalAmount > 0) {
                    $platformFeePercent = (float) \App\Models\Setting::getVal('platform_fee_percent', env('PLATFORM_FEE_PERCENT', 5));
                    $platformFeeFixed = (float) \App\Models\Setting::getVal('platform_fee_fixed', 0);
                    $serviceFee = (($totalAmount - $discountAmount) * ($platformFeePercent / 100)) + $platformFeeFixed;
                }

                $total = ($totalAmount - $discountAmount) + $taxAmount + $serviceFee;

                // Auto-register Guest Users
                $userId = $request->user()?->id;
                if (!$userId) {
                    $existingUser = User::where('email', $validated['buyer_email'])->first();
                    if ($existingUser) {
                        $userId = $existingUser->id;
                    } else {
                        $tempPassword = Str::random(10);
                        $newUser = User::create([
                            'name' => $validated['buyer_name'],
                            'email' => $validated['buyer_email'],
                            'password' => Hash::make($tempPassword),
                        ]);
                        $userId = $newUser->id;
                        // To Do in future: Fire an event here to send a welcome email containing the temp password
                    }
                }

                $order = Order::create([
                    'organization_id' => $organization->id,
                    'event_id' => $event->id,
                    'user_id' => $userId,
                    'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                    'buyer_name' => $validated['buyer_name'],
                    'buyer_email' => $validated['buyer_email'],
                    'buyer_phone' => $validated['buyer_phone'] ?? null,
                    'subtotal' => $totalAmount,
                    'discount' => $discountAmount,
                    'tax' => $taxAmount,
                    'service_fee' => $serviceFee,
                    'total' => $total,
                    'coupon_id' => $couponId,
                    'status' => $total > 0 ? 'pending' : 'paid',
                    'currency' => $organization->currency,
                    'expires_at' => $total > 0 ? clone $expiryTime : null,
                ]);

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

                return $order;
            });

            $result->load(['attendees.ticket', 'event.organization']);
            
            $response = [
                'order' => new OrderResource($result)
            ];

            // Setup Payment Gateway if total > 0
            if ($result->total > 0) {
                $paymentMethod = $validated['payment_method'] ?? 'stripe';
                $response['payment_method'] = $paymentMethod;

                if ($paymentMethod === 'paypal') {
                    $paypalService = app(\App\Services\PayPalService::class);
                    $paypalOrder = $paypalService->createOrder($result->total, $organization->currency);
                    
                    $response['paypal_order_id'] = $paypalOrder['id'];
                    $response['approve_link'] = $paypalOrder['approve_link'];
                    
                } elseif ($paymentMethod === 'midtrans') {
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
                    
                    // Add discount, tax, service fee as separate items if needed, or Midtrans will auto-calculate gross_amount
                    if ($result->discount > 0) {
                        $itemDetails[] = ['id' => 'DISC', 'price' => -round($result->discount), 'quantity' => 1, 'name' => 'Discount'];
                    }
                    if ($result->tax > 0) {
                        $itemDetails[] = ['id' => 'TAX', 'price' => round($result->tax), 'quantity' => 1, 'name' => 'Tax'];
                    }
                    if ($result->service_fee > 0) {
                        $itemDetails[] = ['id' => 'FEE', 'price' => round($result->service_fee), 'quantity' => 1, 'name' => 'Service Fee'];
                    }

                    $snap = $midtransService->createTransaction($result, $customerDetails, $itemDetails);
                    
                    $response['midtrans_token'] = $snap['token'];
                    $response['midtrans_redirect_url'] = $snap['redirect_url'];

                } else {
                    // Stripe
                    if (!$organization->stripe_account_id) {
                        throw new \Exception('The event organizer cannot accept payments at this time.');
                    }

                    \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                    
                    $feeAmountCents = (int) ($result->service_fee * 100);

                    $paymentIntentParams = [
                        'amount' => (int) ($result->total * 100),
                        'currency' => strtolower($organization->currency),
                        'metadata' => [
                            'order_id' => $result->id,
                            'event_id' => $event->id,
                        ],
                    ];

                    $paymentIntentOptions = [];

                    // Only use Stripe Connect features if it's a real connected account (not our dummy test one)
                    if ($organization->stripe_account_id && $organization->stripe_account_id !== 'acct_123456789') {
                        $paymentIntentParams['application_fee_amount'] = $feeAmountCents;
                        $paymentIntentOptions['stripe_account'] = $organization->stripe_account_id;
                        $response['stripe_account'] = $organization->stripe_account_id;
                    }

                    $paymentIntent = \Stripe\PaymentIntent::create($paymentIntentParams, $paymentIntentOptions);

                    $response['client_secret'] = $paymentIntent->client_secret;
                    $response['publishable_key'] = env('STRIPE_KEY');
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/v1/orders/{orderNumber}/verify-payment — Verify mobile payment intent
     */
    public function verifyPayment(Request $request, $orderNumber)
    {
        $request->validate([
            'payment_intent_id' => 'nullable|string', // Nullable because Midtrans doesn't need it from client
            'payment_method' => 'nullable|string|in:stripe,paypal,midtrans',
        ]);

        $order = Order::with(['attendees', 'event.organization'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Order is already paid', 'order' => new OrderResource($order)]);
        }

        try {
            $paymentMethod = $request->payment_method ?? 'stripe';
            $isSuccessful = false;
            $gatewayPaymentId = $request->payment_intent_id;

            if ($paymentMethod === 'paypal') {
                $paypalService = app(\App\Services\PayPalService::class);
                $captureResult = $paypalService->capturePayment($request->payment_intent_id);
                
                if ($captureResult['status'] === 'COMPLETED') {
                    $isSuccessful = true;
                }
            } elseif ($paymentMethod === 'midtrans') {
                $midtransService = app(\App\Services\MidtransService::class);
                $status = $midtransService->getTransactionStatus($order->order_number);
                
                if ($status && in_array($status['transaction_status'], ['capture', 'settlement'])) {
                    $isSuccessful = true;
                    $gatewayPaymentId = $status['transaction_id'] ?? $order->order_number;
                }
            } else {
                // Stripe
                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                
                $stripeAccount = $order->event->organization->stripe_account_id;
                $paymentIntent = \Stripe\PaymentIntent::retrieve(
                    $request->payment_intent_id,
                    ($stripeAccount && $stripeAccount !== 'acct_123456789') ? ['stripe_account' => $stripeAccount] : []
                );

                if ($paymentIntent->status === 'succeeded') {
                    $isSuccessful = true;
                }
            }

            if ($isSuccessful) {
                DB::transaction(function () use ($order, $gatewayPaymentId, $paymentMethod) {
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
                            'gateway' => $paymentMethod,
                            'gateway_payment_id' => $gatewayPaymentId,
                            'amount' => $order->total,
                            'currency' => $order->currency,
                            'status' => 'completed',
                            'paid_at' => now(),
                        ]);
                    }
                });

                $order->refresh();
                return response()->json(['message' => 'Payment verified successfully', 'order' => new OrderResource($order)]);
            }

            return response()->json(['message' => 'Payment not successful yet'], 400);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error verifying payment: ' . $e->getMessage()], 422);
        }
    }

    /**
     * GET /api/v1/orders/{orderNumber} — Get order status
     */
    public function orderStatus($orderNumber)
    {
        $order = Order::with(['attendees.ticket', 'event.organization'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return new OrderResource($order);
    }
}
