<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Eventora') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-[#2b2118] antialiased bg-[#f6eddf]">
        <div class="min-h-screen px-4 py-8 sm:px-6 lg:px-8" style="background: radial-gradient(circle at 12% 12%, rgba(111, 143, 114, .18), transparent 28%), radial-gradient(circle at 88% 18%, rgba(185, 103, 79, .13), transparent 24%), radial-gradient(circle at 70% 85%, rgba(214, 154, 58, .16), transparent 26%), #f6eddf;">
            <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center">
                <div class="hidden flex-1 pr-12 lg:block">
                    <a href="/" class="inline-flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black text-[#fffaf1] shadow-md" style="background: conic-gradient(from 160deg, #6f8f72, #b9674f, #d69a3a, #6f8f72);">E</span>
                        <span class="text-2xl font-black tracking-tight">{{ config('app.name', 'Eventora') }}</span>
                    </a>
                    <h1 class="mt-10 max-w-xl text-5xl font-black leading-tight tracking-tight">
                        Run event sales from one polished dashboard.
                    </h1>
                    <p class="mt-5 max-w-lg text-lg leading-8 text-[#766757]">
                        Create branded event pages, sell paid or free tickets, collect Stripe payouts, and scan QR tickets at the door.
                    </p>
                    <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">
                        <div class="rounded-lg border border-[#eadfcb] bg-[#fffaf1] p-4">
                            <div class="text-2xl font-black text-[#6f8f72]">QR</div>
                            <div class="mt-1 text-xs font-bold text-[#766757]">Ticket scanner</div>
                        </div>
                        <div class="rounded-lg border border-[#eadfcb] bg-[#fffaf1] p-4">
                            <div class="text-2xl font-black text-[#b9674f]">5%</div>
                            <div class="mt-1 text-xs font-bold text-[#766757]">Platform fee</div>
                        </div>
                        <div class="rounded-lg border border-[#eadfcb] bg-[#fffaf1] p-4">
                            <div class="text-2xl font-black text-[#d69a3a]">24/7</div>
                            <div class="mt-1 text-xs font-bold text-[#766757]">Online sales</div>
                        </div>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-md">
                    <div class="mb-7 text-center lg:hidden">
                        <a href="/" class="inline-flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black text-[#fffaf1] shadow-md" style="background: conic-gradient(from 160deg, #6f8f72, #b9674f, #d69a3a, #6f8f72);">E</span>
                            <span class="text-2xl font-black tracking-tight">{{ config('app.name', 'Eventora') }}</span>
                        </a>
                    </div>

                    <div class="w-full overflow-hidden rounded-lg border border-[#eadfcb] bg-[#fffaf1] p-7 shadow-2xl shadow-[#2b2118]/10 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
