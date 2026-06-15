<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $organization->name }} - Eventora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('public.home') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                            E
                        </div>
                        <span class="font-bold text-xl tracking-tight text-gray-900">Eventora</span>
                    </a>
                    
                    <div class="hidden sm:flex sm:ml-8 space-x-8">
                        <a href="{{ route('public.home') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700">
                            Browse Events
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('buyer.my-tickets') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">My Tickets</a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                                {{ Auth::user()->name }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden" :class="{'hidden': !open}">
                                <form method="POST" action="{{ route('buyer.logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('buyer.login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600">Log in</a>
                        <a href="{{ route('buyer.register') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Sign up
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Organization Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex items-center gap-6">
                    @if($organization->logo)
                        <img src="{{ asset('storage/' . $organization->logo) }}" alt="{{ $organization->name }}" class="w-24 h-24 rounded-2xl object-cover shadow-sm border border-gray-100">
                    @else
                        <div class="w-24 h-24 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-sm" style="background-color: {{ $organization->primary_color }}">
                            {{ substr($organization->name, 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">
                            {{ $organization->name }}
                        </h1>
                        <div class="mt-2 text-sm text-gray-500">
                            Organizing events since {{ $organization->created_at->format('Y') }}
                        </div>
                    </div>
                </div>
            </div>
            
            @if($organization->description)
            <div class="mt-8 max-w-3xl prose prose-indigo text-gray-600">
                {!! strip_tags($organization->description) !!}
            </div>
            @endif
        </div>
    </div>

    <!-- Events Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-8 border-b border-gray-200 pb-4">
            <h2 class="text-2xl font-bold text-gray-900">Events by {{ $organization->name }}</h2>
        </div>

        @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($events as $event)
                    <a href="{{ route('public.event.show', ['organizationSlug' => $organization->slug, 'eventSlug' => $event->slug]) }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="aspect-w-16 aspect-h-9 bg-gray-200 relative">
                            @if($event->hero_image)
                                <img src="{{ asset('storage/' . $event->hero_image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                                    <svg class="w-12 h-12 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            <div class="absolute top-4 left-4">
                                <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-sm font-semibold rounded-full shadow-sm text-gray-900">
                                    {{ date('M j', strtotime($event->start_date)) }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $event->title }}</h3>
                            
                            <div class="space-y-2 mt-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ date('D, M j, Y \a\t g:i A', strtotime($event->start_date)) }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="truncate">
                                        @if($event->type === 'online')
                                            Online Event
                                        @else
                                            {{ $event->venue_name ?? 'Location TBA' }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            
            <div class="mt-12">
                {{ $events->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900">No events found</h3>
                <p class="mt-1 text-sm text-gray-500">This organization currently doesn't have any published events.</p>
            </div>
        @endif
    </div>

</body>
</html>
