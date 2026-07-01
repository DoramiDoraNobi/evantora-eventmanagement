<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Coupon') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('coupons.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Coupon Code -->
                            <div>
                                <x-input-label for="code" value="Coupon Code" />
                                <x-text-input id="code" name="code" type="text" class="mt-1 block w-full uppercase" value="{{ old('code') }}" required />
                                <p class="text-xs text-gray-500 mt-1">Example: SUMMER25</p>
                                <x-input-error class="mt-2" :messages="$errors->get('code')" />
                            </div>

                            <!-- Apply To Event -->
                            <div>
                                <x-input-label for="event_id" value="Apply to Event" />
                                <select id="event_id" name="event_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Events (Global)</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" {{ old('event_id') == $event->id ? 'selected' : '' }}>
                                            {{ $event->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Leave empty to apply to all your events.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('event_id')" />
                            </div>

                            <!-- Discount Type -->
                            <div>
                                <x-input-label for="type" value="Discount Type" />
                                <select id="type" name="type" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('type')" />
                            </div>

                            <!-- Discount Value -->
                            <div>
                                <x-input-label for="value" value="Discount Value" />
                                <x-text-input id="value" name="value" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('value') }}" required />
                                <x-input-error class="mt-2" :messages="$errors->get('value')" />
                            </div>

                            <!-- Max Uses -->
                            <div>
                                <x-input-label for="max_uses" value="Maximum Uses (Optional)" />
                                <x-text-input id="max_uses" name="max_uses" type="number" class="mt-1 block w-full" value="{{ old('max_uses') }}" />
                                <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited uses.</p>
                                <x-input-error class="mt-2" :messages="$errors->get('max_uses')" />
                            </div>

                            <!-- Min Order Amount -->
                            <div>
                                <x-input-label for="min_order_amount" value="Minimum Order Amount (Optional)" />
                                <x-text-input id="min_order_amount" name="min_order_amount" type="number" step="0.01" class="mt-1 block w-full" value="{{ old('min_order_amount') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('min_order_amount')" />
                            </div>

                            <!-- Start Date -->
                            <div>
                                <x-input-label for="starts_at" value="Start Date (Optional)" />
                                <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('starts_at') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <x-input-label for="expires_at" value="Expiry Date (Optional)" />
                                <x-text-input id="expires_at" name="expires_at" type="datetime-local" class="mt-1 block w-full" value="{{ old('expires_at') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('expires_at')" />
                            </div>

                            <!-- Is Active -->
                            <div class="mt-4">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-600">Active</span>
                                </label>
                            </div>

                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Save Coupon') }}</x-primary-button>
                            <a href="{{ route('coupons.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
