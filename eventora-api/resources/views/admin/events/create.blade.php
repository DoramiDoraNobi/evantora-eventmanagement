<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Event') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Validation Errors Summary --}}
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <h3 class="font-semibold text-red-800 text-sm">Please fix the following errors:</h3>
                            </div>
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ eventType: '{{ old('type', 'offline') }}' }">
                        @csrf
                        <div>
                            <x-input-label for="title" :value="__('Event Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="hero_image" :value="__('Hero Image / Banner')" />
                            <input id="hero_image" name="hero_image" type="file" class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100
                            " accept="image/*" />
                            <x-input-error class="mt-2" :messages="$errors->get('hero_image')" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="type" :value="__('Event Type')" />
                                <select id="type" name="type" x-model="eventType" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="offline" {{ old('type') == 'offline' ? 'selected' : '' }}>Offline / In-person</option>
                                    <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online / Virtual</option>
                                    <option value="hybrid" {{ old('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('type')" />
                            </div>
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('status')" />
                            </div>
                        </div>

                        {{-- Venue Name (shown for offline/hybrid) --}}
                        <div x-show="eventType === 'offline' || eventType === 'hybrid'" x-transition>
                            <x-input-label for="venue_name" :value="__('Venue Name')" />
                            <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full" :value="old('venue_name')" placeholder="e.g. Jakarta Convention Center" />
                            <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                        </div>

                        {{-- Online URL (shown for online/hybrid) --}}
                        <div x-show="eventType === 'online' || eventType === 'hybrid'" x-transition>
                            <x-input-label for="online_url" :value="__('Online Event URL')" />
                            <x-text-input id="online_url" name="online_url" type="url" class="mt-1 block w-full" :value="old('online_url')" placeholder="e.g. https://zoom.us/j/123456" />
                            <x-input-error class="mt-2" :messages="$errors->get('online_url')" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date & Time')" />
                                <x-text-input id="start_date" name="start_date" type="datetime-local" class="mt-1 block w-full" :value="old('start_date')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('End Date & Time')" />
                                <x-text-input id="end_date" name="end_date" type="datetime-local" class="mt-1 block w-full" :value="old('end_date')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="timezone" :value="__('Timezone')" />
                            <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" value="{{ old('timezone', 'Asia/Jakarta') }}" required />
                            <p class="text-xs text-gray-500 mt-1">Example: UTC, Asia/Jakarta</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Create & Continue') }}</x-primary-button>
                            <a href="{{ route('events.index') }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>