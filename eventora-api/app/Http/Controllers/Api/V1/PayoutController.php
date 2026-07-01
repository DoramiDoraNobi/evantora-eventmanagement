<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function index($orgId)
    {
        $organization = Organization::findOrFail($orgId);

        $payouts = $organization->payouts()->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'balances' => [
                    'total_earnings' => $organization->total_earnings,
                    'withdrawn_amount' => $organization->withdrawn_amount,
                    'available_balance' => $organization->available_balance,
                ],
                'payouts' => $payouts
            ]
        ]);
    }

    public function store(Request $request, $orgId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ]);

        $organization = Organization::findOrFail($orgId);

        if ($request->amount > $organization->available_balance) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient available balance. You can only request up to ' . number_format($organization->available_balance, 2)
            ], 422);
        }

        $payout = $organization->payouts()->create([
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payout request submitted successfully.',
            'data' => $payout
        ], 201);
    }
}
