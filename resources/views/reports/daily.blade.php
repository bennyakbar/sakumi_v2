<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daily Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="GET" action="{{ route('reports.daily') }}" class="mb-6 flex gap-4 items-end">
                        <div>
                            <x-input-label for="date" :value="__('Select Date')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="$date"
                                required />
                        </div>
                        <x-primary-button>
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('reports.daily') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 ml-2 text-sm font-semibold uppercase">Reset</a>
                    </form>

                    <div class="flex justify-between items-center mb-4 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <span class="text-gray-600">Report Date:</span>
                            <span
                                class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Income:</span>
                            <span class="font-bold text-xl text-green-600">Rp
                                {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Time</th>
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
                                        Items</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->created_at->format('H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <a href="{{ route('transactions.show', $transaction) }}"
                                                class="text-indigo-600 hover:text-indigo-900 hover:underline">{{ $transaction->code }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->student->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $transaction->student->schoolClass->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <ul class="list-disc list-inside">
                                                @foreach($transaction->items as $item)
                                                    <li>{{ $item->feeType->name }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No
                                            transactions found for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-3 text-right text-xs font-bold text-gray-900 uppercase tracking-wider">
                                        Total</td>
                                    <td
                                        class="px-6 py-3 text-right text-xs font-bold text-gray-900 uppercase tracking-wider">
                                        Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>