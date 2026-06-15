<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Super Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">System Overview</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-indigo-50 rounded-lg p-6 border border-indigo-100">
                            <dt class="text-sm font-medium text-indigo-500 truncate">Total Tenants</dt>
                            <dd class="mt-1 text-3xl font-semibold text-indigo-900">{{ number_format($stats['total_tenants']) }}</dd>
                        </div>

                        <div class="bg-blue-50 rounded-lg p-6 border border-blue-100">
                            <dt class="text-sm font-medium text-blue-500 truncate">Total Users</dt>
                            <dd class="mt-1 text-3xl font-semibold text-blue-900">{{ number_format($stats['total_users']) }}</dd>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-6 border border-purple-100">
                            <dt class="text-sm font-medium text-purple-500 truncate">Total Events</dt>
                            <dd class="mt-1 text-3xl font-semibold text-purple-900">{{ number_format($stats['total_events']) }}</dd>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="{{ route('super-admin.tenants.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Manage Tenants
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
