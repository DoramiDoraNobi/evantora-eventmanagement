<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $organization = $request->get('tenant');
        
        $payouts = $organization->payouts()->latest()->paginate(10);

        return view('admin.finance.index', compact('organization', 'payouts'));
    }

    public function store(Request $request)
    {
        $organization = $request->get('tenant');

        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'bank_name' => 'required|string|max:255',
            'bank_account_number' => 'required|string|max:255',
            'bank_account_name' => 'required|string|max:255',
        ]);

        if ($request->amount > $organization->available_balance) {
            return back()->with('error', 'Insufficient available balance. You can only request up to ' . number_format($organization->available_balance, 2));
        }

        $organization->payouts()->create([
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Payout request submitted successfully. We will process it shortly.');
    }
}
