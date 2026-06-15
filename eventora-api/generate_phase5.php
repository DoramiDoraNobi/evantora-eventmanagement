<?php

$dir = __DIR__;

// 1. Create Migration for Organizations table
$migrationName = '2026_06_10_000000_add_stripe_connect_to_organizations_table.php';
$migrationPath = $dir . '/database/migrations/' . $migrationName;
$migrationCode = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint \$table) {
            \$table->string('stripe_account_id')->nullable()->after('primary_color');
            \$table->boolean('stripe_onboarding_completed')->default(false)->after('stripe_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint \$table) {
            \$table->dropColumn(['stripe_account_id', 'stripe_onboarding_completed']);
        });
    }
};
PHP;
file_put_contents($migrationPath, $migrationCode);

// 2. Create StripeConnectController
$stripeConnectControllerPath = $dir . '/app/Http/Controllers/Admin/StripeConnectController.php';
$stripeConnectControllerCode = <<<PHP
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
        \$clientId = env('STRIPE_CLIENT_ID');
        if (!\$clientId) {
            return back()->with('error', 'Stripe Client ID is not configured on the platform.');
        }

        \$organizationId = app('current_organization')->id;
        \$state = base64_encode(json_encode(['org_id' => \$organizationId, 'token' => csrf_token()]));

        // Using standard OAuth authorize URL
        \$url = "https://connect.stripe.com/oauth/authorize?" . http_build_query([
            'response_type' => 'code',
            'client_id' => \$clientId,
            'scope' => 'read_write',
            'state' => \$state,
            'redirect_uri' => route('stripe.callback'),
        ]);

        return redirect(\$url);
    }

    public function callback(Request \$request)
    {
        if (\$request->has('error')) {
            return redirect()->route('organization.edit')->with('error', 'Stripe connection failed: ' . \$request->error_description);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            \$response = OAuth::token([
                'grant_type' => 'authorization_code',
                'code' => \$request->code,
            ]);

            \$accountId = \$response->stripe_user_id;

            \$organization = app('current_organization');
            \$organization->update([
                'stripe_account_id' => \$accountId,
                'stripe_onboarding_completed' => true,
            ]);

            return redirect()->route('organization.edit')->with('status', 'Successfully connected to Stripe!');

        } catch (\Exception \$e) {
            return redirect()->route('organization.edit')->with('error', 'Error connecting to Stripe: ' . \$e->getMessage());
        }
    }
    
    public function disconnect()
    {
        \$organization = app('current_organization');
        // Optional: Call Stripe API to deauthorize the account here
        
        \$organization->update([
            'stripe_account_id' => null,
            'stripe_onboarding_completed' => false,
        ]);
        
        return redirect()->route('organization.edit')->with('status', 'Stripe account disconnected.');
    }
}
PHP;
if(!is_dir(dirname($stripeConnectControllerPath))) mkdir(dirname($stripeConnectControllerPath), 0755, true);
file_put_contents($stripeConnectControllerPath, $stripeConnectControllerCode);

