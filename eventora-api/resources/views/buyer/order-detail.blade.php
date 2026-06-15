<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - {{ config('app.name', 'Eventora') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .print-border { border: 1px solid #e5e7eb; }
        }
    </style>
</head>
<body class="text-gray-900 antialiased min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 no-print">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('buyer.my-tickets') }}" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </a>
                    @else
                        <a href="/" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </a>
                    @endauth
                    <h1 class="font-bold text-lg text-gray-900">Order #{{ $order->order_number }}</h1>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="window.print()" class="text-sm font-medium text-gray-600 hover:text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
        
        @if($order->status == 'pending')
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-xl mb-8 flex items-start gap-3">
                <svg class="w-6 h-6 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h4 class="font-bold">Payment Pending</h4>
                    <p class="text-sm mt-1">This order is awaiting payment confirmation. Your tickets are not valid until payment is complete.</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Left Col: Tickets -->
            <div class="md:col-span-2 space-y-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Your Tickets</h2>
                    <span class="text-sm text-gray-500">{{ $order->attendees->count() }} Tickets</span>
                </div>

                @foreach($order->attendees as $attendee)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden print-border relative">
                    <!-- Ticket Header -->
                    <div class="bg-gradient-to-r from-violet-600 to-indigo-600 px-6 py-4 text-white">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-lg leading-tight">{{ $order->event->title }}</h3>
                                <p class="text-violet-200 text-sm mt-1">{{ $order->event->organization->name }}</p>
                            </div>
                            <span class="bg-white/20 px-2 py-1 rounded text-xs font-bold uppercase">{{ $attendee->ticket->name }}</span>
                        </div>
                    </div>
                    
                    <!-- Ticket Body -->
                    <div class="p-6 flex flex-col sm:flex-row gap-6">
                        <!-- Info -->
                        <div class="flex-grow space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Attendee</p>
                                <p class="font-bold text-gray-900 text-lg">{{ $attendee->name }}</p>
                                <p class="text-gray-500 text-sm">{{ $attendee->email }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Date</p>
                                    <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($order->event->start_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Time</p>
                                    <p class="font-medium text-gray-900 text-sm">{{ \Carbon\Carbon::parse($order->event->start_date)->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Area -->
                        <div class="sm:w-32 flex flex-col items-center justify-center pt-4 sm:pt-0 border-t sm:border-t-0 sm:border-l border-gray-100 sm:pl-6">
                            @if($order->status == 'paid' || $order->total == 0)
                                <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center p-2 border border-gray-200">
                                    {!! QrCode::size(80)->generate($attendee->ticket_code) !!}
                                </div>
                                <p class="text-xs text-gray-500 font-mono mt-2">{{ substr($attendee->ticket_code, 0, 8) }}</p>
                            @else
                                <div class="w-24 h-24 bg-gray-50 rounded-lg flex items-center justify-center text-gray-400 border border-dashed border-gray-300">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </div>
                                <p class="text-xs text-red-500 font-medium mt-2 text-center">Awaiting<br>Payment</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Right Col: Order Info -->
            <div class="space-y-6">
                <!-- Event Details -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 print-border">
                    <h3 class="font-bold text-gray-900 mb-4">Event Information</h3>
                    <div class="space-y-4">
                        <div class="flex gap-3 text-sm">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <div>
                                <p class="text-gray-900">{{ \Carbon\Carbon::parse($order->event->start_date)->format('l, F j, Y') }}</p>
                                <p class="text-gray-500">{{ \Carbon\Carbon::parse($order->event->start_date)->format('h:i A') }} - {{ \Carbon\Carbon::parse($order->event->end_date)->format('h:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 text-sm">
                            <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div>
                                @if($order->event->type == 'online')
                                    <p class="text-gray-900">Online Event</p>
                                    @if($order->status == 'paid')
                                        <a href="{{ $order->event->online_url }}" target="_blank" class="text-violet-600 hover:underline font-medium">Join Event Link</a>
                                    @endif
                                @else
                                    <p class="text-gray-900">{{ $order->event->venue_name }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 print-border">
                    <h3 class="font-bold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Order Number</span>
                            <span class="font-medium text-gray-900">{{ $order->order_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Order Date</span>
                            <span class="font-medium text-gray-900">{{ $order->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Name</span>
                            <span class="font-medium text-gray-900">{{ $order->buyer_name }}</span>
                        </div>
                        
                        <hr class="border-gray-100 my-4">
                        
                        <div class="flex justify-between font-bold text-base">
                            <span class="text-gray-900">Total Paid</span>
                            <span class="text-gray-900">{{ $order->total == 0 ? 'Free' : $order->currency . ' ' . number_format($order->total, 2) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
</body>
</html>
