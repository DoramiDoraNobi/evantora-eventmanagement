<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('events.edit', $event->id) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tickets: ') }} {{ $event->title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Create Ticket Form -->
            <div class="md:col-span-1">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-lg mb-4">Create Ticket</h3>

                    {{-- Success Message --}}
                    @if(session('status'))
                        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-700">{{ session('status') }}</p>
                        </div>
                    @endif

                    {{-- Validation Errors --}}
                    @if($errors->any())
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="post" action="{{ route('events.tickets.store', $event->id) }}" class="space-y-4">
                        @csrf
                        
                        <div>
                            <x-input-label for="name" :value="__('Ticket Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                        </div>
                        
                        <div>
                            <x-input-label for="type" :value="__('Type')" />
                            <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="document.getElementById('price-wrapper').style.display = this.value === 'free' ? 'none' : 'block'">
                                <option value="paid">Paid</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        
                        <div id="price-wrapper">
                            <x-input-label for="price" :value="__('Price')" />
                            <x-text-input id="price" name="price" type="number" step="0.01" min="0" value="0" class="mt-1 block w-full" required />
                        </div>
                        
                        <div>
                            <x-input-label for="quantity" :value="__('Quantity (Leave empty for unlimited)')" />
                            <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" />
                        </div>
                        
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                <span class="ms-2 text-sm text-gray-600">Active (Visible to public)</span>
                            </label>
                        </div>
                        
                        <x-primary-button class="w-full justify-center">{{ __('Create Ticket') }}</x-primary-button>
                    </form>
                </div>
            </div>
            
            <!-- Tickets List -->
            <div class="md:col-span-2">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="font-medium text-lg mb-4">Existing Tickets</h3>
                    
                    @if($tickets->isEmpty())
                        <p class="text-gray-500 py-4 text-center">No tickets created yet.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($tickets as $ticket)
                            <div class="border rounded-lg p-4 flex justify-between items-center {{ !$ticket->is_active ? 'bg-gray-50 opacity-75' : '' }}">
                                <div>
                                    <div class="font-semibold text-lg">{{ $ticket->name }}</div>
                                    <div class="text-sm text-gray-500 space-x-2">
                                        <span class="font-medium {{ $ticket->type === 'free' ? 'text-green-600' : 'text-indigo-600' }}">
                                            {{ $ticket->type === 'free' ? 'FREE' : $event->organization->currency . ' ' . number_format($ticket->price, 2) }}
                                        </span>
                                        <span>
                                            @if($ticket->quantity)
                                                {{ $ticket->quantity - $ticket->quantity_sold }} left of {{ $ticket->quantity }} ({{ $ticket->quantity_sold }} sold)
                                            @else
                                                Unlimited ({{ $ticket->quantity_sold }} sold)
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs px-2 py-1 rounded {{ $ticket->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $ticket->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <form method="post" action="{{ route('events.tickets.destroy', [$event->id, $ticket->id]) }}" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">Delete</button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>