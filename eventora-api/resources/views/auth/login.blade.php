<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-black uppercase tracking-[.18em] text-[#6f8f72]">Organizer access</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-[#2b2118]">Welcome back</h1>
        <p class="mt-2 text-sm leading-6 text-[#766757]">Sign in to manage events, tickets, teams, payouts, and check-ins.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[#ddcfb8] text-[#6f8f72] shadow-sm focus:ring-[#6f8f72]" name="remember">
                <span class="ms-2 text-sm font-semibold text-[#766757]">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-4">
            @if (Route::has('password.request'))
                <a class="text-sm font-bold text-[#766757] hover:text-[#2b2118] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#6f8f72]" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="mt-6 border-t border-[#eadfcb] pt-5 text-center text-sm font-semibold text-[#766757]">
            New organizer?
            <a href="{{ route('register') }}" class="font-black text-[#b9674f] hover:text-[#9f543f]">Create an account</a>
        </div>
    </form>
</x-guest-layout>
