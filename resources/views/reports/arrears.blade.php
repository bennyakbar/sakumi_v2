<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Arrears Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="GET" action="{{ route('reports.arrears') }}"
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
                        <div>
                            <x-input-label for="class_id" :value="__('Class (Optional)')" />
                            <select id="class_id" name="class_id"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                                <option value="">-- All Classes --</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($consolidated ?? false)
                            <input type="hidden" name="scope" value="all">
                        @endif
                        <x-primary-button>
                            {{ __('Filter') }}
                        </x-primary-button>

                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="{{ route('reports.arrears', array_merge(request()->except('scope'), ['scope' => ($scope ?? 'unit') === 'all' ? 'unit' : 'all'])) }}"
                                class="px-4 py-2 rounded-md text-sm font-semibold uppercase {{ ($scope ?? 'unit') === 'all' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ ($scope ?? 'unit') === 'all' ? 'Current Unit' : 'All Units' }}
                            </a>
                        @endif
                    </form>

                    <div class="flex justify-between items-center mb-4 bg-red-50 p-4 rounded-lg border border-red-100">
                        <div>
                            <span class="text-red-800">Unpaid Obligations for:</span>
                            <span
                                class="font-bold text-red-900">{{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                {{ $year }}</span>
                            @if($consolidated ?? false)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">All Units</span>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if($consolidated ?? false)
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit</th>
                                    @endif
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Class</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fee Type</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount Due</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($arrears as $arrear)
                                    <tr>
                                        @if($consolidated ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $arrear->unit->code ?? '-' }}</td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $arrear->student->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $arrear->student->schoolClass->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $arrear->feeType->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600 text-right">
                                            Rp {{ number_format($arrear->amount, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="{{ route('transactions.create', ['student_id' => $arrear->student_id]) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Pay Now</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ ($consolidated ?? false) ? 6 : 5 }}"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No arrears
                                            found for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $arrears->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
