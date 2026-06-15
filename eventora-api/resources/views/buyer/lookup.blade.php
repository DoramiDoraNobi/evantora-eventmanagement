<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lookup Tickets - {{ config('app.name', 'Eventora') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="text-gray-900 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0" style="background: linear-gradient(135deg, #0f0a2e 0%, #1a1145 25%, #2d1b69 50%, #1a1145 75%, #0f0a2e 100%);"></div>
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 0%, rgba(139, 92, 246, 0.15) 0%, transparent 60%);"></div>
    </div>
    
    <div class="max-w-md w-full relative z-10">
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                </div>
            </a>
            <h1 class="mt-6 text-3xl font-extrabold text-white">Find Your Tickets</h1>
            <p class="mt-2 text-sm text-gray-300">Enter the email you used during checkout</p>
        </div>

        <div class="auth-card rounded-2xl p-8">
            <form method="POST" action="{{ route('tickets.lookup') }}">
                @csrf

                @if (session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm flex items-start gap-2">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="space-y-5">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="e.g., john@example.com" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-violet-500 focus:border-violet-500">
                    </div>

                    <div>
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-violet-500 transition-all">
                            Find My Tickets
                        </button>
                    </div>
                </div>
            </form>

            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Have an account? <a href="{{ route('buyer.login') }}" class="font-bold text-violet-600 hover:text-violet-500">Sign in</a></p>
            </div>
        </div>
    </div>
</body>
</html>
