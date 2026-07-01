<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    protected $clientId;
    protected $secret;
    protected $mode;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = Setting::where('key', 'paypal_client_id')->value('value');
        $this->secret = Setting::where('key', 'paypal_secret')->value('value');
        $this->mode = Setting::where('key', 'paypal_mode')->value('value') ?? 'sandbox';
        
        $this->baseUrl = $this->mode === 'sandbox' 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';
    }

    /**
     * Get PayPal Access Token
     */
    protected function getAccessToken()
    {
        if (empty($this->clientId) || empty($this->secret)) {
            throw new \Exception('PayPal credentials are not configured.');
        }

        $response = Http::asForm()
            ->withBasicAuth($this->clientId, $this->secret)
            ->post($this->baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->successful()) {
            return $response->json('access_token');
        }

        Log::error('PayPal Auth Failed', ['response' => $response->body()]);
        throw new \Exception('Failed to authenticate with PayPal.');
    }

    /**
     * Create an Order for Checkout
     * 
     * @return array ['id' => 'ORDER_ID', 'approve_link' => 'https://...']
     */
    public function createOrder($amount, $currency = 'USD')
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', '')
                        ]
                    ]
                ]
            ]);

        if ($response->successful()) {
            $data = $response->json();
            $approveLink = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? null;

            return [
                'id' => $data['id'],
                'approve_link' => $approveLink
            ];
        }

        Log::error('PayPal Create Order Failed', ['response' => $response->body()]);
        throw new \Exception('Failed to create PayPal order.');
    }

    /**
     * Capture Payment for an Order
     * 
     * @param string $orderId
     * @return array
     */
    public function capturePayment($orderId)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->baseUrl . "/v2/checkout/orders/{$orderId}/capture");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PayPal Capture Failed', ['order_id' => $orderId, 'response' => $response->body()]);
        throw new \Exception('Failed to capture PayPal payment.');
    }

    /**
     * Send Payout to Organizer
     * 
     * @param string $receiverEmail
     * @param float $amount
     * @param string $currency
     * @param string $note
     * @return array
     */
    public function sendPayout($receiverEmail, $amount, $currency = 'USD', $note = 'Payout from Eventora')
    {
        $token = $this->getAccessToken();
        
        $payoutBatchId = 'Payout_' . uniqid();

        $response = Http::withToken($token)
            ->post($this->baseUrl . '/v1/payments/payouts', [
                'sender_batch_header' => [
                    'sender_batch_id' => $payoutBatchId,
                    'email_subject' => 'You have a payout!',
                    'email_message' => $note
                ],
                'items' => [
                    [
                        'recipient_type' => 'EMAIL',
                        'amount' => [
                            'value' => number_format($amount, 2, '.', ''),
                            'currency' => $currency
                        ],
                        'note' => $note,
                        'sender_item_id' => 'item_' . uniqid(),
                        'receiver' => $receiverEmail
                    ]
                ]
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('PayPal Payout Failed', ['email' => $receiverEmail, 'response' => $response->body()]);
        throw new \Exception('Failed to send PayPal payout: ' . $response->json('message', 'Unknown error'));
    }
}
