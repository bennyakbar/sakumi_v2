<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monthly Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="GET" action="{{ route('reports.monthly') }}"
                        class="mb-6 flex gap-4 items-end flex-wrap">
                        <div>
                            <x-input-label for="month" :value="__('Month')" />
                            <select id="month" name="month"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $month == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <x-input-label for="year" :value="__('Year')" />
                            <select id="year" name="year"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <x-primary-button>
                            {{ __('Filter') }}
                        </x-primary-button>
                    </form>

                    <div class="flex justify-between items-center mb-4 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <span class="text-gray-600">Period:</span>
                            <span
                                class="font-bold text-gray-900">{{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                {{ $year }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Income:</span>
                            <span class="font-bold text-xl text-green-600">Rp
                                {{ number_format($totalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Daily Summary Table -->
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Summary</h3>
                            <div class="bg-white border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date</th>
                                            <th scope="col"
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($dailySummary as $date => $total)
                                            <tr>
                                                <td class="px-6 py-3 text-sm text-gray-900">
                                                    {{ \Carbon\Carbon::parse($date)->format('d M') }}</td>
                                                <td class="px-6 py-3 text-sm text-gray-900 text-right">Rp
                                                    {{ number_format($total, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="px-6 py-3 text-sm text-gray-500 text-center">No
                                                    transactions.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Transaction List (Simplified) -->
                        <div class="lg:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Latest Transactions</h3>
                            <div class="bg-white border rounded-lg overflow-hidden">
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
                                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($transactions as $transaction)
                                            <tr>
                                                <td class="px-6 py-3 text-sm text-gray-500">
                                                    {{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                                <td class="px-6 py-3 text-sm font-medium text-indigo-600 hover:underline">
                                                    <a
                                                        href="{{ route('transactions.show', $transaction) }}">{{ $transaction->code }}</a>
                                                </td>
                                                <td class="px-6 py-3 text-sm text-gray-900">
                                                    {{ $transaction->student->name }}</td>
                                                <td class="px-6 py-3 text-sm text-gray-900 text-right">Rp
                                                    {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-3 text-sm text-gray-500 text-center">No
                                                    transactions found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>