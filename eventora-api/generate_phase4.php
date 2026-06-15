<?php

$dir = __DIR__;

// 1. Create PublicEventController
$controllerPath = $dir . '/app/Http/Controllers/PublicEventController.php';
$controllerCode = <<<PHP
<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Order;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicEventController extends Controller
{
    public function show(\$organizationSlug, \$eventSlug)
    {
        \$organization = Organization::where('slug', \$organizationSlug)->firstOrFail();
        \$event = \$organization->events()->where('slug', \$eventSlug)->where('status', 'published')->firstOrFail();
        
        \$tickets = \$event->tickets()->where('is_active', true)->orderBy('sort_order')->get();
        
        return view('public.event.show', compact('organization', 'event', 'tickets'));
    }

    public function checkout(Request \$request, \$organizationSlug, \$eventSlug)
    {
        \$organization = Organization::where('slug', \$organizationSlug)->firstOrFail();
        \$event = \$organization->events()->where('slug', \$eventSlug)->where('status', 'published')->firstOrFail();
        
        // Process selected tickets from the landing page
        \$selectedTickets = \$request->input('tickets', []);
        \$ticketsToBuy = [];
        \$totalAmount = 0;
        
        foreach (\$selectedTickets as \$ticketId => \$quantity) {
            if (\$quantity > 0) {
                \$ticket = \$event->tickets()->find(\$ticketId);
                if (\$ticket) {
                    \$ticketsToBuy[] = [
                        'ticket' => \$ticket,
                        'quantity' => \$quantity
                    ];
                    \$totalAmount += (\$ticket->price * \$quantity);
                }
            }
        }
        
        if (empty(\$ticketsToBuy)) {
            return redirect()->back()->with('error', 'Please select at least one ticket.');
        }

        return view('public.event.checkout', compact('organization', 'event', 'ticketsToBuy', 'totalAmount'));
    }

    public function processCheckout(Request \$request, \$organizationSlug, \$eventSlug)
    {
        \$organization = Organization::where('slug', \$organizationSlug)->firstOrFail();
        \$event = \$organization->events()->where('slug', \$eventSlug)->where('status', 'published')->firstOrFail();
        
        \$validated = \$request->validate([
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'nullable|string|max:50',
            'tickets' => 'required|array',
            // Attendee details
            'attendees' => 'required|array',
            'attendees.*.name' => 'required|string|max:255',
            'attendees.*.email' => 'required|email|max:255',
        ]);

        \$totalAmount = 0;
        \$orderTickets = [];

        foreach (\$validated['tickets'] as \$ticketId => \$quantity) {
            if (\$quantity > 0) {
                \$ticket = \$event->tickets()->findOrFail(\$ticketId);
                \$totalAmount += (\$ticket->price * \$quantity);
                \$orderTickets[] = ['ticket' => \$ticket, 'quantity' => \$quantity];
            }
        }

        // Create Order
        \$order = Order::create([
            'organization_id' => \$organization->id,
            'event_id' => \$event->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'buyer_name' => \$validated['buyer_name'],
            'buyer_email' => \$validated['buyer_email'],
            'buyer_phone' => \$validated['buyer_phone'] ?? null,
            'subtotal' => \$totalAmount,
            'total' => \$totalAmount,
            'status' => \$totalAmount > 0 ? 'pending' : 'paid',
            'currency' => \$organization->currency,
        ]);

        // Create Attendees
        \$attendeeIndex = 0;
        foreach (\$orderTickets as \$ot) {
            for (\$i = 0; \$i < \$ot['quantity']; \$i++) {
                \$attendeeData = \$validated['attendees'][\$attendeeIndex];
                
                Attendee::create([
                    'organization_id' => \$organization->id,
                    'event_id' => \$event->id,
                    'order_id' => \$order->id,
                    'ticket_id' => \$ot['ticket']->id,
                    'ticket_number' => 'TKT-' . strtoupper(Str::random(12)),
                    'qr_code' => Str::uuid()->toString(),
                    'name' => \$attendeeData['name'],
                    'email' => \$attendeeData['email'],
                    'status' => \$order->status === 'paid' ? 'confirmed' : 'registered',
                ]);
                
                \$ot['ticket']->increment('quantity_sold');
                \$attendeeIndex++;
            }
        }

        // If total is 0 (Free tickets), redirect to success
        if (\$totalAmount == 0) {
            return redirect()->route('public.order.success', \$order->order_number);
        }

        // Otherwise redirect to payment (Phase 5 placeholder)
        return redirect()->route('public.order.success', \$order->order_number)->with('status', 'Payment integration coming soon!');
    }

    public function orderSuccess(\$orderNumber)
    {
        \$order = Order::with('attendees.ticket', 'event.organization')->where('order_number', \$orderNumber)->firstOrFail();
        return view('public.event.success', compact('order'));
    }
    
    public function downloadTicket(\$ticketNumber)
    {
        \$attendee = Attendee::with('event.organization', 'ticket')->where('ticket_number', \$ticketNumber)->firstOrFail();
        return view('public.event.ticket', compact('attendee'));
    }
}
PHP;
file_put_contents($controllerPath, $controllerCode);

