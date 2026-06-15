<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - {{ $event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <nav class="bg-white shadow-sm py-4">
        <div class="max-w-3xl mx-auto px-4">
            <h1 class="text-xl font-bold" style="color: {{ $organization->primary_color }}">{{ $organization->name }}</h1>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-extrabold tracking-tight mb-8">Checkout</h2>
        
        @if(session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm mb-6 flex items-start gap-3 shadow-sm">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <h4 class="font-bold mb-1">Transaction Failed</h4>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm mb-6 shadow-sm">
                <div class="flex items-start gap-3 mb-2">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h4 class="font-bold">Please correct the following errors:</h4>
                </div>
                <ul class="list-disc pl-8 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <form action="{{ route('public.event.process', ['organizationSlug' => $organization->slug, 'eventSlug' => $event->slug]) }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Hidden inputs to carry over selected tickets -->
            @foreach($ticketsToBuy as $ot)
                <input type="hidden" name="tickets[{{ $ot['ticket']->id }}]" value="{{ $ot['quantity'] }}">
            @endforeach

            <!-- Order Summary -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium">Order Summary</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($ticketsToBuy as $ot)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $ot['quantity'] }}x {{ $ot['ticket']->name }}</span>
                            <span class="font-medium">{{ $ot['ticket']->type == 'free' ? 'Free' : $organization->currency . ' ' . number_format($ot['ticket']->price * $ot['quantity'], 2) }}</span>
                        </div>
                        @endforeach
                        <div class="pt-4 mt-4 border-t border-gray-200 flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span>{{ $totalAmount == 0 ? 'Free' : $organization->currency . ' ' . number_format($totalAmount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buyer Details -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden" x-data="{ createAccount: false }">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium">Buyer Information</h3>
                    @guest
                        <a href="{{ route('buyer.login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Log in</a>
                    @endguest
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="buyer_name" value="{{ auth()->check() ? auth()->user()->name : '' }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="buyer_email" value="{{ auth()->check() ? auth()->user()->email : '' }}" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone (Optional)</label>
                        <input type="text" name="buyer_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    @guest
                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="create_account" name="create_account" type="checkbox" x-model="createAccount" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="create_account" class="font-medium text-gray-700">Create an account to manage your tickets</label>
                                <p class="text-gray-500">You'll be able to log in to view and download your tickets anytime.</p>
                            </div>
                        </div>

                        <div x-show="createAccount" x-transition x-cloak class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" :required="createAccount" :disabled="!createAccount" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" name="password_confirmation" :required="createAccount" :disabled="!createAccount" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>
                    @endguest
                </div>
            </div>

            <!-- Attendee Details -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium">Ticket Holders</h3>
                </div>
                <div class="p-6 space-y-6">
                    @php $attendeeIndex = 0; @endphp
                    @foreach($ticketsToBuy as $ot)
                        @for($i = 1; $i <= $ot['quantity']; $i++)
                            <div class="border border-gray-100 rounded-lg p-4 bg-gray-50/50">
                                <h4 class="font-medium text-sm text-gray-900 mb-3">Ticket {{ $attendeeIndex + 1 }} - {{ $ot['ticket']->name }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Attendee Name</label>
                                        <input type="text" name="attendees[{{ $attendeeIndex }}][name]" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700">Attendee Email</label>
                                        <input type="email" name="attendees[{{ $attendeeIndex }}][email]" required class="mt-1 block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </div>
                            </div>
                            @php $attendeeIndex++; @endphp
                        @endfor
                    @endforeach
                </div>
            </div>

            <button type="submit" class="w-full py-4 px-4 border border-transparent rounded-xl shadow-sm text-lg font-bold text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2" style="background-color: {{ $organization->primary_color }};">
                {{ $totalAmount == 0 ? 'Register for Free' : 'Proceed to Payment' }}
            </button>
        </form>
    </main>
</body>
</html>