// 3. Create StripeWebhookController
$stripeWebhookControllerPath = $dir . '/app/Http/Controllers/StripeWebhookController.php';
$stripeWebhookControllerCode = <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function handle(Request \$request)
    {
        \$payload = \$request->getContent();
        \$sigHeader = \$request->header('Stripe-Signature');
        \$endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            \$event = Webhook::constructEvent(
                \$payload, \$sigHeader, \$endpointSecret
            );
        } catch(\UnexpectedValueException \$e) {
            return response('Invalid payload', 400);
        } catch(SignatureVerificationException \$e) {
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch (\$event->type) {
            case 'checkout.session.completed':
                \$session = \$event->data->object;
                \$this->handleCheckoutSessionCompleted(\$session);
                break;
            default:
                // Unhandled event type
        }

        return response('Webhook handled', 200);
    }

    protected function handleCheckoutSessionCompleted(\$session)
    {
        if (isset(\$session->metadata->order_id)) {
            \$order = Order::with('attendees.ticket')->find(\$session->metadata->order_id);
            if (\$order && \$order->status === 'pending') {
                \$order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                // Confirm all attendees
                foreach (\$order->attendees as \$attendee) {
                    \$attendee->update(['status' => 'confirmed']);
                }
                
                // Create payment record
                \$order->payments()->create([
                    'organization_id' => \$order->organization_id,
                    'gateway' => 'stripe',
                    'gateway_payment_id' => \$session->payment_intent,
                    'amount' => \$order->total,
                    'currency' => \$order->currency,
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
            }
        }
    }
}
PHP;
file_put_contents($stripeWebhookControllerPath, $stripeWebhookControllerCode);

// 4. Update PublicEventController to use Stripe Checkout
$publicControllerPath = $dir . '/app/Http/Controllers/PublicEventController.php';
$publicControllerContent = file_get_contents($publicControllerPath);

$stripeCheckoutLogic = <<<PHP
        // If total is 0 (Free tickets), redirect to success
        if (\$totalAmount == 0) {
            return redirect()->route('public.order.success', \$order->order_number);
        }

        // --- STRIPE CONNECT INTEGRATION ---
        if (!\$organization->stripe_account_id) {
            // Revert order if organizer is not connected to Stripe
            \$order->delete();
            return redirect()->back()->with('error', 'The event organizer cannot accept payments at this time. Please contact them.');
        }

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        \$lineItems = [];
        foreach (\$orderTickets as \$ot) {
            \$lineItems[] = [
                'price_data' => [
                    'currency' => strtolower(\$organization->currency),
                    'product_data' => [
                        'name' => \$ot['ticket']->name . ' - ' . \$event->title,
                    ],
                    'unit_amount' => (int) (\$ot['ticket']->price * 100), // Stripe expects cents
                ],
                'quantity' => \$ot['quantity'],
            ];
        }

        // Calculate Application Fee (e.g. 5% + $0.50)
        // For MVP, we'll just take a simple 5% flat fee.
        \$platformFeePercent = env('PLATFORM_FEE_PERCENT', 5);
        \$feeAmountCents = (int) (\$totalAmount * (\$platformFeePercent / 100) * 100);

        try {
            \$checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => \$lineItems,
                'mode' => 'payment',
                'success_url' => route('public.order.success', \$order->order_number) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('public.event.show', ['organizationSlug' => \$organization->slug, 'eventSlug' => \$event->slug]),
                'payment_intent_data' => [
                    'application_fee_amount' => \$feeAmountCents,
                ],
                'metadata' => [
                    'order_id' => \$order->id,
                    'event_id' => \$event->id,
                ],
            ], [
                'stripe_account' => \$organization->stripe_account_id,
            ]);

            return redirect(\$checkoutSession->url);

        } catch (\Exception \$e) {
            \$order->delete();
            return redirect()->back()->with('error', 'Payment gateway error: ' . \$e->getMessage());
        }
PHP;

$publicControllerContent = preg_replace(
    '/\/\/\s*If total is 0.*?\/\/ Otherwise redirect to payment.*?return redirect\(\)->route\(\'public.order.success\'.*?;/s',
    $stripeCheckoutLogic,
    $publicControllerContent
);
file_put_contents($publicControllerPath, $publicControllerContent);

// 5. Update Organization Edit View to show Stripe Connect button
$orgEditViewPath = $dir . '/resources/views/admin/organization/edit.blade.php';
$orgEditViewContent = file_get_contents($orgEditViewPath);

