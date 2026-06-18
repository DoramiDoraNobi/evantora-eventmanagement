<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Order;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicCheckoutController extends Controller
{
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
            'tickets' => 'required|array',
            'tickets.*.ticket_id' => 'required|integer|exists:tickets,id',
            'tickets.*.quantity' => 'required|integer|min:1|max:10',
            'attendees' => 'required|array',
            'attendees.*.name' => 'required|string|max:255',
            'attendees.*.email' => 'required|email|max:255',
        ]);

        // Calculate total tickets
        $totalTickets = collect($validated['tickets'])->sum('quantity');
        if ($totalTickets > 10) {
            return response()->json(['message' => 'Maximum 10 tickets per transaction.'], 422);
        }
        if (count($validated['attendees']) !== $totalTickets) {
            return response()->json(['message' => 'Number of attendees must match total tickets.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($event, $organization, $validated, $request) {
                $totalAmount = 0;
                $orderTickets = [];

                foreach ($validated['tickets'] as $item) {
                    $ticket = $event->tickets()->lockForUpdate()->findOrFail($item['ticket_id']);
                    $qty = $item['quantity'];

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

                $userId = $request->user()?->id;

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
                    'expires_at' => $totalAmount > 0 ? now()->addMinutes(30) : null,
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

            // Setup Stripe PaymentIntent if total > 0
            if ($result->total > 0) {
                if (!$organization->stripe_account_id) {
                    throw new \Exception('The event organizer cannot accept payments at this time.');
                }

                \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                
                $platformFeePercent = env('PLATFORM_FEE_PERCENT', 5);
                $feeAmountCents = (int) ($result->total * ($platformFeePercent / 100) * 100);

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
            'payment_intent_id' => 'required|string',
        ]);

        $order = Order::with(['attendees', 'event.organization'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Order is already paid', 'order' => new OrderResource($order)]);
        }

        try {
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            
            $stripeAccount = $order->event->organization->stripe_account_id;
            $paymentIntent = \Stripe\PaymentIntent::retrieve(
                $request->payment_intent_id,
                ($stripeAccount && $stripeAccount !== 'acct_123456789') ? ['stripe_account' => $stripeAccount] : []
            );

            if ($paymentIntent->status === 'succeeded') {
                DB::transaction(function () use ($order, $paymentIntent) {
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
                            'gateway_payment_id' => $paymentIntent->id,
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
