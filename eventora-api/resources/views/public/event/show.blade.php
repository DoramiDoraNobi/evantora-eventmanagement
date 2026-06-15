<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->title }} - {{ $organization->name }}</title>
    <!-- Modern Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .hero-bg {
            @if($event->hero_image)
                background-image: url('{{ asset('storage/' . $event->hero_image) }}');
            @else
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            @endif
            background-size: cover;
            background-position: center;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        /* Custom scrollbar for description if needed */
        .prose ul { list-style-type: disc; padding-left: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
        .prose li { margin-bottom: 0.25rem; }
    </style>
</head>
<body class="text-gray-900 antialiased">
    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center gap-3">
                <a href="{{ route('public.organization.show', $organization->slug) }}" class="flex items-center gap-3 group">
                    @if($organization->logo)
                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="w-8 h-8 rounded-lg object-cover group-hover:opacity-80 transition-opacity">
                    @else
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold group-hover:opacity-80 transition-opacity" style="background-color: {{ $organization->primary_color }}">
                            {{ substr($organization->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="font-bold text-lg text-gray-800 group-hover:text-indigo-600 transition-colors">{{ $organization->name }}</span>
                </a>
                
                <div class="hidden sm:flex sm:ml-8">
                    <a href="{{ route('public.home') }}" class="text-sm font-medium text-gray-500 hover:text-gray-900">
                        Browse Events
                    </a>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ route('buyer.my-tickets') }}" class="hidden sm:block text-sm font-medium text-gray-700 hover:text-indigo-600">My Tickets</a>
                    <div class="relative hidden sm:block" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                            {{ Auth::user()->name }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden" :class="{'hidden': !open}">
                            <form method="POST" action="{{ route('buyer.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('buyer.login') }}" class="hidden sm:block text-sm font-medium text-gray-700 hover:text-indigo-600">Log in</a>
                @endauth
                <a href="#tickets" class="px-5 py-2 rounded-full text-white font-medium text-sm hover:shadow-lg transition-all" style="background-color: {{ $organization->primary_color }}">
                    Get Tickets
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-bg relative w-full h-[50vh] min-h-[400px] flex items-end pb-12">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="inline-block px-3 py-1 mb-4 rounded-full bg-white/20 backdrop-blur-sm border border-white/30 text-white text-xs font-semibold tracking-wider uppercase">
                {{ ucfirst($event->type) }} Event
            </div>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight drop-shadow-md mb-4">
                {{ $event->title }}
            </h1>
            @if($event->subtitle)
                <p class="text-xl md:text-2xl text-gray-200 font-light max-w-3xl drop-shadow-sm">
                    {{ $event->subtitle }}
                </p>
            @elseif($event->short_description)
                <p class="text-xl md:text-2xl text-gray-200 font-light max-w-3xl drop-shadow-sm">
                    {{ $event->short_description }}
                </p>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Left Column: Details -->
            <div class="lg:col-span-8 space-y-10">
                
                <!-- Quick Info Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Date & Time -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Date & Time</h3>
                            <p class="text-gray-600 mt-1">{{ \Carbon\Carbon::parse($event->start_date)->format('D, M d, Y') }}</p>
                            <p class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($event->start_date)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('g:i A') }} ({{ $event->timezone }})</p>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-start gap-4">
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Location</h3>
                            @if($event->type == 'online')
                                <p class="text-gray-600 mt-1">Online Event</p>
                                <p class="text-gray-500 text-sm">Link provided after registration</p>
                            @else
                                <p class="text-gray-600 mt-1">{{ $event->venue_name ?: 'Venue TBA' }}</p>
                                @if($event->venue_address)
                                    <p class="text-gray-500 text-sm">{{ $event->venue_address }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">About This Event</h2>
                    <div class="prose prose-lg text-gray-600 max-w-none">
                        {!! nl2br($event->description) !!}
                    </div>
                </div>

                <!-- Terms & Policies -->
                @if($event->refund_policy || $event->terms)
                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Policies & Terms</h2>
                    <div class="space-y-4 text-sm text-gray-600">
                        @if($event->refund_policy)
                        <div>
                            <strong class="text-gray-800 block mb-1">Refund Policy:</strong>
                            <p>{{ $event->refund_policy }}</p>
                        </div>
                        @endif
                        
                        @if($event->terms)
                        <div>
                            <strong class="text-gray-800 block mb-1">Terms & Conditions:</strong>
                            <p>{{ $event->terms }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column: Tickets Widget -->
            <div class="lg:col-span-4" id="tickets">
                <div class="glass-panel rounded-3xl shadow-xl sticky top-24 overflow-hidden border-t-4" style="border-top-color: {{ $organization->primary_color }}">
                    <div class="p-6 bg-white">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Select Tickets</h3>
                        <p class="text-gray-500 text-sm mb-6">Sales end on {{ \Carbon\Carbon::parse($event->start_date)->format('M d') }}</p>
                        
                        @if(session('error'))
                            <div class="p-3 bg-red-50 text-red-700 rounded-lg mb-6 text-sm font-medium border border-red-100 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('public.event.checkout', ['organizationSlug' => $organization->slug, 'eventSlug' => $event->slug]) }}" method="GET">
                            <div class="space-y-4 mb-8">
                                @forelse($tickets as $ticket)
                                <div class="p-4 border border-gray-200 rounded-2xl hover:border-indigo-300 transition-colors bg-gray-50/50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-lg">{{ $ticket->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $ticket->description ?? 'Standard entry ticket' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center mt-2 pt-3 border-t border-gray-200 border-dashed">
                                        <div class="font-extrabold text-xl text-gray-900">
                                            {{ $ticket->type == 'free' ? 'FREE' : $organization->currency . ' ' . number_format($ticket->price, 2) }}
                                        </div>
                                        @php
                                            $remaining = $ticket->quantity === null ? 1000 : max(0, $ticket->quantity - $ticket->quantity_sold);
                                            $maxSelectable = min($remaining, $ticket->max_per_order ?? 10);
                                        @endphp
                                        <div class="w-32" x-data="{ count: 0, max: {{ $maxSelectable }} }">
                                            @if($maxSelectable > 0)
                                                <div class="flex items-center justify-between border border-gray-300 rounded-lg bg-white overflow-hidden shadow-sm">
                                                    <button type="button" 
                                                        @click="if(count > 0) count--" 
                                                        :class="{'text-gray-300 bg-gray-50 cursor-not-allowed': count === 0, 'text-gray-600 hover:bg-gray-100': count > 0}"
                                                        class="w-10 h-10 flex items-center justify-center transition-colors focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                    </button>
                                                    
                                                    <input type="number" 
                                                        name="tickets[{{ $ticket->id }}]" 
                                                        x-model="count" 
                                                        readonly 
                                                        class="w-12 text-center border-none focus:ring-0 text-gray-900 font-bold p-0 bg-transparent text-sm">
                                                        
                                                    <button type="button" 
                                                        @click="if(count < max) count++" 
                                                        :class="{'text-gray-300 bg-gray-50 cursor-not-allowed': count === max, 'text-gray-600 hover:bg-gray-100': count < max}"
                                                        class="w-10 h-10 flex items-center justify-center transition-colors focus:outline-none">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    </button>
                                                </div>
                                            @else
                                                <div class="px-3 py-2 bg-red-50 text-red-700 text-sm font-bold rounded-lg text-center border border-red-100">
                                                    Sold Out
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                    <div class="text-center py-6 text-gray-500">
                                        No tickets available at the moment.
                                    </div>
                                @endforelse
                            </div>
                            
                            @if($tickets->isNotEmpty())
                            <button type="submit" class="w-full py-4 px-4 border border-transparent rounded-xl shadow-lg text-lg font-bold text-white transition-all transform hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 flex justify-center items-center gap-2" style="background-color: {{ $organization->primary_color }};">
                                Proceed to Checkout
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                            @endif
                        </form>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-center gap-2 text-gray-400 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Secure Registration
                    </div>
                </div>
            </div>
            
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} {{ $organization->name }}. All rights reserved.<br>
            Powered by <a href="#" class="font-bold text-gray-800 hover:underline">Eventora</a>
        </div>
    </footer>
</body>
</html>