<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transaction Detail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6 border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $transaction->code }}</h3>
                            <p class="text-sm text-gray-500">{{ $transaction->transaction_date->format('d F Y') }}</p>
                        </div>
                        <div class="text-right">
                            @if($transaction->status == 'completed')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            @elseif($transaction->status == 'cancelled')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Student Info
                            </h4>
                            <p class="text-gray-900 font-medium">{{ $transaction->student->name ?? '-' }}</p>
                            <p class="text-gray-600 text-sm">NIS: {{ $transaction->student->nis ?? '-' }}</p>
                            <p class="text-gray-600 text-sm">Class:
                                {{ $transaction->student->schoolClass->name ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Payment Info
                            </h4>
                            <p class="text-gray-600 text-sm">Payment Method: {{ ucfirst($transaction->payment_method) }}
                            </p>
                            <p class="text-gray-600 text-sm">Created By: {{ $transaction->creator->name ?? 'System' }}</p>
                            @if($transaction->notes)
                                <p class="text-gray-600 text-sm mt-2">Notes: {{ $transaction->notes }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Description / Fee Type</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($transaction->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->feeType->name }}
                                            @if($item->description)
                                                <span class="text-gray-500 text-xs">- {{ $item->description }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp
                                            {{ number_format($item->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                        Total</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                        Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('transactions.index') }}"
                            class="px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                            Back
                        </a>
                        @can('receipts.print')
                            <a href="{{ route('receipts.print', $transaction) }}" target="_blank"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Print Receipt
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
