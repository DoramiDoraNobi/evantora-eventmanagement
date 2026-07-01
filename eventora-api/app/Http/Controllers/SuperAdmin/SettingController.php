<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display the settings form.
     */
    public function index()
    {
        $platformFeePercent = Setting::getVal('platform_fee_percent', env('PLATFORM_FEE_PERCENT', 5));
        $platformFeeFixed = Setting::getVal('platform_fee_fixed', 0);

        $midtransServerKey = Setting::getVal('midtrans_server_key');
        $midtransClientKey = Setting::getVal('midtrans_client_key');
        $midtransIsProduction = Setting::getVal('midtrans_is_production', false);

        $paypalClientId = Setting::getVal('paypal_client_id');
        $paypalSecret = Setting::getVal('paypal_secret');
        $paypalMode = Setting::getVal('paypal_mode', 'sandbox');

        return view('super-admin.settings.index', compact(
            'platformFeePercent', 'platformFeeFixed',
            'midtransServerKey', 'midtransClientKey', 'midtransIsProduction',
            'paypalClientId', 'paypalSecret', 'paypalMode'
        ));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'platform_fee_percent' => 'required|numeric|min:0|max:100',
            'platform_fee_fixed' => 'required|numeric|min:0',
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_is_production' => 'nullable|boolean',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
            'paypal_mode' => 'nullable|string|in:sandbox,live',
        ]);

        Setting::setVal('platform_fee_percent', $request->platform_fee_percent, 'integer');
        Setting::setVal('platform_fee_fixed', $request->platform_fee_fixed, 'integer');

        // Midtrans
        if ($request->filled('midtrans_server_key') && !str_starts_with($request->midtrans_server_key, '********')) {
            Setting::setVal('midtrans_server_key', $request->midtrans_server_key);
        }
        if ($request->filled('midtrans_client_key') && !str_starts_with($request->midtrans_client_key, '********')) {
            Setting::setVal('midtrans_client_key', $request->midtrans_client_key);
        }
        Setting::setVal('midtrans_is_production', $request->has('midtrans_is_production'), 'boolean');

        // PayPal
        if ($request->filled('paypal_client_id') && !str_starts_with($request->paypal_client_id, '********')) {
            Setting::setVal('paypal_client_id', $request->paypal_client_id);
        }
        if ($request->filled('paypal_secret') && !str_starts_with($request->paypal_secret, '********')) {
            Setting::setVal('paypal_secret', $request->paypal_secret);
        }
        if ($request->filled('paypal_mode')) {
            Setting::setVal('paypal_mode', $request->paypal_mode);
        }

        return redirect()->route('super-admin.settings.index')->with('success', 'Global settings updated successfully.');
    }
}
