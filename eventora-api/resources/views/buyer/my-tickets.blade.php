<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - {{ config('app.name', 'Eventora') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
    </style>
</head>
<body class="text-gray-900 antialiased min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        </div>
                        <span class="font-bold text-xl text-gray-900">Eventora</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-500">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('buyer.logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-900">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 w-full">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">My Tickets</h1>
            <p class="mt-2 text-sm text-gray-500">View and manage all your event tickets.</p>
        </div>

        @if($orders->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center border border-gray-200 shadow-sm">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No tickets yet</h3>
                <p class="text-gray-500 mb-6">You haven't purchased any tickets. Discover great events around you!</p>
                <a href="/#events" class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-violet-600 hover:bg-violet-700">
                    Browse Events
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($orders as $order)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col md:flex-row transition-all hover:shadow-md">
                    <!-- Date Column -->
                    <div class="bg-gray-50 p-6 md:w-48 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
                        <span class="text-sm font-bold text-red-500 uppercase tracking-wider">{{ \Carbon\Carbon::parse($order->event->start_date)->format('M') }}</span>
                        <span class="text-4xl font-extrabold text-gray-900">{{ \Carbon\Carbon::parse($order->event->start_date)->format('d') }}</span>
                        <span class="text-sm text-gray-500 mt-1">{{ \Carbon\Carbon::parse($order->event->start_date)->format('Y') }}</span>
                    </div>
                    
                    <!-- Details Column -->
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="text-xl font-bold text-gray-900 line-clamp-1">{{ $order->event->title }}</h3>
                                @if($order->status == 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">Confirmed</span>
                                @elseif($order->status == 'pending')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Pending</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">{{ ucfirst($order->status) }}</span>
                                @endif
                            </div>
                            
                            <p class="text-sm text-gray-500 mb-4">{{ $order->event->organization->name }}</p>
                            
                            <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ \Carbon\Carbon::parse($order->event->start_date)->format('h:i A') }}
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                    {{ $order->attendees->count() }} Tickets
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-sm text-gray-500 font-mono">Order #{{ $order->order_number }}</span>
                            <a href="{{ route('buyer.order-detail', $order->order_number) }}" class="text-sm font-bold text-violet-600 hover:text-violet-800 flex items-center gap-1">
                                View Details
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </main>

    <footer class="bg-white border-t border-gray-200 mt-auto py-8 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'Eventora') }}. All rights reserved.
    </footer>
</body>
</html>
