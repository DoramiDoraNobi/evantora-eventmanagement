<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Coupon: ') }} {{ $coupon->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Coupon Code -->
                            <div>
                                <x-input-label for="code" value="Coupon Code" />
                                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full uppercase" value="{{ old('code', $coupon->code) }}" required />
                                <x-input-error class="mt-2" :messages="$errors->get('code')" />
                            </div>

                            <!-- Apply To Event -->
                            <div>
                                <x-input-label for="event_id" value="Apply to Event" />
                                <select id="event_id" name="event_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Events (Global)</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id', $coupon->event_id) == $event->id ? 'selected' : '' }}>
                                            {{ $event->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('event_id')" />
                            </div>

                            <!-- Discount Type -->
                            <div>
                                <x-input-label for="type" value="Discount Type" />
                                <select id="type" name="type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="percentage" {{ old('type', $coupon->type) == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('type')" />
                            </div>

                            <!-- Discount Value -->
                            <div>
                                <x-input-label for="value" value="Discount Value" />
                                <x-text-input id="value" name="value" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('value', $coupon->value) }}" required />
                                <x-input-error class="mt-2" :messages="$errors->get('value')" />
                            </div>

                            <!-- Max Uses -->
                            <div>
                                <x-input-label for="max_uses" value="Maximum Uses" />
                                <x-text-input id="max_uses" name="max_uses" type="number" class="mt-1 block w-full" value="{{ old('max_uses', $coupon->max_uses) }}" />
                                <p class="text-xs text-gray-500 mt-1">Currently used: {{ $coupon->used_count }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('max_uses')" />
                            </div>

                            <!-- Min Order Amount -->
                            <div>
                                <x-input-label for="min_order_amount" value="Minimum Order Amount" />
                                <x-text-input id="min_order_amount" name="min_order_amount" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('min_order_amount')" />
                            </div>

                            <!-- Start Date -->
                            <div>
                                <x-input-label for="starts_at" value="Start Date" />
                                <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <x-input-label for="expires_at" value="Expiry Date" />
                                <x-text-input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('expires_at')" />
                            </div>

                            <!-- Is Active -->
                            <div class="mt-4">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>

                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Update Coupon') }}</x-primary-button>
                            <a href="{{ route('coupons.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
