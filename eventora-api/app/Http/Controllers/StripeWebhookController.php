<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendOrderConfirmationJob;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch(\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch(SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            case 'checkout.session.expired':
                $session = $event->data->object;
                $this->handleCheckoutSessionExpired($session);
                break;
            default:
                // Unhandled event type
        }

        return response('Webhook handled', 200);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
        if (isset($session->metadata->order_id)) {
            $order = Order::with('attendees.ticket')->find($session->metadata->order_id);
            if ($order && $order->status === 'pending') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                // Confirm all attendees
                foreach ($order->attendees as $attendee) {
                    $attendee->update(['status' => 'confirmed']);
                }
                
                // Create payment record
                $order->payments()->create([
                    'organization_id' => $order->organization_id,
                    'gateway' => 'stripe',
                    'gateway_payment_id' => $session->payment_intent,
                    'amount' => $order->total,
                    'currency' => $order->currency,
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
                
                // Send confirmation email
                dispatch(new SendOrderConfirmationJob($order));
            }
        }
    }

    protected function handleCheckoutSessionExpired($session)
    {
        if (isset($session->metadata->order_id)) {
            $order = Order::with('attendees.ticket')->find($session->metadata->order_id);
            if ($order && $order->status === 'pending') {
                \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                    $order->update([
                        'status' => 'cancelled',
                    ]);
                    
                    $ticketCounts = [];
                    foreach ($order->attendees as $attendee) {
                        $attendee->update(['status' => 'cancelled']);
                        
                        $ticketId = $attendee->ticket_id;
                        if (!isset($ticketCounts[$ticketId])) {
                            $ticketCounts[$ticketId] = [
                                'ticket' => $attendee->ticket,
                                'count' => 0
                            ];
                        }
                        $ticketCounts[$ticketId]['count']++;
                    }
                    
                    foreach ($ticketCounts as $data) {
                        $ticket = $data['ticket'];
                        $ticket->decrement('quantity_sold', $data['count']);
                    }
                });
            }
        }
    }
}