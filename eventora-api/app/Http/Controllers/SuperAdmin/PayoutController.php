<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with('organization')->latest()->paginate(20);
        return view('super-admin.payouts.index', compact('payouts'));
    }

    public function update(Request $request, Payout $payout)
    {
        $request->validate([
            'status' => 'required|in:processing,paid,rejected',
            'transaction_id' => 'required_if:status,paid|string|max:255|nullable',
            'notes' => 'nullable|string',
            'auto_paypal' => 'nullable|boolean'
        ]);

        if ($request->status === 'paid' && $request->auto_paypal) {
            $organization = $payout->organization;
            if (!$payout->bank_account_number) {
                return back()->with('error', 'The payout request does not contain a PayPal email (bank_account_number).');
            }

            try {
                $paypalService = app(\App\Services\PayPalService::class);
                // Note: assuming standard currency is IDR or USD, but PayPal Payouts requires supported currency like USD
                $payoutResponse = $paypalService->sendPayout(
                    $payout->bank_account_number, 
                    $payout->amount, 
                    'USD', 
                    $request->notes ?? 'Payout from Eventora'
                );
                
                $payout->transaction_id = $payoutResponse['batch_header']['payout_batch_id'] ?? 'PAYPAL_AUTO';
                $payout->paid_at = now();
                $payout->status = 'paid';
                $payout->notes = $request->notes;
                $payout->save();

                return redirect()->route('super-admin.payouts.index')->with('success', 'Auto PayPal Payout processed successfully.');
            } catch (\Exception $e) {
                return back()->with('error', 'PayPal Payout Failed: ' . $e->getMessage());
            }
        } else {
            $payout->status = $request->status;
            $payout->notes = $request->notes;
            
            if ($request->status === 'paid') {
                $payout->transaction_id = $request->transaction_id;
                $payout->paid_at = now();
            }

            $payout->save();

            return redirect()->route('super-admin.payouts.index')->with('success', 'Payout status updated successfully.');
        }
    }
}
