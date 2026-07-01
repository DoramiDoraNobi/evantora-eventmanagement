<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Global Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('super-admin.settings.update') }}">
                        @csrf
                        
                        <h3 class="text-lg font-bold mb-4">Platform Fee Settings</h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Configure the service fee that is charged to the buyer during checkout. Free tickets will not be charged a service fee. The fee is calculated from the subtotal after any discounts.
                        </p>

                        <!-- Platform Fee Percent -->
                        <div class="mb-4">
                            <x-input-label for="platform_fee_percent" :value="__('Platform Fee Percent (%)')" />
                            <x-text-input id="platform_fee_percent" class="block mt-1 w-full" type="number" step="0.01" name="platform_fee_percent" :value="old('platform_fee_percent', $platformFeePercent)" required />
                            <x-input-error :messages="$errors->get('platform_fee_percent')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">E.g. 5 for 5%.</p>
                        </div>

                        <!-- Platform Fee Fixed -->
                        <div class="mb-6">
                            <x-input-label for="platform_fee_fixed" :value="__('Platform Fee Fixed Amount (Optional)')" />
                            <x-text-input id="platform_fee_fixed" class="block mt-1 w-full" type="number" step="0.01" name="platform_fee_fixed" :value="old('platform_fee_fixed', $platformFeeFixed)" required />
                            <x-input-error :messages="$errors->get('platform_fee_fixed')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">A fixed fee added on top of the percentage. E.g. 2000 for Rp2.000.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4 border-t pt-4">
                            <x-primary-button>
                                {{ __('Save Platform Fee Settings') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>

            <!-- Midtrans Gateway Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('super-admin.settings.update') }}">
                        @csrf
                        
                        <h3 class="text-lg font-bold mb-4">Midtrans Payment Gateway</h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Configure Midtrans API keys for local Indonesian payments.
                        </p>

                        <div class="mb-4">
                            <x-input-label for="midtrans_server_key" :value="__('Server Key')" />
                            <x-text-input id="midtrans_server_key" class="block mt-1 w-full" type="text" name="midtrans_server_key" :placeholder="$midtransServerKey ? '****************' . substr($midtransServerKey, -5) : 'e.g. SB-Mid-server-...'" />
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing key.</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="midtrans_client_key" :value="__('Client Key')" />
                            <x-text-input id="midtrans_client_key" class="block mt-1 w-full" type="text" name="midtrans_client_key" :placeholder="$midtransClientKey ? '****************' . substr($midtransClientKey, -5) : 'e.g. SB-Mid-client-...'" />
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing key.</p>
                        </div>

                        <div class="mb-6 flex items-center">
                            <input id="midtrans_is_production" type="checkbox" name="midtrans_is_production" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ $midtransIsProduction ? 'checked' : '' }}>
                            <label for="midtrans_is_production" class="ml-2 block text-sm text-gray-900">
                                Use Production Environment
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4 border-t pt-4">
                            <x-primary-button>
                                {{ __('Save Midtrans Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PayPal Gateway Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('super-admin.settings.update') }}">
                        @csrf
                        
                        <h3 class="text-lg font-bold mb-4">PayPal Payment Gateway</h3>
                        <p class="text-sm text-gray-600 mb-6">
                            Configure PayPal API keys for international payments and organizer payouts.
                        </p>

                        <div class="mb-4">
                            <x-input-label for="paypal_client_id" :value="__('Client ID')" />
                            <x-text-input id="paypal_client_id" class="block mt-1 w-full" type="text" name="paypal_client_id" :placeholder="$paypalClientId ? '****************' . substr($paypalClientId, -5) : 'Enter Client ID'" />
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing Client ID.</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="paypal_secret" :value="__('Secret Key')" />
                            <x-text-input id="paypal_secret" class="block mt-1 w-full" type="password" name="paypal_secret" :placeholder="$paypalSecret ? '****************' . substr($paypalSecret, -5) : 'Enter Secret Key'" />
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing Secret Key.</p>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="paypal_mode" :value="__('Environment Mode')" />
                            <select id="paypal_mode" name="paypal_mode" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="sandbox" {{ $paypalMode == 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                                <option value="live" {{ $paypalMode == 'live' ? 'selected' : '' }}>Live (Production)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end mt-4 border-t pt-4">
                            <x-primary-button>
                                {{ __('Save PayPal Settings') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
