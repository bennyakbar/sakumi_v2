<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invoice Detail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Header --}}
                    <div class="flex justify-between items-start mb-6 border-b pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $invoice->invoice_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $invoice->invoice_date->format('d F Y') }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                <span class="uppercase">{{ $invoice->period_type }}</span> &mdash; {{ $invoice->period_identifier }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($invoice->status === 'unpaid')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Unpaid</span>
                            @elseif($invoice->status === 'partially_paid')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Partially Paid</span>
                            @elseif($invoice->status === 'paid')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    {{-- Student & Invoice Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Student Info</h4>
                            <p class="text-gray-900 font-medium">{{ $invoice->student->name ?? '-' }}</p>
                            <p class="text-gray-600 text-sm">NIS: {{ $invoice->student->nis ?? '-' }}</p>
                            <p class="text-gray-600 text-sm">Class: {{ $invoice->student->schoolClass->name ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Invoice Info</h4>
                            <p class="text-gray-600 text-sm">Due Date: {{ $invoice->due_date->format('d F Y') }}</p>
                            <p class="text-gray-600 text-sm">Created By: {{ $invoice->creator->name ?? 'System' }}</p>
                            @if($invoice->notes)
                                <p class="text-gray-600 text-sm mt-2">Notes: {{ $invoice->notes }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Line Items --}}
                    <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Line Items</h4>
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Obligation Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($invoice->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->feeType->name ?? $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($item->month && $item->year)
                                                {{ sprintf('%02d/%d', $item->month, $item->year) }}
                                            @elseif($item->year)
                                                {{ $item->year }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($item->studentObligation && $item->studentObligation->is_paid)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Paid</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50">
                                    <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900 text-right">Total</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="3" class="px-6 py-4 text-sm font-bold text-green-700 text-right">Paid</td>
                                    <td class="px-6 py-4 text-sm font-bold text-green-700 text-right">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td colspan="3" class="px-6 py-4 text-sm font-bold text-red-700 text-right">Outstanding</td>
                                    <td class="px-6 py-4 text-sm font-bold text-red-700 text-right">Rp {{ number_format($invoice->outstanding, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Settlement Allocations --}}
                    @if($invoice->allocations->isNotEmpty())
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Settlement History</h4>
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Settlement #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($invoice->allocations as $allocation)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <a href="{{ route('settlements.show', $allocation->settlement) }}" class="text-indigo-600 hover:text-indigo-900">{{ $allocation->settlement->settlement_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $allocation->settlement->payment_date->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($allocation->settlement->payment_method) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($allocation->settlement->status === 'completed')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Rp {{ number_format($allocation->amount, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('invoices.index') }}"
                            class="px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                            Back
                        </a>
                        @can('invoices.print')
                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                Print Invoice
                            </a>
                        @endcan
                        @can('settlements.create')
                            @if(in_array($invoice->status, ['unpaid', 'partially_paid']))
                                <a href="{{ route('settlements.create', ['student_id' => $invoice->student_id]) }}"
                                    class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Create Settlement
                                </a>
                            @endif
                        @endcan
                        @can('invoices.cancel')
                            @if(in_array($invoice->status, ['unpaid']))
                                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Are you sure you want to cancel this invoice?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        Cancel Invoice
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
