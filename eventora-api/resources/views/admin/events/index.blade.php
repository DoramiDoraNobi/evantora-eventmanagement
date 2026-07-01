<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Events') }}
            </h2>
            <a href="{{ route('events.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Create Event
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($events->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500 mb-4">You haven't created any events yet.</p>
                            <a href="{{ route('events.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first event</a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($events as $event)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                            <div class="text-sm text-gray-500">{{ ucfirst($event->type) }}</div>
                                            @if($event->category)
                                                <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 text-xs font-medium rounded-full text-white" style="background-color: {{ $event->category->color ?? '#6b7280' }}">
                                                    {{ $event->category->icon }} {{ $event->category->name }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $event->status === 'published' ? 'green' : 'yellow' }}-100 text-{{ $event->status === 'published' ? 'green' : 'yellow' }}-800">
                                                {{ ucfirst($event->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                                            <a href="{{ route('events.edit', $event->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <a href="{{ route('events.tickets.index', $event->id) }}" class="text-blue-600 hover:text-blue-900">Tickets</a>
                                            <a href="{{ route('events.attendees.index', $event->id) }}" class="text-purple-600 hover:text-purple-900">Attendees</a>
                                            <a href="{{ route('events.scanner', $event->id) }}" class="text-green-600 hover:text-green-900 font-semibold">Scanner</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $events->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>