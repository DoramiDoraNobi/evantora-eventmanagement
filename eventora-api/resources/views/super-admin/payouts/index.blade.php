<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payout Requests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bank Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($payouts as $payout)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $payout->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $payout->organization->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                                        Rp {{ number_format($payout->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div><strong>Bank:</strong> {{ $payout->bank_name }}</div>
                                        <div><strong>Account:</strong> {{ $payout->bank_account_number }}</div>
                                        <div><strong>Name:</strong> {{ $payout->bank_account_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($payout->status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($payout->status == 'processing')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Processing</span>
                                        @elseif($payout->status == 'paid')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($payout->status != 'paid' && $payout->status != 'rejected')
                                        <button onclick="openModal('{{ $payout->id }}')" class="text-indigo-600 hover:text-indigo-900">Process</button>
                                        @else
                                            @if($payout->transaction_id)
                                                <span class="text-xs text-gray-500">Trx: {{ $payout->transaction_id }}</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>

                                <!-- Process Modal -->
                                <div id="modal-{{ $payout->id }}" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal('{{ $payout->id }}')"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                            <form action="{{ route('super-admin.payouts.update', $payout) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                        Process Payout: {{ $payout->organization->name }}
                                                    </h3>
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                                        <select name="status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                                            <option value="processing" {{ $payout->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                                            <option value="paid" {{ $payout->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                                            <option value="rejected" {{ $payout->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                        </select>
                                                    </div>
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700">Transaction ID / Transfer Ref (Required if Paid Manually)</label>
                                                        <input type="text" name="transaction_id" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="Leave empty if using Auto PayPal">
                                                    </div>
                                                    @if($payout->organization->paypal_email)
                                                    <div class="mt-4 flex items-start">
                                                        <div class="flex items-center h-5">
                                                            <input id="auto_paypal_{{ $payout->id }}" name="auto_paypal" value="1" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                        </div>
                                                        <div class="ml-3 text-sm">
                                                            <label for="auto_paypal_{{ $payout->id }}" class="font-medium text-gray-700">Auto Payout via PayPal API</label>
                                                            <p class="text-gray-500">Send money automatically to <strong>{{ $payout->organization->paypal_email }}</strong></p>
                                                        </div>
                                                    </div>
                                                    @else
                                                    <div class="mt-4 text-sm text-yellow-600 bg-yellow-50 p-2 rounded">
                                                        * Organization has not setup a PayPal Email. Manual bank transfer is required.
                                                    </div>
                                                    @endif
                                                    <div class="mt-4">
                                                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                                                        <textarea name="notes" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                                    </div>
                                                </div>
                                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                        Save
                                                    </button>
                                                    <button type="button" onclick="closeModal('{{ $payout->id }}')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No payout requests found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $payouts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById('modal-' + id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById('modal-' + id).classList.add('hidden');
        }
    </script>
</x-app-layout>
