<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transactions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-semibold leading-tight text-gray-800">
                            {{ __('Transaction History') }}
                        </h2>
                        @can('transactions.create')
                            <a href="{{ route('transactions.create') }}"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('New Transaction') }}
                            </a>
                        @endcan
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Code</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Class</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Amount</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $transaction->code }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->student->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->student->schoolClass->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Rp
                                            {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($transaction->status == 'completed')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                            @elseif($transaction->status == 'cancelled')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst($transaction->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('transactions.show', $transaction) }}"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Detail</a>
                                            @can('receipts.print')
                                                <a href="{{ route('receipts.print', $transaction) }}" target="_blank"
                                                    class="text-gray-600 hover:text-gray-900 mr-3">Receipt</a>
                                            @endcan
                                            @can('transactions.cancel')
                                                @if($transaction->status != 'cancelled')
                                                    <button type="button" class="text-red-600 hover:text-red-900"
                                                        onclick="openCancellationModal('{{ route('transactions.destroy', $transaction) }}', '{{ $transaction->code }}')">
                                                        Cancel
                                                    </button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No
                                            transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="cancellation-modal" class="fixed inset-0 bg-black/40 hidden z-50 items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-lg shadow-xl">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Cancel Transaction</h3>
                <p id="cancellation-modal-subtitle" class="text-sm text-gray-500 mt-1"></p>
            </div>

            <form id="cancellation-form" method="POST" class="p-6">
                @csrf
                @method('DELETE')
                <div>
                    <x-input-label for="cancellation_reason" :value="__('Cancellation Reason')" />
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="4" required
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        placeholder="Enter reason for cancellation"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-semibold uppercase"
                        onclick="closeCancellationModal()">
                        Close
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-semibold uppercase">
                        Confirm Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const cancellationModal = document.getElementById('cancellation-modal');
        const cancellationForm = document.getElementById('cancellation-form');
        const cancellationSubtitle = document.getElementById('cancellation-modal-subtitle');
        const cancellationReasonInput = document.getElementById('cancellation_reason');

        function openCancellationModal(actionUrl, transactionCode) {
            cancellationForm.action = actionUrl;
            cancellationSubtitle.textContent = `Transaction: ${transactionCode}`;
            cancellationReasonInput.value = '';
            cancellationModal.classList.remove('hidden');
            cancellationModal.classList.add('flex');
            cancellationReasonInput.focus();
        }

        function closeCancellationModal() {
            cancellationModal.classList.add('hidden');
            cancellationModal.classList.remove('flex');
        }

        cancellationModal.addEventListener('click', function (event) {
            if (event.target === cancellationModal) {
                closeCancellationModal();
            }
        });
    </script>
</x-app-layout>
