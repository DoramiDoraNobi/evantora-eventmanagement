<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#3E2723] leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-[#9C6644]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ __('Finance & Payouts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Top Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Earnings -->
                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-[#E6DCCF] transition duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Gross Earnings</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $organization->currency }} {{ number_format($organization->total_earnings, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Withdrawn Amount -->
                <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-[#E6DCCF] transition duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 truncate">Total Withdrawn</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $organization->currency }} {{ number_format($organization->withdrawn_amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Balance -->
                <div class="bg-gradient-to-br from-[#9C6644] to-[#7F5539] overflow-hidden shadow-sm rounded-2xl transition duration-300 hover:shadow-md">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-white/20 text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-white/80 truncate">Available Balance</p>
                                <p class="text-2xl font-bold text-white">{{ $organization->currency }} {{ number_format($organization->available_balance, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Request Payout Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-[#E6DCCF]">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                            <h3 class="text-lg font-bold text-gray-900">Request Payout</h3>
                            <p class="text-sm text-gray-500 mt-1">Withdraw funds to your bank account.</p>
                        </div>
                        <div class="p-6">
                            @if($organization->available_balance >= 50000)
                                <form action="{{ route('finance.store') }}" method="POST">
                                    @csrf
                                    <div class="space-y-4">
                                        <div>
                                            <x-input-label for="amount" :value="__('Amount to Withdraw')" />
                                            <div class="relative mt-1 rounded-md shadow-sm">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">{{ $organization->currency }}</span>
                                                </div>
                                                <x-text-input id="amount" type="number" name="amount" min="50000" max="{{ $organization->available_balance }}" step="1" class="block w-full pl-12" placeholder="0.00" required />
                                            </div>
                                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                            <p class="text-xs text-gray-500 mt-1">Min. {{ $organization->currency }} 50,000</p>
                                        </div>

                                        <div>
                                            <x-input-label for="bank_name" :value="__('Bank Name')" />
                                            <x-text-input id="bank_name" type="text" name="bank_name" class="block mt-1 w-full" placeholder="e.g. BCA, Mandiri, PayPal" required />
                                            <x-input-error :messages="$errors->get('bank_name')" class="mt-2" />
                                        </div>

                                        <div>
                                            <x-input-label for="bank_account_number" :value="__('Account Number')" />
                                            <x-text-input id="bank_account_number" type="text" name="bank_account_number" class="block mt-1 w-full" placeholder="e.g. 1234567890" required />
                                            <x-input-error :messages="$errors->get('bank_account_number')" class="mt-2" />
                                        </div>

                                        <div>
                                            <x-input-label for="bank_account_name" :value="__('Account Holder Name')" />
                                            <x-text-input id="bank_account_name" type="text" name="bank_account_name" class="block mt-1 w-full" placeholder="e.g. John Doe" required />
                                            <x-input-error :messages="$errors->get('bank_account_name')" class="mt-2" />
                                        </div>

                                        <div class="pt-4">
                                            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-[#9C6644] hover:bg-[#7F5539] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9C6644] transition-colors">
                                                Submit Request
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="text-center py-6">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <p class="text-sm text-gray-500">Insufficient balance to request a payout.</p>
                                    <p class="text-xs text-gray-400 mt-1">Minimum requirement: {{ $organization->currency }} 50,000</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payout History -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-[#E6DCCF]">
                        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Payout History</h3>
                                <p class="text-sm text-gray-500 mt-1">Track your previous withdrawal requests.</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($payouts as $payout)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $payout->created_at->format('M d, Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $organization->currency }} {{ number_format($payout->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <div class="font-medium text-gray-900">{{ $payout->bank_name }}</div>
                                                <div class="text-xs">{{ $payout->bank_account_number }}</div>
                                                <div class="text-xs">{{ $payout->bank_account_name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($payout->status == 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                @elseif($payout->status == 'processing')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Processing
                                                    </span>
                                                @elseif($payout->status == 'paid')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Paid
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Failed
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <h3 class="mt-2 text-sm font-medium text-gray-900">No payout requests</h3>
                                                <p class="mt-1 text-sm text-gray-500">You haven't requested any payouts yet.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        @if($payouts->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200">
                                {{ $payouts->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
