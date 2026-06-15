<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organization Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            @if(session('status') && session('status') !== 'organization-updated')
                <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            {{-- Stripe Connect Panel --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Payment Gateway') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Connect your Stripe account to receive payments directly for your paid tickets.') }}
                        </p>
                    </header>

                    <div class="mt-6">
                        @if(app('current_organization')->stripe_account_id)
                            <div class="flex items-center gap-4 bg-green-50 border border-green-200 p-4 rounded-lg">
                                <svg class="w-8 h-8 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <h3 class="font-bold text-green-800">Connected to Stripe</h3>
                                    <p class="text-sm text-green-700">Account ID: {{ app('current_organization')->stripe_account_id }}</p>
                                </div>
                                <div class="ml-auto">
                                    <form action="{{ route('stripe.disconnect') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Disconnect</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-6 bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-200 p-6 rounded-lg">
                                <div class="shrink-0">
                                    <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-900">Accept Payments Online</h3>
                                    <p class="text-sm text-gray-600 mt-1">Connect with Stripe to automatically process credit card payments and receive funds directly to your bank account.</p>
                                </div>
                                <div class="shrink-0">
                                    <a href="{{ route('stripe.connect') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#635BFF] border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-[#4B45C6] focus:outline-none focus:ring-2 focus:ring-[#635BFF] focus:ring-offset-2 transition ease-in-out duration-150">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/></svg>
                                        Connect with Stripe
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Organization Profile') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Update your organization's profile information.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('organization.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="logo" :value="__('Organization Logo')" />
                                @if($organization->logo)
                                    <div class="mt-2 mb-4">
                                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="Current Logo" class="h-20 w-20 object-cover rounded-lg border border-gray-200">
                                    </div>
                                @endif
                                <input id="logo" name="logo" type="file" class="mt-1 block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-md file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-indigo-50 file:text-indigo-700
                                  hover:file:bg-indigo-100
                                " accept="image/*" />
                                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                            </div>

                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $organization->name)" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="slug" :value="__('Slug (URL)')" />
                                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $organization->slug)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $organization->email)" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone')" />
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $organization->phone)" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <div>
                                <x-input-label for="primary_color" :value="__('Primary Color (Hex)')" />
                                <x-text-input id="primary_color" name="primary_color" type="color" class="mt-1 block w-full h-10 p-1" :value="old('primary_color', $organization->primary_color)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('primary_color')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save') }}</x-primary-button>

                                @if (session('status') === 'organization-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >{{ __('Saved.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Notification Preferences') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Manage when and how you receive notifications.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('organization.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="space-y-4">
                                <label for="notify_daily_sales" class="inline-flex items-center">
                                    <input id="notify_daily_sales" type="checkbox" class="rounded border-gray-300 text-[#9C6644] shadow-sm focus:ring-[#9C6644]" name="notify_daily_sales" {{ ($organization->settings['notify_daily_sales'] ?? false) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Receive Daily Sales Summary') }}</span>
                                </label>
                                <br>
                                <label for="notify_new_order" class="inline-flex items-center">
                                    <input id="notify_new_order" type="checkbox" class="rounded border-gray-300 text-[#9C6644] shadow-sm focus:ring-[#9C6644]" name="notify_new_order" {{ ($organization->settings['notify_new_order'] ?? false) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Receive Notification per New Order') }}</span>
                                </label>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save Preferences') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Custom Mail Server (SMTP)') }}
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ __("Configure your own SMTP server to send tickets to buyers from your own email domain instead of the platform's default email.") }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('organization.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="smtp_host" :value="__('SMTP Host')" />
                                    <x-text-input id="smtp_host" name="smtp_host" type="text" class="mt-1 block w-full" :value="old('smtp_host', $organization->smtp_host)" placeholder="e.g. smtp.mailtrap.io" />
                                    <x-input-error class="mt-2" :messages="$errors->get('smtp_host')" />
                                </div>

                                <div>
                                    <x-input-label for="smtp_port" :value="__('SMTP Port')" />
                                    <x-text-input id="smtp_port" name="smtp_port" type="number" class="mt-1 block w-full" :value="old('smtp_port', $organization->smtp_port)" placeholder="e.g. 587 or 465" />
                                    <x-input-error class="mt-2" :messages="$errors->get('smtp_port')" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="smtp_username" :value="__('SMTP Username')" />
                                    <x-text-input id="smtp_username" name="smtp_username" type="text" class="mt-1 block w-full" :value="old('smtp_username', $organization->smtp_username)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('smtp_username')" />
                                </div>

                                <div>
                                    <x-input-label for="smtp_password" :value="__('SMTP Password')" />
                                    <x-text-input id="smtp_password" name="smtp_password" type="password" class="mt-1 block w-full" :value="old('smtp_password', $organization->smtp_password)" />
                                    <x-input-error class="mt-2" :messages="$errors->get('smtp_password')" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="smtp_from_email" :value="__('From Email Address')" />
                                    <x-text-input id="smtp_from_email" name="smtp_from_email" type="email" class="mt-1 block w-full" :value="old('smtp_from_email', $organization->smtp_from_email)" placeholder="e.g. no-reply@yourdomain.com" />
                                    <x-input-error class="mt-2" :messages="$errors->get('smtp_from_email')" />
                                </div>

                                <div>
                                    <x-input-label for="smtp_from_name" :value="__('From Name')" />
                                    <x-text-input id="smtp_from_name" name="smtp_from_name" type="text" class="mt-1 block w-full" :value="old('smtp_from_name', $organization->smtp_from_name)" placeholder="e.g. Your Event Team" />
                                    <x-input-error class="mt-2" :messages="$errors->get('smtp_from_name')" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save SMTP Settings') }}</x-primary-button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
