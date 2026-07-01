<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Coupons & Promos') }}
            </h2>
            <a href="{{ route('coupons.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Create Coupon
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($coupons->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500 mb-4">You haven't created any coupons yet.</p>
                            <a href="{{ route('coupons.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first coupon</a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usage</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($coupons as $coupon)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $coupon->code }}</div>
                                            <div class="text-xs text-gray-500">Min Order: {{ $coupon->min_order_amount ? number_format($coupon->min_order_amount, 2) : 'None' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium">
                                            @if($coupon->type === 'percentage')
                                                <span class="text-green-600">{{ number_format($coupon->value, 0) }}% OFF</span>
                                            @else
                                                <span class="text-blue-600">{{ number_format($coupon->value, 2) }} OFF</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $coupon->event ? $coupon->event->title : 'All Events' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $coupon->is_active ? 'green' : 'red' }}-100 text-{{ $coupon->is_active ? 'green' : 'red' }}-800">
                                                {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            @if($coupon->expires_at && $coupon->expires_at < now())
                                                <div class="text-xs text-red-500 mt-1">Expired</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium space-x-2">
                                            <a href="{{ route('coupons.edit', $coupon->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                {{ $coupons->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
