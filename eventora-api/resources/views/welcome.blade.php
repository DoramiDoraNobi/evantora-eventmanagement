<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Eventora') }} — Modern Event Ticketing Platform</title>
    <meta name="description" content="Eventora is a powerful multi-tenant event ticketing platform. Create, manage, and sell tickets for your events with ease.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --bg-primary: #FDFBF7;
            --bg-secondary: #F5F0E6;
            --bg-surface: #FFFFFF;
            --text-primary: #3E2723;
            --text-secondary: #6D4C41;
            --accent-primary: #B08968;
            --accent-hover: #9C6644;
            --accent-light: #DDB892;
            --border-color: #E6DCCF;
        }
        *, *::before, *::after { font-family: 'Inter', sans-serif; }
        body { background-color: var(--bg-primary); color: var(--text-primary); }

        .hero-gradient {
            background: linear-gradient(135deg, #FDFBF7 0%, #F5F0E6 50%, #E6DCCF 100%);
        }
        .hero-glow {
            background: radial-gradient(ellipse at 50% 0%, rgba(176, 137, 104, 0.15) 0%, transparent 60%);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: var(--bg-surface);
            border: 1px solid var(--border-color);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(62, 39, 35, 0.1);
            border-color: var(--accent-primary);
        }
        .feature-icon {
            background: linear-gradient(135deg, rgba(176, 137, 104, 0.1), rgba(221, 184, 146, 0.1));
            border: 1px solid rgba(176, 137, 104, 0.2);
        }
        .gradient-text {
            background: linear-gradient(135deg, #9C6644, #B08968, #DDB892);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-primary {
            background: linear-gradient(135deg, #B08968, #9C6644);
            color: #FFFFFF;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #9C6644, #7F5539);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(156, 102, 68, 0.3);
        }
        .btn-outline {
            border: 1.5px solid var(--accent-primary);
            color: var(--accent-hover);
            transition: all 0.3s ease;
        }
        .btn-outline:hover {
            background: var(--accent-primary);
            color: #FFFFFF;
        }
        .floating-badge {
            animation: float 6s ease-in-out infinite;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid var(--border-color);
            color: var(--accent-hover);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }
        .event-card-img {
            background: linear-gradient(135deg, #DDB892 0%, #B08968 100%);
        }
        .nav-blur {
            backdrop-filter: blur(16px) saturate(180%);
            background-color: rgba(253, 251, 247, 0.85);
            border-bottom: 1px solid var(--border-color);
        }
        .section-divider {
            background: linear-gradient(90deg, transparent, rgba(176, 137, 104, 0.3), transparent);
            height: 1px;
        }
        .pulse-dot {
            animation: pulse-dot 2s ease-in-out infinite;
            background-color: #10B981;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="antialiased" x-data="{ mobileMenu: false }">

    {{-- Navigation --}}
    <nav class="fixed top-0 left-0 right-0 z-50 nav-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#B08968] to-[#9C6644] flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                    <span class="text-[#3E2723] font-bold text-lg tracking-tight">Eventora</span>
                </a>

                {{-- Desktop Nav Links --}}
                <div class="hidden md:flex items-center gap-1">
                    <a href="#features" class="text-[#6D4C41] hover:text-[#9C6644] px-3 py-2 text-sm font-medium transition-colors">Features</a>
                    <a href="#events" class="text-[#6D4C41] hover:text-[#9C6644] px-3 py-2 text-sm font-medium transition-colors">Discover Events</a>
                    <a href="#pricing" class="text-[#6D4C41] hover:text-[#9C6644] px-3 py-2 text-sm font-medium transition-colors">Pricing</a>
                </div>

                {{-- Auth Buttons --}}
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @if(auth()->user()->organizations()->exists())
                            <a href="{{ url('/dashboard') }}" class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('buyer.my-tickets') }}" class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold">
                                My Tickets
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-[#6D4C41] hover:text-[#9C6644] px-4 py-2 text-sm font-medium transition-colors">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold">
                                Get Started Free
                            </a>
                        @endif
                    @endauth
                </div>

                {{-- Mobile menu toggle --}}
                <button @click="mobileMenu = !mobileMenu" class="md:hidden text-[#6D4C41] hover:text-[#9C6644] p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Mobile Menu --}}
            <div x-show="mobileMenu" x-transition class="md:hidden pb-4 border-t border-[#E6DCCF] mt-2 pt-4">
                <div class="flex flex-col gap-2">
                    <a href="#features" class="text-[#6D4C41] hover:text-[#9C6644] px-3 py-2 text-sm font-medium">Features</a>
                    <a href="#events" class="text-[#6D4C41] hover:text-[#9C6644] px-3 py-2 text-sm font-medium">Discover Events</a>
                    <a href="#pricing" class="text-[#6D4C41] hover:text-[#9C6644] px-3 py-2 text-sm font-medium">Pricing</a>
                    <div class="flex gap-3 mt-2 px-3">
                        @auth
                            @if(auth()->user()->organizations()->exists())
                                <a href="{{ url('/dashboard') }}" class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold w-full text-center">Dashboard</a>
                            @else
                                <a href="{{ route('buyer.my-tickets') }}" class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold w-full text-center">My Tickets</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="text-[#6D4C41] hover:text-[#9C6644] py-2 text-sm font-medium">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary px-5 py-2 rounded-lg text-sm font-semibold">Get Started</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="hero-gradient relative overflow-hidden">
        <div class="hero-glow absolute inset-0"></div>

        {{-- Decorative grid --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%233E2723&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20 lg:pt-40 lg:pb-28">
            <div class="text-center max-w-4xl mx-auto">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-medium mb-8 floating-badge">
                    <span class="w-2 h-2 rounded-full pulse-dot"></span>
                    Trusted by 500+ Event Organizers
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-[#3E2723] leading-tight tracking-tight mb-6">
                    Create, Sell & Manage
                    <span class="block gradient-text">Event Tickets Effortlessly</span>
                </h1>

                {{-- Subheadline --}}
                <p class="text-lg sm:text-xl text-[#6D4C41] max-w-2xl mx-auto mb-10 leading-relaxed">
                    The all-in-one ticketing platform for organizers who want a seamless experience.
                    From concerts to conferences — powered by Stripe Connect.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary px-8 py-3.5 rounded-xl text-base font-semibold w-full sm:w-auto">
                            Start Selling Tickets — It's Free
                        </a>
                    @endif
                    <a href="#events" class="btn-outline px-8 py-3.5 rounded-xl text-base font-medium w-full sm:w-auto">
                        Browse Events
                    </a>
                </div>

                {{-- Stats --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-3xl mx-auto">
                    <div class="stat-card rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-bold text-[#3E2723]">10K+</div>
                        <div class="text-sm text-[#6D4C41] mt-1">Events Created</div>
                    </div>
                    <div class="stat-card rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-bold text-[#3E2723]">500K+</div>
                        <div class="text-sm text-[#6D4C41] mt-1">Tickets Sold</div>
                    </div>
                    <div class="stat-card rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-bold text-[#3E2723]">99.9%</div>
                        <div class="text-sm text-[#6D4C41] mt-1">Uptime</div>
                    </div>
                    <div class="stat-card rounded-xl p-4">
                        <div class="text-2xl sm:text-3xl font-bold text-[#3E2723]">50+</div>
                        <div class="text-sm text-[#6D4C41] mt-1">Countries</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Wave divider --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 80" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 40L60 36C120 32 240 24 360 28C480 32 600 48 720 52C840 56 960 48 1080 40C1200 32 1320 24 1380 20L1440 16V80H1380C1320 80 1200 80 1080 80C960 80 840 80 720 80C600 80 480 80 360 80C240 80 120 80 60 80H0V40Z" fill="#FDFBF7"/>
            </svg>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="features" class="py-20 lg:py-28 bg-[#F5F0E6]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-[#9C6644] uppercase tracking-wider mb-3">Why Eventora?</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#3E2723] mb-4">Everything You Need to Run Events</h2>
                <p class="text-lg text-[#6D4C41] max-w-2xl mx-auto">From ticketing to check-in, Eventora provides all the tools organizers need in one beautiful dashboard.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {{-- Feature 1 --}}
                <div class="rounded-2xl p-7 card-hover">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#3E2723] mb-2">Multi-Tier Tickets</h3>
                    <p class="text-[#6D4C41] text-sm leading-relaxed">Create unlimited ticket types — VIP, Early Bird, General Admission, Group packages — each with custom pricing and capacity.</p>
                </div>

                {{-- Feature 2 --}}
                <div class="rounded-2xl p-7 card-hover">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#3E2723] mb-2">Stripe Connect Payments</h3>
                    <p class="text-[#6D4C41] text-sm leading-relaxed">Organizers receive funds directly to their Stripe account. Platform earns a small commission automatically on each sale.</p>
                </div>

                {{-- Feature 3 --}}
                <div class="rounded-2xl p-7 card-hover">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#3E2723] mb-2">QR Code Check-in</h3>
                    <p class="text-[#6D4C41] text-sm leading-relaxed">Each ticket gets a unique QR code. Scan attendees in seconds with our built-in scanner — no extra apps needed.</p>
                </div>

                {{-- Feature 4 --}}
                <div class="rounded-2xl p-7 card-hover">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#3E2723] mb-2">Multi-Tenant Teams</h3>
                    <p class="text-[#6D4C41] text-sm leading-relaxed">Invite team members with role-based access. Manage multiple organizations and events from a single account.</p>
                </div>

                {{-- Feature 5 --}}
                <div class="rounded-2xl p-7 card-hover">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#3E2723] mb-2">Real-Time Analytics</h3>
                    <p class="text-[#6D4C41] text-sm leading-relaxed">Track ticket sales, revenue, and attendee check-ins with beautiful dashboards and exportable reports.</p>
                </div>

                {{-- Feature 6 --}}
                <div class="rounded-2xl p-7 card-hover">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-[#3E2723] mb-2">Mobile-First Design</h3>
                    <p class="text-[#6D4C41] text-sm leading-relaxed">Every page is beautifully responsive. Attendees can buy tickets and view their QR codes from any device.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Divider --}}
    <div class="section-divider"></div>

    {{-- Discover Events Section --}}
    <section id="events" class="py-20 lg:py-28 bg-[#FDFBF7]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-[#9C6644] uppercase tracking-wider mb-3">Discover</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#3E2723] mb-4">Upcoming Events</h2>
                <p class="text-lg text-[#6D4C41] max-w-2xl mx-auto">Find and book tickets for the latest events happening near you.</p>
            </div>

            @php
                $publicEvents = \App\Models\Event::with('organization', 'tickets')
                    ->where('status', 'published')
                    ->where('end_date', '>=', now())
                    ->orderBy('start_date')
                    ->take(6)
                    ->get();
            @endphp

            @if($publicEvents->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($publicEvents as $event)
                        <a href="{{ route('public.event.show', [$event->organization->slug, $event->slug]) }}" class="group">
                            <div class="rounded-2xl overflow-hidden card-hover">
                                {{-- Event Image Placeholder --}}
                                <div class="event-card-img h-48 relative overflow-hidden flex items-center justify-center"
                                     style="background: linear-gradient(135deg,
                                        {{ $event->organization->primary_color ?? '#DDB892' }}cc,
                                        {{ $event->organization->primary_color ?? '#B08968' }}99);">
                                    <div class="absolute inset-0 bg-black/10"></div>
                                    <svg class="w-16 h-16 text-white/30 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{-- Date Badge --}}
                                    <div class="absolute top-4 left-4 bg-white/95 backdrop-blur rounded-lg px-3 py-1.5 text-center shadow-sm">
                                        <div class="text-xs font-bold text-[#9C6644] uppercase">{{ \Carbon\Carbon::parse($event->start_date)->format('M') }}</div>
                                        <div class="text-xl font-extrabold text-[#3E2723] leading-tight">{{ \Carbon\Carbon::parse($event->start_date)->format('d') }}</div>
                                    </div>
                                    {{-- Status Badge --}}
                                    @php
                                        $cheapestTicket = $event->tickets->where('is_active', true)->sortBy('price')->first();
                                    @endphp
                                    @if($cheapestTicket)
                                        <div class="absolute top-4 right-4 px-3 py-1 rounded-full text-xs font-semibold {{ $cheapestTicket->type === 'free' ? 'bg-[#10B981] text-white' : 'bg-white/95 text-[#3E2723]' }} shadow-sm">
                                            {{ $cheapestTicket->type === 'free' ? 'FREE' : ($event->organization->currency ?? 'USD') . ' ' . number_format($cheapestTicket->price, 0) }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Event Info --}}
                                <div class="p-5 bg-white">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xs font-medium text-[#9C6644] bg-[#F5F0E6] px-2 py-0.5 rounded-full">{{ ucfirst($event->type) }}</span>
                                        <span class="text-xs text-[#6D4C41]">by {{ $event->organization->name }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-[#3E2723] group-hover:text-[#9C6644] transition-colors line-clamp-2 mb-2">{{ $event->title }}</h3>
                                    <div class="flex items-center gap-1.5 text-sm text-[#6D4C41]">
                                        <svg class="w-4 h-4 text-[#B08968]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('D, M d · h:i A') }}
                                    </div>
                                    @if($event->venue_name)
                                        <div class="flex items-center gap-1.5 text-sm text-[#6D4C41] mt-1">
                                            <svg class="w-4 h-4 text-[#B08968]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            {{ $event->venue_name }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-[#F5F0E6] rounded-2xl border border-[#E6DCCF]">
                    <svg class="w-16 h-16 text-[#B08968] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h3 class="text-lg font-semibold text-[#3E2723] mb-2">No upcoming events yet</h3>
                    <p class="text-[#6D4C41] mb-6">Be the first to create an event on Eventora!</p>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary px-6 py-2.5 rounded-lg text-sm font-semibold inline-block">
                            Create Your First Event
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-20 lg:py-28 bg-[#F5F0E6]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-[#9C6644] uppercase tracking-wider mb-3">Pricing</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-[#3E2723] mb-4">Simple, Transparent Pricing</h2>
                <p class="text-lg text-[#6D4C41] max-w-2xl mx-auto">No monthly fees. No hidden charges. We only earn when you earn.</p>
            </div>

            <div class="max-w-lg mx-auto">
                <div class="bg-white rounded-2xl p-8 border border-[#E6DCCF] shadow-lg relative overflow-hidden">
                    {{-- Popular badge --}}
                    <div class="absolute top-0 right-0 bg-gradient-to-l from-[#B08968] to-[#9C6644] text-white text-xs font-bold px-4 py-1 rounded-bl-xl">
                        ONLY MODEL
                    </div>

                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-[#3E2723]">Pay As You Go</h3>
                        <p class="text-[#6D4C41] text-sm mt-1">Perfect for organizers of all sizes</p>
                    </div>

                    <div class="flex items-baseline gap-1 mb-6">
                        <span class="text-5xl font-extrabold text-[#3E2723]">{{ env('PLATFORM_FEE_PERCENT', 5) }}%</span>
                        <span class="text-[#6D4C41] text-lg">per paid ticket</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-3 text-sm text-[#6D4C41]">
                            <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Unlimited events & tickets
                        </li>
                        <li class="flex items-center gap-3 text-sm text-[#6D4C41]">
                            <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Free tickets are always free (0% fee)
                        </li>
                        <li class="flex items-center gap-3 text-sm text-[#6D4C41]">
                            <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Direct Stripe payouts to your bank
                        </li>
                        <li class="flex items-center gap-3 text-sm text-[#6D4C41]">
                            <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            QR code check-in & attendee management
                        </li>
                        <li class="flex items-center gap-3 text-sm text-[#6D4C41]">
                            <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Team collaboration & role management
                        </li>
                        <li class="flex items-center gap-3 text-sm text-[#6D4C41]">
                            <svg class="w-5 h-5 text-[#10B981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Custom branding per organization
                        </li>
                    </ul>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-primary block w-full text-center py-3 rounded-xl text-base font-semibold">
                            Get Started for Free
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="hero-gradient relative overflow-hidden py-20">
        <div class="hero-glow absolute inset-0"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-extrabold text-[#3E2723] mb-6">Ready to Sell Your First Ticket?</h2>
            <p class="text-lg text-[#6D4C41] mb-8 max-w-2xl mx-auto">Join hundreds of organizers who trust Eventora to power their events. Set up in under 5 minutes.</p>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn-primary inline-block px-8 py-3.5 rounded-xl text-base font-semibold">
                    Create Your Free Account
                </a>
            @endif
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-[#3E2723] text-[#E6DCCF] py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#B08968] to-[#9C6644] flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                        </div>
                        <span class="text-white font-bold text-lg">Eventora</span>
                    </div>
                    <p class="text-sm text-[#DDB892] leading-relaxed">The modern ticketing platform built for organizers who demand excellence.</p>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Product</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Pricing</a></li>
                        <li><a href="#events" class="hover:text-white transition-colors">Discover Events</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Support</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Documentation</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">API Reference</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Legal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-[#6D4C41] pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-[#DDB892]">&copy; {{ date('Y') }} {{ config('app.name', 'Eventora') }}. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-[#DDB892] hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="text-[#DDB892] hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
