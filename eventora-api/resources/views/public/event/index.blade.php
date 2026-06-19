<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Eventora') }} - Event Ticketing Platform</title>
    <meta name="description" content="Launch a complete event ticketing marketplace with organizer dashboards, Stripe Connect payouts, QR check-in, buyer accounts, and branded event pages.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --ink: #2b2118;
            --muted: #766757;
            --line: #eadfcb;
            --paper: #fffaf1;
            --soft: #f6eddf;
            --sage: #6f8f72;
            --clay: #b9674f;
            --honey: #d69a3a;
            --moss: #7b8654;
        }

        html { scroll-behavior: smooth; }
        body { background: var(--soft); color: var(--ink); }
        .shell { max-width: 1180px; margin: 0 auto; }
        .brand-mark { background: conic-gradient(from 160deg, var(--sage), var(--clay), var(--honey), var(--sage)); }
        .hero-media {
            background:
                linear-gradient(135deg, rgba(43, 33, 24, .78), rgba(92, 69, 49, .52)),
                url('{{ asset('storage/events/hero.png') }}');
            background-size: cover;
            background-position: center;
        }
        .mesh {
            background:
                radial-gradient(circle at 15% 15%, rgba(111, 143, 114, .18), transparent 28%),
                radial-gradient(circle at 86% 18%, rgba(185, 103, 79, .13), transparent 24%),
                radial-gradient(circle at 70% 82%, rgba(214, 154, 58, .16), transparent 26%),
                #f6eddf;
        }
        .glass {
            background: rgba(255, 250, 241, .86);
            border: 1px solid rgba(255, 250, 241, .8);
            box-shadow: 0 24px 70px rgba(43, 33, 24, .14);
            backdrop-filter: blur(18px);
        }
        .card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 18px 42px rgba(43, 33, 24, .06);
        }
        .event-card {
            transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
        }
        .event-card:hover {
            transform: translateY(-3px);
            border-color: rgba(111, 143, 114, .45);
            box-shadow: 0 22px 55px rgba(43, 33, 24, .10);
        }
        .ticket-edge {
            background-image: radial-gradient(circle at 0 50%, transparent 0 10px, #fffaf1 11px),
                radial-gradient(circle at 100% 50%, transparent 0 10px, #fffaf1 11px);
            background-size: 51% 100%;
            background-position: left, right;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <nav class="sticky top-0 z-50 border-b border-[#eadfcb]/80 bg-[#fffaf1]/88 backdrop-blur-xl">
        <div class="shell px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                    <span class="brand-mark flex h-9 w-9 items-center justify-center rounded-lg text-sm font-black text-[#fffaf1] shadow-md">E</span>
                    <span class="text-xl font-black tracking-tight">{{ config('app.name', 'Eventora') }}</span>
                </a>

                <div class="hidden items-center gap-7 md:flex">
                    <a href="#platform" class="text-sm font-semibold text-[#766757] hover:text-[#2b2118]">Platform</a>
                    <a href="#events" class="text-sm font-semibold text-[#766757] hover:text-[#2b2118]">Events</a>
                    <a href="#revenue" class="text-sm font-semibold text-[#766757] hover:text-[#2b2118]">Revenue</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('buyer.my-tickets') }}" class="hidden text-sm font-semibold text-[#766757] hover:text-[#2b2118] sm:inline-flex">My Tickets</a>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-lg bg-[#8f4f36] px-4 py-2 text-sm font-bold text-[#fffaf1] shadow-sm hover:bg-[#75402c]">Dashboard</a>
                    @else
                        <a href="{{ route('buyer.login') }}" class="hidden text-sm font-semibold text-[#766757] hover:text-[#2b2118] sm:inline-flex">Buyer Login</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-[#8f4f36] px-4 py-2 text-sm font-bold text-[#fffaf1] shadow-sm hover:bg-[#75402c]">Start Selling</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <header class="mesh overflow-hidden">
        <div class="shell grid min-h-[calc(100vh-4rem)] items-center gap-12 px-4 py-12 sm:px-6 lg:grid-cols-[1.02fr_.98fr] lg:px-8">
            <div class="max-w-2xl">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#e7dbc7] bg-[#fffaf1] px-3 py-1.5 text-xs font-bold uppercase tracking-[.16em] text-[#766757]">
                    <span class="h-2 w-2 rounded-full bg-[#6f8f72]"></span>
                    Complete ticketing business kit
                </div>
                <h1 class="text-4xl font-black leading-[1.02] tracking-tight text-[#2b2118] sm:text-6xl lg:text-7xl">
                    Launch your own event ticket marketplace.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-[#766757]">
                    Eventora gives organizers beautiful event pages, paid and free tickets, Stripe Connect payouts, buyer accounts, QR tickets, and on-site check-in from one polished Laravel platform.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-lg bg-[#b9674f] px-6 py-3 text-sm font-black text-[#fffaf1] shadow-lg shadow-[#b9674f]/20 hover:bg-[#9f543f]">
                        Create organizer account
                    </a>
                    <a href="#events" class="inline-flex items-center justify-center rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-6 py-3 text-sm font-black text-[#2b2118] hover:border-[#6f8f72]">
                        Browse live events
                    </a>
                </div>
                <div class="mt-8 grid grid-cols-3 gap-3 max-w-xl">
                    <div class="rounded-lg border border-[#eadfcb] bg-[#fffaf1] px-4 py-3">
                        <div class="text-2xl font-black">{{ number_format($stats['events'] ?? 0) }}</div>
                        <div class="text-xs font-semibold text-[#766757]">Published events</div>
                    </div>
                    <div class="rounded-lg border border-[#eadfcb] bg-[#fffaf1] px-4 py-3">
                        <div class="text-2xl font-black">{{ number_format($stats['tickets'] ?? 0) }}</div>
                        <div class="text-xs font-semibold text-[#766757]">QR tickets</div>
                    </div>
                    <div class="rounded-lg border border-[#eadfcb] bg-[#fffaf1] px-4 py-3">
                        <div class="text-2xl font-black">{{ number_format($stats['organizers'] ?? 0) }}</div>
                        <div class="text-xs font-semibold text-[#766757]">Organizers</div>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="hero-media min-h-[540px] rounded-lg p-5 shadow-2xl shadow-[#2b2118]/18">
                    <div class="glass rounded-lg p-4">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-[.16em] text-[#766757]">Organizer dashboard</div>
                                <div class="text-2xl font-black text-[#2b2118]">Ticket sales live</div>
                            </div>
                            <span class="rounded-full bg-[#eef3dd] px-3 py-1 text-xs font-black text-[#607444]">Paid</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-[#5f4632] p-3 text-[#fffaf1]">
                                <div class="text-xs text-[#fffaf1]/70">Revenue</div>
                                <div class="mt-2 text-xl font-black">$24.8K</div>
                            </div>
                            <div class="rounded-lg bg-[#fffaf1] p-3">
                                <div class="text-xs text-[#766757]">Orders</div>
                                <div class="mt-2 text-xl font-black">1,248</div>
                            </div>
                            <div class="rounded-lg bg-[#fffaf1] p-3">
                                <div class="text-xs text-[#766757]">Check-ins</div>
                                <div class="mt-2 text-xl font-black">86%</div>
                            </div>
                        </div>
                        <div class="mt-4 rounded-lg bg-[#fffaf1] p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-black">Tonight's lineup</span>
                                <span class="text-xs font-bold text-[#6f8f72]">Scanning now</span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <span class="h-10 w-10 rounded-lg bg-[#6f8f72]"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="h-2.5 w-3/4 rounded bg-[#e7dbc7]"></div>
                                        <div class="mt-2 h-2 w-1/2 rounded bg-[#f0e6d5]"></div>
                                    </div>
                                    <span class="text-xs font-black">$89</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="h-10 w-10 rounded-lg bg-[#b9674f]"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="h-2.5 w-4/5 rounded bg-[#e7dbc7]"></div>
                                        <div class="mt-2 h-2 w-2/5 rounded bg-[#f0e6d5]"></div>
                                    </div>
                                    <span class="text-xs font-black">$49</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 left-6 right-6 rounded-lg bg-[#fffaf1] p-4 shadow-2xl sm:left-auto sm:w-[360px]">
                        <div class="ticket-edge rounded-lg border border-dashed border-[#ddcfb8] p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs font-black uppercase tracking-[.16em] text-[#b9674f]">VIP Pass</div>
                                    <div class="mt-1 text-lg font-black">Digital Marketing Summit</div>
                                </div>
                                <div class="grid h-14 w-14 grid-cols-3 gap-1 rounded bg-[#5f4632] p-1">
                                    @for($i = 0; $i < 9; $i++)
                                        <span class="rounded-sm {{ in_array($i, [1, 4, 7]) ? 'bg-[#fffaf1]' : 'bg-[#d69a3a]' }}"></span>
                                    @endfor
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-xs font-bold text-[#766757]">
                                <span>QR ticket</span>
                                <span>Instant checkout</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section id="platform" class="bg-[#fffaf1] py-20">
            <div class="shell px-4 sm:px-6 lg:px-8">
                <div class="mb-12 max-w-3xl">
                    <p class="text-sm font-black uppercase tracking-[.18em] text-[#6f8f72]">Platform</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-5xl">Built for organizers, buyers, and your marketplace revenue.</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <div class="card p-6">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-[#eef4e7] text-[#506f56]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 012 2v3a2 2 0 010 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 010-4V7a2 2 0 012-2z"/></svg>
                        </div>
                        <h3 class="text-lg font-black">Ticket inventory</h3>
                        <p class="mt-2 text-sm leading-6 text-[#766757]">Paid, free, VIP, early bird, stock limits, max per order, and sold counters.</p>
                    </div>
                    <div class="card p-6">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-[#f8e5db] text-[#8f4f36]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-black">Stripe Connect</h3>
                        <p class="mt-2 text-sm leading-6 text-[#766757]">Organizer payouts and platform fees are handled directly in the checkout flow.</p>
                    </div>
                    <div class="card p-6">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-[#faedcf] text-[#8c6424]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <h3 class="text-lg font-black">QR check-in</h3>
                        <p class="mt-2 text-sm leading-6 text-[#766757]">Issue unique tickets and scan attendees at the venue without extra tools.</p>
                    </div>
                    <div class="card p-6">
                        <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-[#eef3dd] text-[#607444]">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a5 5 0 00-10 0v2m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-black">Multi-tenant teams</h3>
                        <p class="mt-2 text-sm leading-6 text-[#766757]">Each organization has its own brand, events, members, and operation space.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="events" class="mesh py-20">
            <div class="shell px-4 sm:px-6 lg:px-8">
                <div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[.18em] text-[#b9674f]">Marketplace preview</p>
                        <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-5xl">Upcoming events, ready to sell.</h2>
                    </div>
                    @auth
                        @if(auth()->user()->organizations()->exists())
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-5 py-3 text-sm font-black text-[#2b2118] hover:border-[#6f8f72] md:w-auto">Go to Dashboard</a>
                        @else
                            <a href="{{ route('buyer.my-tickets') }}" class="inline-flex items-center justify-center rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-5 py-3 text-sm font-black text-[#2b2118] hover:border-[#6f8f72] md:w-auto">View My Tickets</a>
                        @endif
                    @else
                        <a href="{{ route('buyer.register') }}" class="inline-flex items-center justify-center rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-5 py-3 text-sm font-black text-[#2b2118] hover:border-[#b9674f] md:w-auto">Create buyer account</a>
                    @endauth
                </div>
                <div class="mb-6 flex flex-wrap gap-4 items-center">
                    <form method="GET" action="{{ route('public.home') }}#events" class="flex flex-wrap gap-4 items-center w-full">
                        <select name="sort" onchange="this.form.submit()" class="rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-4 py-2 text-sm font-semibold text-[#2b2118] focus:border-[#6f8f72] focus:ring-0">
                            <option value="">Relevance</option>
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Events</option>
                        </select>
                        <select name="filter" onchange="this.form.submit()" class="rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-4 py-2 text-sm font-semibold text-[#2b2118] focus:border-[#6f8f72] focus:ring-0">
                            <option value="">All Dates</option>
                            <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="14_days" {{ request('filter') == '14_days' ? 'selected' : '' }}>Next 14 Days</option>
                            <option value="30_days" {{ request('filter') == '30_days' ? 'selected' : '' }}>Next 30 Days</option>
                        </select>

                        <select name="type" onchange="this.form.submit()" class="rounded-lg border border-[#ddcfb8] bg-[#fffaf1] px-4 py-2 text-sm font-semibold text-[#2b2118] focus:border-[#6f8f72] focus:ring-0">
                            <option value="">All Types</option>
                            <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline / In-person</option>
                            <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online / Virtual</option>
                            <option value="hybrid" {{ request('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                    </form>
                </div>

                @if($events->count() > 0)
                    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($events as $event)
                            @php
                                $activeTickets = $event->tickets->where('is_active', true);
                                $cheapestTicket = $activeTickets->sortBy('price')->first();
                                $currency = $event->organization->currency ?? 'USD';
                                $eventDate = \Carbon\Carbon::parse($event->start_date);
                            @endphp
                            <a href="{{ route('public.event.show', ['organizationSlug' => $event->organization->slug, 'eventSlug' => $event->slug]) }}" class="event-card card group overflow-hidden">
                                <div class="relative h-56 overflow-hidden bg-[#5f4632]">
                                    @if($event->hero_image)
                                        <img src="{{ asset('storage/' . $event->hero_image) }}" alt="{{ $event->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#5f4632] via-[#9b7656] to-[#d69a3a]">
                                            <svg class="h-16 w-16 text-[#fffaf1]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                                    <div class="absolute left-4 top-4 rounded-lg bg-[#fffaf1] px-3 py-2 text-center shadow-md">
                                        <div class="text-xs font-black uppercase text-[#b9674f]">{{ $eventDate->format('M') }}</div>
                                        <div class="text-2xl font-black leading-none">{{ $eventDate->format('d') }}</div>
                                    </div>
                                    <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="mb-2 inline-flex rounded-full bg-[#fffaf1]/90 px-2.5 py-1 text-xs font-black text-[#2b2118]">{{ ucfirst($event->type) }}</div>
                                            <h3 class="line-clamp-2 text-xl font-black leading-tight text-[#fffaf1]">{{ $event->title }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-5">
                                    <div class="mb-4 flex items-center gap-2">
                                        @if($event->organization->logo)
                                            <img src="{{ asset('storage/' . $event->organization->logo) }}" alt="{{ $event->organization->name }}" class="h-8 w-8 rounded-lg object-cover">
                                        @else
                                            <span class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-black text-[#fffaf1]" style="background-color: {{ $event->organization->primary_color ?? '#6f8f72' }}">{{ substr($event->organization->name, 0, 1) }}</span>
                                        @endif
                                        <span class="truncate text-sm font-bold text-[#766757]">{{ $event->organization->name }}</span>
                                    </div>
                                    <div class="space-y-2 text-sm text-[#766757]">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-[#6f8f72]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $eventDate->format('D, M d, Y') }}
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-[#b9674f]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="truncate">{{ $event->type === 'online' ? 'Online event' : ($event->venue_name ?? 'Venue TBA') }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-5 flex items-center justify-between border-t border-[#eadfcb] pt-4">
                                        <span class="text-xs font-black uppercase tracking-[.14em] text-[#766757]">From</span>
                                        <span class="text-lg font-black text-[#2b2118]">
                                            @if($cheapestTicket)
                                                {{ $cheapestTicket->type === 'free' || (float) $cheapestTicket->price === 0.0 ? 'FREE' : $currency . ' ' . number_format($cheapestTicket->price, 0) }}
                                            @else
                                                TBA
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $events->links() }}
                    </div>
                @else
                    <div class="card bg-[#fffaf1] p-10 text-center">
                        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-lg bg-[#eef4e7] text-[#506f56]">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-2xl font-black">No published events yet</h3>
                        <p class="mx-auto mt-3 max-w-lg text-[#766757]">Create the first event and this marketplace section will instantly become a live discovery page for buyers.</p>
                        <a href="{{ route('register') }}" class="mt-6 inline-flex rounded-lg bg-[#8f4f36] px-5 py-3 text-sm font-black text-[#fffaf1] hover:bg-[#75402c]">Create first event</a>
                    </div>
                @endif
            </div>
        </section>

        <section id="revenue" class="bg-[#efe1cb] py-20 text-[#2b2118]">
            <div class="shell grid gap-10 px-4 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
                <div>
                    <p class="text-sm font-black uppercase tracking-[.18em] text-[#8f4f36]">Revenue engine</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-5xl">A marketplace model that makes sense.</h2>
                    <p class="mt-5 text-lg leading-8 text-[#766757]">Let organizers sell under their own brand while your platform earns a configurable fee on paid ticket sales.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg border border-[#ddcfb8] bg-[#fffaf1] p-6 shadow-sm">
                        <div class="text-3xl font-black">{{ env('PLATFORM_FEE_PERCENT', 5) }}%</div>
                        <div class="mt-2 text-sm font-semibold text-[#766757]">Default platform fee</div>
                    </div>
                    <div class="rounded-lg border border-[#ddcfb8] bg-[#fffaf1] p-6 shadow-sm">
                        <div class="text-3xl font-black">{{ number_format($stats['orders'] ?? 0) }}</div>
                        <div class="mt-2 text-sm font-semibold text-[#766757]">Orders tracked</div>
                    </div>
                    <div class="rounded-lg border border-[#ddcfb8] bg-[#fffaf1] p-6 shadow-sm">
                        <div class="text-3xl font-black">24/7</div>
                        <div class="mt-2 text-sm font-semibold text-[#766757]">Online ticket sales</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#fffaf1] py-16">
            <div class="shell px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-start justify-between gap-6 rounded-lg border border-[#eadfcb] bg-[#f6eddf] p-8 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-3xl font-black tracking-tight">Ready to publish your next event?</h2>
                        <p class="mt-2 max-w-2xl text-[#766757]">Create an organizer account, connect Stripe, add tickets, and start selling with QR-ready checkout.</p>
                    </div>
                    <a href="{{ route('register') }}" class="inline-flex shrink-0 rounded-lg bg-[#6f8f72] px-6 py-3 text-sm font-black text-[#fffaf1] shadow-lg shadow-[#6f8f72]/20 hover:bg-[#5b795e]">Start now</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-[#eadfcb] bg-[#fffaf1] py-8">
        <div class="shell flex flex-col items-center justify-between gap-4 px-4 text-sm text-[#766757] sm:px-6 md:flex-row lg:px-8">
            <div class="flex items-center gap-3">
                <span class="brand-mark flex h-8 w-8 items-center justify-center rounded-lg text-xs font-black text-[#fffaf1]">E</span>
                <span class="font-bold">&copy; {{ date('Y') }} {{ config('app.name', 'Eventora') }}</span>
            </div>
            <div class="flex items-center gap-5 font-semibold">
                <a href="{{ route('tickets.lookup') }}" class="hover:text-[#2b2118]">Find ticket</a>
                <a href="{{ route('buyer.login') }}" class="hover:text-[#2b2118]">Buyer login</a>
                <a href="{{ route('login') }}" class="hover:text-[#2b2118]">Organizer login</a>
            </div>
        </div>
    </footer>
</body>
</html>