$stripeConnectUI = <<<BLADE
        <!-- Stripe Connect Panel -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg mb-8">
            <div class="max-w-xl">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Payment Gateway') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Connect your Stripe account to receive payments directly for your paid tickets.') }}
                        </p>
                    </header>

                    <div class="mt-6">
                        @if(app('current_organization')->stripe_account_id)
                            <div class="flex items-center gap-4 bg-green-50 border border-green-200 p-4 rounded-lg">
                                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <h3 class="font-bold text-green-800">Connected to Stripe</h3>
                                    <p class="text-sm text-green-700">Account ID: {{ app('current_organization')->stripe_account_id }}</p>
                                </div>
                                <div class="ml-auto">
                                    <form action="{{ route('stripe.disconnect') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Disconnect</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="bg-blue-50 border border-blue-200 p-6 rounded-lg text-center">
                                <svg class="w-12 h-12 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                <h3 class="font-bold text-gray-900 mb-2">Accept Payments Online</h3>
                                <p class="text-sm text-gray-600 mb-4">Connect with Stripe to automatically process credit card payments and receive funds directly to your bank account.</p>
                                <a href="{{ route('stripe.connect') }}" class="inline-flex items-center px-6 py-3 bg-[#635BFF] border border-transparent rounded-md font-bold text-white uppercase tracking-widest hover:bg-[#4B45C6] focus:bg-[#4B45C6] active:bg-[#4B45C6] focus:outline-none focus:ring-2 focus:ring-[#635BFF] focus:ring-offset-2 transition ease-in-out duration-150">
                                    Connect with Stripe
                                </a>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>

BLADE;

// Insert before the first <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
$orgEditViewContent = preg_replace(
    '/(<div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg)/',
    $stripeConnectUI . "\n        $1",
    $orgEditViewContent,
    1
);
file_put_contents($orgEditViewPath, $orgEditViewContent);

// 6. Add Routes
$webRoutesPath = $dir . '/routes/web.php';
$routesContent = file_get_contents($webRoutesPath);

$stripeRoutes = <<<PHP

    // Stripe Connect Routes
    Route::get('settings/stripe/connect', [\App\Http\Controllers\Admin\StripeConnectController::class, 'connect'])->name('stripe.connect');
    Route::get('settings/stripe/callback', [\App\Http\Controllers\Admin\StripeConnectController::class, 'callback'])->name('stripe.callback');
    Route::post('settings/stripe/disconnect', [\App\Http\Controllers\Admin\StripeConnectController::class, 'disconnect'])->name('stripe.disconnect');
PHP;

if (strpos($routesContent, 'Route::get(\'settings/stripe/connect\'') === false) {
    $routesContent = str_replace('// Team Routes', $stripeRoutes . "\n\n    // Team Routes", $routesContent);
    file_put_contents($webRoutesPath, $routesContent);
}

// 7. CSRF Exception for webhook
$bootstrapAppPath = $dir . '/bootstrap/app.php';
$bootstrapAppContent = file_get_contents($bootstrapAppPath);
if (strpos($bootstrapAppContent, 'validateCsrfTokens') === false) {
    $bootstrapAppContent = str_replace(
        '->withMiddleware(function (Middleware $middleware) {',
        "->withMiddleware(function (Middleware \$middleware) {\n        \$middleware->validateCsrfTokens(except: [\n            'stripe/webhook',\n        ]);",
        $bootstrapAppContent
    );
    file_put_contents($bootstrapAppPath, $bootstrapAppContent);
}

// 8. Add Webhook route
$webhookRoute = "\nRoute::post('stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');\n";
file_put_contents($webRoutesPath, $webhookRoute, FILE_APPEND);

// 9. Ensure Stripe package is required (We already ran composer require, but let's add dummy env vars)
$envPath = $dir . '/.env';
$envContent = file_get_contents($envPath);
if (strpos($envContent, 'STRIPE_CLIENT_ID') === false) {
    $stripeEnv = <<<ENV

STRIPE_KEY=
STRIPE_SECRET=
STRIPE_CLIENT_ID=
STRIPE_WEBHOOK_SECRET=
PLATFORM_FEE_PERCENT=5
ENV;
    file_put_contents($envPath, $stripeEnv, FILE_APPEND);
}

echo "Phase 5 Stripe Connect generated successfully.";

