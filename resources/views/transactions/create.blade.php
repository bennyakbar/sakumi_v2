<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Transaction') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('transactions.store') }}" id="transactionForm">
                        @csrf

                        @if($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Date -->
                            <div>
                                <x-input-label for="transaction_date" :value="__('Transaction Date')" />
                                <x-text-input id="transaction_date" class="block mt-1 w-full" type="date"
                                    name="transaction_date" :value="old('transaction_date', date('Y-m-d'))" required />
                            </div>

                            <!-- Student -->
                            <div>
                                <x-input-label for="student_id" :value="__('Student')" />
                                <select id="student_id" name="student_id"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    required>
                                    <option value="">-- Select Student --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->name }} ({{ $student->schoolClass->name ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <x-input-label for="payment_method" :value="__('Payment Method')" />
                                <select id="payment_method" name="payment_method"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    required>
                                    <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>
                                        Transfer</option>
                                    <option value="qris" {{ old('payment_method') === 'qris' ? 'selected' : '' }}>QRIS
                                    </option>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description')" />
                                <textarea id="description" name="description"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                                    rows="2">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Transaction Items') }}</h3>

                            <div id="items-container">
                                <!-- Items will be added here via JS -->
                            </div>

                            <button type="button" onclick="addItem()"
                                class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('+ Add Item') }}
                            </button>
                        </div>

                        <div class="flex justify-end mt-6 border-t border-gray-200 pt-4">
                            <div class="mr-4 flex items-center">
                                <span class="text-lg font-bold mr-2">Total:</span>
                                <span class="text-xl font-bold text-indigo-600" id="grand-total">Rp 0</span>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('transactions.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Process Payment') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Template for Fee Type Options (loaded dynamically ideally, but hardcoding for now or using a json object) -->
    <script>
        // Simple client-side script to handle adding rows
        // Ideally we would fetch fee types from an API
        const feeTypes = @json($feeTypes);
        let itemCount = 0;

        function addItem() {
            const container = document.getElementById('items-container');
            const rowId = itemCount++;

            let optionsHtml = '<option value="">-- Select Fee Type --</option>';
            feeTypes.forEach(ft => {
                optionsHtml += `<option value="${ft.id}">${ft.name}</option>`;
            });

            const html = `
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 p-4 bg-gray-50 rounded-lg item-row" id="row-${rowId}">
                    <div class="md:col-span-1">
                        <label class="block font-medium text-sm text-gray-700">Fee Type</label>
                        <select name="items[${rowId}][fee_type_id]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full" required>
                            ${optionsHtml}
                        </select>
                    </div>
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Amount</label>
                        <input type="number" name="items[${rowId}][amount]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full amount-input" required min="0" oninput="calculateTotal()">
                    </div>
                     <div>
                        <label class="block font-medium text-sm text-gray-700">Notes</label>
                        <input type="text" name="items[${rowId}][description]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="removeRow(${rowId})" class="text-red-600 hover:text-red-900 text-sm font-semibold">Remove</button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', html);
        }

        function removeRow(id) {
            const row = document.getElementById(`row-${id}`);
            row.remove();
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.amount-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('grand-total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        }

        // Add first row by default
        addItem();
    </script>
</x-app-layout>
