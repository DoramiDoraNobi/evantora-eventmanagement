<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\OAuth;

class StripeConnectController extends Controller
{
    public function connect()
    {
        $clientId = env('STRIPE_CLIENT_ID');
        if (!$clientId) {
            return back()->with('error', 'Stripe Client ID is not configured on the platform.');
        }

        $organizationId = app('current_organization')->id;
        $state = base64_encode(json_encode(['org_id' => $organizationId, 'token' => csrf_token()]));

        // Using standard OAuth authorize URL
        $url = "https://connect.stripe.com/oauth/authorize?" . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'scope' => 'read_write',
            'state' => $state,
            'redirect_uri' => route('stripe.callback'),
        ]);

        return redirect($url);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('organization.edit')->with('error', 'Stripe connection failed: ' . $request->error_description);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $response = OAuth::token([
                'grant_type' => 'authorization_code',
                'code' => $request->code,
            ]);

            $accountId = $response->stripe_user_id;

            $organization = app('current_organization');
            $organization->update([
                'stripe_account_id' => $accountId,
                'stripe_onboarding_completed' => true,
            ]);

            return redirect()->route('organization.edit')->with('status', 'Successfully connected to Stripe!');

        } catch (\Exception $e) {
            return redirect()->route('organization.edit')->with('error', 'Error connecting to Stripe: ' . $e->getMessage());
        }
    }
    
    public function disconnect()
    {
        $organization = app('current_organization');
        // Optional: Call Stripe API to deauthorize the account here
        
        $organization->update([
            'stripe_account_id' => null,
            'stripe_onboarding_completed' => false,
        ]);
        
        return redirect()->route('organization.edit')->with('status', 'Stripe account disconnected.');
    }
}