// 2. Create public views
$viewsDir = $dir . '/resources/views/public/event';
if (!is_dir($viewsDir)) mkdir($viewsDir, 0755, true);

$showView = <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \$event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm py-4">
        <div class="max-w-5xl mx-auto px-4 flex justify-between items-center">
            <h1 class="text-xl font-bold" style="color: {{ \$organization->primary_color }}">{{ \$organization->name }}</h1>
        </div>
    </nav>

    <!-- Hero -->
    <header class="max-w-5xl mx-auto px-4 py-12">
        <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">{{ \$event->title }}</h1>
        <div class="mt-4 flex gap-6 text-gray-600">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>{{ \Carbon\Carbon::parse(\$event->start_date)->format('l, M d, Y - g:i A') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>{{ \$event->type == 'online' ? 'Online Event' : \$event->venue_name }}</span>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 pb-20 grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Details -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100">
                <h2 class="text-2xl font-bold mb-4">About This Event</h2>
                <div class="prose prose-indigo max-w-none">
                    {!! nl2br(e(\$event->description)) !!}
                </div>
            </div>
        </div>

        <!-- Ticket Selection -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 sticky top-6">
                <h3 class="text-xl font-bold mb-4">Select Tickets</h3>
                
                @if(session('error'))
                    <div class="p-3 bg-red-50 text-red-700 rounded-md mb-4 text-sm">{{ session('error') }}</div>
                @endif

                <form action="{{ route('public.event.checkout', ['organizationSlug' => \$organization->slug, 'eventSlug' => \$event->slug]) }}" method="GET">
                    <div class="space-y-4 mb-6">
                        @foreach(\$tickets as \$ticket)
                        <div class="flex justify-between items-center border-b pb-4 last:border-0 last:pb-0">
                            <div>
                                <h4 class="font-semibold">{{ \$ticket->name }}</h4>
                                <p class="text-sm text-gray-500 font-medium">
                                    {{ \$ticket->type == 'free' ? 'Free' : \$organization->currency . ' ' . number_format(\$ticket->price, 2) }}
                                </p>
                            </div>
                            <div class="w-20">
                                <select name="tickets[{{ \$ticket->id }}]" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @for(\$i=0; \$i<=(min(\$ticket->max_per_order, 10)); \$i++)
                                        <option value="{{ \$i }}">{{ \$i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <button type="submit" class="w-full py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: {{ \$organization->primary_color }};">
                        Checkout
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
BLADE;
file_put_contents($viewsDir . '/show.blade.php', $showView);

$checkoutView = <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ \$event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <nav class="bg-white shadow-sm py-4">
        <div class="max-w-3xl mx-auto px-4">
            <h1 class="text-xl font-bold" style="color: {{ \$organization->primary_color }}">{{ \$organization->name }}</h1>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-extrabold tracking-tight mb-8">Checkout</h2>
        
        <form action="{{ route('public.event.process', ['organizationSlug' => \$organization->slug, 'eventSlug' => \$event->slug]) }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Hidden inputs to carry over selected tickets -->
            @foreach(\$ticketsToBuy as \$ot)
                <input type="hidden" name="tickets[{{ \$ot['ticket']->id }}]" value="{{ \$ot['quantity'] }}">
            @endforeach

            <!-- Order Summary -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium">Order Summary</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach(\$ticketsToBuy as \$ot)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ \$ot['quantity'] }}x {{ \$ot['ticket']->name }}</span>
                            <span class="font-medium">{{ \$ot['ticket']->type == 'free' ? 'Free' : \$organization->currency . ' ' . number_format(\$ot['ticket']->price * \$ot['quantity'], 2) }}</span>
                        </div>
                        @endforeach
                        <div class="pt-4 mt-4 border-t border-gray-200 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>{{ \$totalAmount == 0 ? 'Free' : \$organization->currency . ' ' . number_format(\$totalAmount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Details -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium">Buyer Information</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="buyer_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="buyer_email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                        <input type="text" name="buyer_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Attendee Details -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium">Ticket Holders</h3>
                </div>
                <div class="p-6 space-y-6">
                    @php \$attendeeIndex = 0; @endphp
                    @foreach(\$ticketsToBuy as \$ot)
                        @for(\$i = 1; \$i <= \$ot['quantity']; \$i++)
                            <div class="border border-gray-100 rounded-lg p-4 bg-gray-50/50">
                                <h4 class="font-medium text-sm text-gray-900 mb-3">Ticket {{ \$attendeeIndex + 1 }} - {{ \$ot['ticket']->name }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Attendee Name</label>
                                        <input type="text" name="attendees[{{ \$attendeeIndex }}][name]" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Attendee Email</label>
                                        <input type="email" name="attendees[{{ \$attendeeIndex }}][email]" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            @php \$attendeeIndex++; @endphp
                        @endfor
                    @endforeach
                </div>
            </div>

            <button type="submit" class="w-full py-4 px-4 border border-transparent rounded-xl shadow-sm text-lg font-bold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: {{ \$organization->primary_color }};">
                {{ \$totalAmount == 0 ? 'Register for Free' : 'Proceed to Payment' }}
            </button>
        </form>
    </main>
</body>
</html>
BLADE;
file_put_contents($viewsDir . '/checkout.blade.php', $checkoutView);

$successView = <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success - {{ \$order->event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 flex items-center justify-center min-h-screen">
    <div class="max-w-2xl w-full mx-4 bg-white rounded-2xl shadow-lg p-8 text-center border-t-8" style="border-top-color: {{ \$order->event->organization->primary_color }}">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Order Confirmed!</h1>
        <p class="text-gray-600 mb-8">Thank you for registering. Order #{{ \$order->order_number }}</p>
        
        <div class="text-left bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8">
            <h3 class="font-bold text-lg mb-4 border-b pb-2">Your Tickets</h3>
            <div class="space-y-4">
                @foreach(\$order->attendees as \$attendee)
                <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow-sm border border-gray-100">
                    <div>
                        <div class="font-bold text-gray-900">{{ \$attendee->name }}</div>
                        <div class="text-sm text-gray-500">{{ \$attendee->ticket->name }}</div>
                    </div>
                    <a href="{{ route('public.ticket.download', \$attendee->ticket_number) }}" target="_blank" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-50">View E-Ticket</a>
                </div>
                @endforeach
            </div>
        </div>
        
        <a href="{{ route('public.event.show', ['organizationSlug' => \$order->event->organization->slug, 'eventSlug' => \$order->event->slug]) }}" class="text-indigo-600 font-medium hover:underline">Back to Event Page</a>
    </div>
</body>
</html>
BLADE;
file_put_contents($viewsDir . '/success.blade.php', $successView);

$ticketView = <<<BLADE
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket: {{ \$attendee->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 flex justify-center py-10 px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="px-8 py-6 text-center text-white relative" style="background-color: {{ \$attendee->event->organization->primary_color }}">
            <h1 class="text-2xl font-bold leading-tight">{{ \$attendee->event->title }}</h1>
            <p class="mt-2 text-sm opacity-90">{{ \Carbon\Carbon::parse(\$attendee->event->start_date)->format('D, M d Y - g:i A') }}</p>
            
            <!-- Ticket notch left -->
            <div class="absolute -left-4 -bottom-4 w-8 h-8 bg-gray-100 rounded-full"></div>
            <!-- Ticket notch right -->
            <div class="absolute -right-4 -bottom-4 w-8 h-8 bg-gray-100 rounded-full"></div>
        </div>
        
        <!-- Divider -->
        <div class="border-b-2 border-dashed border-gray-300"></div>

        <!-- Body -->
        <div class="p-8 text-center relative">
            <!-- Ticket notch top left -->
            <div class="absolute -left-4 -top-4 w-8 h-8 bg-gray-100 rounded-full"></div>
            <!-- Ticket notch top right -->
            <div class="absolute -right-4 -top-4 w-8 h-8 bg-gray-100 rounded-full"></div>

            <div class="mb-6">
                <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Attendee</div>
                <div class="text-xl font-bold text-gray-900 mt-1">{{ \$attendee->name }}</div>
            </div>
            
            <div class="mb-8">
                <div class="text-gray-500 text-xs uppercase tracking-wider font-semibold">Ticket Type</div>
                <div class="text-lg font-semibold text-gray-800 mt-1">{{ \$attendee->ticket->name }}</div>
            </div>

            <div class="flex justify-center mb-6">
                <div class="p-4 bg-white border-2 border-gray-100 rounded-xl inline-block shadow-sm">
                    {!! QrCode::size(200)->generate(\$attendee->qr_code) !!}
                </div>
            </div>
            
            <div class="text-xs text-gray-400 font-mono tracking-widest">
                {{ \$attendee->ticket_number }}
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 text-center text-xs text-gray-500 border-t border-gray-100">
            Powered by Eventora
        </div>
    </div>
</body>
</html>
BLADE;
file_put_contents($viewsDir . '/ticket.blade.php', $ticketView);

// 3. Update routes
$webRoutesPath = $dir . '/routes/web.php';
$routesContent = file_get_contents($webRoutesPath);
$publicRoutes = <<<PHP

// Public Event Routes
Route::get('/e/{organizationSlug}/{eventSlug}', [\App\Http\Controllers\PublicEventController::class, 'show'])->name('public.event.show');
Route::get('/e/{organizationSlug}/{eventSlug}/checkout', [\App\Http\Controllers\PublicEventController::class, 'checkout'])->name('public.event.checkout');
Route::post('/e/{organizationSlug}/{eventSlug}/checkout', [\App\Http\Controllers\PublicEventController::class, 'processCheckout'])->name('public.event.process');
Route::get('/order/{orderNumber}/success', [\App\Http\Controllers\PublicEventController::class, 'orderSuccess'])->name('public.order.success');
Route::get('/ticket/{ticketNumber}/download', [\App\Http\Controllers\PublicEventController::class, 'downloadTicket'])->name('public.ticket.download');
PHP;

if (strpos($routesContent, 'Route::get(\'/e/{organizationSlug}') === false) {
    file_put_contents($webRoutesPath, $routesContent . $publicRoutes);
}

echo "Phase 4 generated successfully.";

