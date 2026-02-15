<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Settlement') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Step 1: Select Student --}}
                    <form method="GET" action="{{ route('settlements.create') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <x-input-label for="student_select" :value="__('Select Student')" />
                                <select id="student_select" name="student_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    onchange="this.form.submit()">
                                    <option value="">-- Select Student --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ $selectedStudentId == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->schoolClass->name ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    @if($selectedStudentId && $outstandingInvoices->isNotEmpty())
                        {{-- Step 2: Settlement + Allocation --}}
                        <form method="POST" action="{{ route('settlements.store') }}" id="settlementForm">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $selectedStudentId }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-t border-gray-200 pt-4">
                                <div>
                                    <x-input-label for="payment_date" :value="__('Payment Date')" />
                                    <x-text-input id="payment_date" class="block mt-1 w-full" type="date" name="payment_date"
                                        :value="old('payment_date', date('Y-m-d'))" required />
                                </div>
                                <div>
                                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                                    <select id="payment_method" name="payment_method"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                                        <option value="qris" {{ old('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="total_amount" :value="__('Total Payment Amount')" />
                                    <x-text-input id="total_amount" class="block mt-1 w-full" type="number" name="total_amount"
                                        :value="old('total_amount')" required min="1" step="1"
                                        oninput="validateAllocations()" />
                                </div>
                                <div>
                                    <x-input-label for="reference_number" :value="__('Reference Number (Optional)')" />
                                    <x-text-input id="reference_number" class="block mt-1 w-full" type="text" name="reference_number"
                                        :value="old('reference_number')" placeholder="Transfer reference, etc." />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="notes" :value="__('Notes (Optional)')" />
                                    <textarea id="notes" name="notes" rows="2"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            {{-- Allocation Grid --}}
                            <div class="border-t border-gray-200 pt-4 mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Allocate to Invoices') }}</h3>
                                <p class="text-sm text-gray-500 mb-4">Enter the amount to allocate to each outstanding invoice. Total allocation must not exceed the payment amount.</p>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase" style="width:160px">Allocate</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($outstandingInvoices as $idx => $inv)
                                                @php $outstanding = (float)$inv->total_amount - (float)$inv->paid_amount; @endphp
                                                <tr>
                                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                                        {{ $inv->invoice_number }}
                                                        <input type="hidden" name="allocations[{{ $idx }}][invoice_id]" value="{{ $inv->id }}">
                                                    </td>
                                                    <td class="px-4 py-4 text-sm text-gray-500">
                                                        <span class="text-xs uppercase text-gray-400">{{ $inv->period_type }}</span>
                                                        {{ $inv->period_identifier }}
                                                    </td>
                                                    <td class="px-4 py-4 text-sm text-gray-500">{{ $inv->due_date->format('d/m/Y') }}</td>
                                                    <td class="px-4 py-4 text-sm text-gray-900 text-right">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                                    <td class="px-4 py-4 text-sm text-gray-500 text-right">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                                                    <td class="px-4 py-4 text-sm font-medium text-red-600 text-right">Rp {{ number_format($outstanding, 0, ',', '.') }}</td>
                                                    <td class="px-4 py-4 text-right">
                                                        <input type="number" name="allocations[{{ $idx }}][amount]"
                                                            class="allocation-input border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-36 text-right text-sm"
                                                            value="0" min="0" max="{{ $outstanding }}" step="1"
                                                            data-outstanding="{{ $outstanding }}"
                                                            oninput="validateAllocations()">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-gray-50">
                                                <td colspan="6" class="px-4 py-4 text-sm font-bold text-gray-900 text-right">Total Allocated:</td>
                                                <td class="px-4 py-4 text-sm font-bold text-right" id="total-allocated">Rp 0</td>
                                            </tr>
                                            <tr class="bg-gray-50">
                                                <td colspan="6" class="px-4 py-4 text-sm font-bold text-gray-900 text-right">Remaining:</td>
                                                <td class="px-4 py-4 text-sm font-bold text-right" id="remaining-amount">Rp 0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div id="allocation-error" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mt-2 text-sm"></div>
                            </div>

                            <div class="flex justify-end space-x-4">
                                <a href="{{ route('settlements.index') }}"
                                    class="px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                    Cancel
                                </a>
                                <x-primary-button id="submit-btn">{{ __('Create Settlement') }}</x-primary-button>
                            </div>
                        </form>
                    @elseif($selectedStudentId && $outstandingInvoices->isEmpty())
                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-gray-500 text-center py-8">No outstanding invoices found for this student.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function validateAllocations() {
            const totalAmountInput = document.getElementById('total_amount');
            const totalAllocatedDisplay = document.getElementById('total-allocated');
            const remainingDisplay = document.getElementById('remaining-amount');
            const errorDiv = document.getElementById('allocation-error');
            const submitBtn = document.getElementById('submit-btn');
            const allocationInputs = document.querySelectorAll('.allocation-input');

            if (!totalAmountInput) return;

            const totalAmount = parseFloat(totalAmountInput.value) || 0;
            let totalAllocated = 0;
            let hasError = false;
            let errorMsg = '';

            allocationInputs.forEach(input => {
                const amount = parseFloat(input.value) || 0;
                const outstanding = parseFloat(input.dataset.outstanding) || 0;
                totalAllocated += amount;

                if (amount > outstanding) {
                    hasError = true;
                    errorMsg = 'Allocation exceeds outstanding amount for an invoice.';
                    input.classList.add('border-red-500');
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            if (totalAllocated > totalAmount) {
                hasError = true;
                errorMsg = 'Total allocation (Rp ' + new Intl.NumberFormat('id-ID').format(totalAllocated) +
                    ') exceeds payment amount (Rp ' + new Intl.NumberFormat('id-ID').format(totalAmount) + ').';
            }

            if (totalAllocatedDisplay) {
                totalAllocatedDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalAllocated);
                totalAllocatedDisplay.className = 'px-4 py-4 text-sm font-bold text-right ' + (hasError ? 'text-red-600' : 'text-green-600');
            }

            if (remainingDisplay) {
                const remaining = totalAmount - totalAllocated;
                remainingDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(remaining);
                remainingDisplay.className = 'px-4 py-4 text-sm font-bold text-right ' + (remaining < 0 ? 'text-red-600' : 'text-gray-600');
            }

            if (hasError) {
                errorDiv.textContent = errorMsg;
                errorDiv.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
            } else {
                errorDiv.classList.add('hidden');
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        // Initialize
        validateAllocations();
    </script>
</x-app-layout>
