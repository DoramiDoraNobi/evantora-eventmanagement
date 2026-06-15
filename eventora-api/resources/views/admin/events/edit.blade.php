<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Event: ') }} {{ $event->title }}
            </h2>
            <a href="{{ route('events.tickets.index', $event->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                Manage Tickets
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('events.update', $event->id) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('patch')
                        
                        <div>
                            <x-input-label for="title" :value="__('Event Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $event->title)" required autofocus />
                        </div>

                        <div>
                            <x-input-label for="hero_image" :value="__('Hero Image / Banner')" />
                            @if($event->hero_image)
                                <div class="mt-2 mb-4">
                                    <img src="{{ asset('storage/' . $event->hero_image) }}" alt="Hero Image" class="h-32 w-full object-cover rounded-lg border border-gray-200">
                                </div>
                            @endif
                            <input id="hero_image" name="hero_image" type="file" class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100
                            " accept="image/*" />
                            <x-input-error class="mt-2" :messages="$errors->get('hero_image')" />
                        </div>
                        
                        <div>
                            <x-input-label for="slug" :value="__('Slug (URL)')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $event->slug)" required />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
                            <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
                            <input id="description" type="hidden" name="description" value="{{ old('description', $event->description) }}">
                            <trix-editor input="description" class="trix-content mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm min-h-[200px] bg-white"></trix-editor>
                            
                            <script>
                                (function() {
                                    var HOST = "{{ route('upload.image') }}";

                                    document.addEventListener("trix-attachment-add", function(event) {
                                        if (event.attachment.file) {
                                            uploadFileAttachment(event.attachment);
                                        }
                                    });

                                    function uploadFileAttachment(attachment) {
                                        uploadFile(attachment.file, setProgress, setAttributes);

                                        function setProgress(progress) {
                                            attachment.setUploadProgress(progress);
                                        }

                                        function setAttributes(attributes) {
                                            attachment.setAttributes(attributes);
                                        }
                                    }

                                    function uploadFile(file, progressCallback, successCallback) {
                                        var formData = new FormData();
                                        var xhr = new XMLHttpRequest();
                                        
                                        formData.append("file", file);
                                        formData.append("_token", document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                                        xhr.open("POST", HOST, true);

                                        xhr.upload.addEventListener("progress", function(event) {
                                            var progress = event.loaded / event.total * 100;
                                            progressCallback(progress);
                                        });

                                        xhr.addEventListener("load", function(event) {
                                            if (xhr.status == 200) {
                                                var response = JSON.parse(xhr.responseText);
                                                successCallback({
                                                    url: response.url,
                                                    href: response.url
                                                });
                                            }
                                        });

                                        xhr.send(formData);
                                    }
                                })();
                            </script>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="type" :value="__('Event Type')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="offline" {{ $event->type == 'offline' ? 'selected' : '' }}>Offline / In-person</option>
                                    <option value="online" {{ $event->type == 'online' ? 'selected' : '' }}>Online / Virtual</option>
                                    <option value="hybrid" {{ $event->type == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="draft" {{ $event->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ $event->status == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="cancelled" {{ $event->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="start_date" :value="__('Start Date & Time')" />
                                <x-text-input id="start_date" name="start_date" type="datetime-local" class="mt-1 block w-full" :value="old('start_date', date('Y-m-d\TH:i', strtotime($event->start_date)))" required />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('End Date & Time')" />
                                <x-text-input id="end_date" name="end_date" type="datetime-local" class="mt-1 block w-full" :value="old('end_date', date('Y-m-d\TH:i', strtotime($event->end_date)))" required />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="timezone" :value="__('Timezone')" />
                            <x-text-input id="timezone" name="timezone" type="text" class="mt-1 block w-full" :value="old('timezone', $event->timezone)" required />
                        </div>
                        
                        <div class="border-t pt-4">
                            <h3 class="font-medium text-lg mb-4">Location Details</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="venue_name" :value="__('Venue Name (For Offline/Hybrid)')" />
                                    <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full" :value="old('venue_name', $event->venue_name)" />
                                </div>
                                
                                <div>
                                    <x-input-label for="venue_address" :value="__('Venue Address')" />
                                    <textarea id="venue_address" name="venue_address" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('venue_address', $event->venue_address) }}</textarea>
                                </div>
                                
                                <div>
                                    <x-input-label for="online_url" :value="__('Online URL / Stream Link (For Online/Hybrid)')" />
                                    <x-text-input id="online_url" name="online_url" type="url" class="mt-1 block w-full" :value="old('online_url', $event->online_url)" />
                                </div>
                                
                                <div>
                                    <x-input-label for="capacity" :value="__('Total Event Capacity (Optional)')" />
                                    <x-text-input id="capacity" name="capacity" type="number" min="1" class="mt-1 block w-full" :value="old('capacity', $event->capacity)" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t">
                            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>