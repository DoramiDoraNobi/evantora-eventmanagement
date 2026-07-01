<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected $serverKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = Setting::getVal('midtrans_server_key', env('MIDTRANS_SERVER_KEY', ''));
        $this->isProduction = Setting::getVal('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false));
        
        $this->baseUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1' 
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Create Snap Transaction
     * 
     * @param \App\Models\Order $order
     * @param array $customerDetails ['first_name', 'email', 'phone']
     * @param array $itemDetails [['id', 'price', 'quantity', 'name']]
     * @return array ['token' => '...', 'redirect_url' => '...']
     */
    public function createTransaction($order, $customerDetails, $itemDetails)
    {
        if (empty($this->serverKey)) {
            throw new \Exception('Midtrans Server Key is not configured in the system settings.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => round($order->total)
            ],
            'customer_details' => $customerDetails,
            'item_details' => $itemDetails
        ];

        $response = Http::withToken(base64_encode($this->serverKey . ':'), 'Basic')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($this->baseUrl . '/transactions', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Midtrans Create Transaction Failed', [
            'payload' => $payload,
            'response' => $response->body()
        ]);
        
        throw new \Exception('Failed to create Midtrans transaction. ' . $response->json('error_messages.0', 'Unknown error'));
    }

    /**
     * Get Transaction Status
     */
    public function getTransactionStatus($orderId)
    {
        $baseUrlApi = $this->isProduction 
            ? 'https://api.midtrans.com/v2' 
            : 'https://api.sandbox.midtrans.com/v2';

        $response = Http::withToken(base64_encode($this->serverKey . ':'), 'Basic')
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->get($baseUrlApi . '/' . $orderId . '/status');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
