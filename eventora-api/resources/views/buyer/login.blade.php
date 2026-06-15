<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buyer Login - {{ config('app.name', 'Eventora') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6eddf] font-sans text-[#2b2118] antialiased" style="background: radial-gradient(circle at 12% 12%, rgba(111, 143, 114, .18), transparent 28%), radial-gradient(circle at 88% 18%, rgba(185, 103, 79, .13), transparent 24%), radial-gradient(circle at 70% 85%, rgba(214, 154, 58, .16), transparent 26%), #f6eddf;">
    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full max-w-6xl items-center gap-10 lg:grid-cols-[1fr_440px]">
            <section class="hidden lg:block">
                <a href="{{ route('public.home') }}" class="inline-flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black text-[#fffaf1] shadow-md" style="background: conic-gradient(from 160deg, #6f8f72, #b9674f, #d69a3a, #6f8f72);">E</span>
                    <span class="text-2xl font-black tracking-tight">{{ config('app.name', 'Eventora') }}</span>
                </a>
                <h1 class="mt-10 max-w-xl text-5xl font-black leading-tight tracking-tight">
                    Your tickets, receipts, and QR passes in one place.
                </h1>
                <p class="mt-5 max-w-lg text-lg leading-8 text-[#766757]">
                    Sign in to view upcoming events, download tickets, and keep your check-in passes ready for entry.
                </p>
                <div class="mt-8 rounded-lg border border-[#eadfcb] bg-[#fffaf1] p-5 shadow-xl shadow-[#2b2118]/8">
                    <div class="flex items-center justify-between border-b border-dashed border-[#ddcfb8] pb-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[.18em] text-[#b9674f]">QR pass</p>
                            <p class="mt-1 text-xl font-black">Digital Summit VIP</p>
                        </div>
                        <div class="grid h-14 w-14 grid-cols-3 gap-1 rounded bg-[#5f4632] p-1">
                            @for($i = 0; $i < 9; $i++)
                                <span class="rounded-sm {{ in_array($i, [1, 4, 7]) ? 'bg-[#fffaf1]' : 'bg-[#d69a3a]' }}"></span>
                            @endfor
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between text-sm font-bold text-[#766757]">
                        <span>Instant access</span>
                        <span>Mobile ready</span>
                    </div>
                </div>
            </section>

            <section class="w-full rounded-lg border border-[#eadfcb] bg-[#fffaf1] p-7 shadow-2xl shadow-[#2b2118]/10 sm:p-8">
                <div class="mb-7 text-center lg:text-left">
                    <a href="{{ route('public.home') }}" class="mb-7 inline-flex items-center gap-3 lg:hidden">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg text-sm font-black text-[#fffaf1] shadow-md" style="background: conic-gradient(from 160deg, #6f8f72, #b9674f, #d69a3a, #6f8f72);">E</span>
                        <span class="text-2xl font-black tracking-tight">{{ config('app.name', 'Eventora') }}</span>
                    </a>
                    <p class="text-xs font-black uppercase tracking-[.18em] text-[#6f8f72]">Buyer access</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight">Welcome back</h1>
                    <p class="mt-2 text-sm leading-6 text-[#766757]">Sign in to manage purchased tickets.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-[#e7c7b9] bg-[#fbefe8] px-4 py-3 text-sm font-semibold text-[#8f4f36]">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('buyer.login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-bold text-[#2b2118]">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-1 block w-full rounded-lg border-[#ddcfb8] bg-[#fffaf1] px-4 py-3 text-[#2b2118] shadow-sm focus:border-[#6f8f72] focus:ring-[#6f8f72]">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-bold text-[#2b2118]">Password</label>
                        <input id="password" type="password" name="password" required class="mt-1 block w-full rounded-lg border-[#ddcfb8] bg-[#fffaf1] px-4 py-3 text-[#2b2118] shadow-sm focus:border-[#6f8f72] focus:ring-[#6f8f72]">
                    </div>

                    <label for="remember_me" class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-[#ddcfb8] text-[#6f8f72] shadow-sm focus:ring-[#6f8f72]">
                        <span class="ml-2 text-sm font-semibold text-[#766757]">Remember me</span>
                    </label>

                    <button type="submit" class="flex w-full justify-center rounded-lg border border-transparent bg-[#8f4f36] px-5 py-3 text-sm font-black text-[#fffaf1] shadow-sm transition hover:bg-[#75402c] focus:outline-none focus:ring-2 focus:ring-[#6f8f72] focus:ring-offset-2">
                        Sign in
                    </button>
                </form>

                <div class="mt-6 space-y-4 border-t border-[#eadfcb] pt-5 text-center text-sm font-semibold text-[#766757]">
                    <p>Don't have an account? <a href="{{ route('buyer.register') }}" class="font-black text-[#b9674f] hover:text-[#9f543f]">Sign up</a></p>
                    <p>Bought as a guest? <a href="{{ route('tickets.lookup') }}" class="font-black text-[#6f8f72] hover:text-[#5b795e]">Lookup tickets by email</a></p>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
