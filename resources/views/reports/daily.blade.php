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

                    <form method="GET" action="{{ route('reports.daily') }}" class="mb-6 flex gap-4 items-end flex-wrap">
                        <div>
                            <x-input-label for="date" :value="__('Select Date')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="$date"
                                required />
                        </div>
                        @if($consolidated ?? false)
                            <input type="hidden" name="scope" value="all">
                        @endif
                        <x-primary-button>
                            {{ __('Filter') }}
                        </x-primary-button>
                        <a href="{{ route('reports.daily') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 ml-2 text-sm font-semibold uppercase">Reset</a>

                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="{{ route('reports.daily', array_merge(request()->except('scope'), ['scope' => ($scope ?? 'unit') === 'all' ? 'unit' : 'all'])) }}"
                                class="px-4 py-2 rounded-md text-sm font-semibold uppercase {{ ($scope ?? 'unit') === 'all' ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                {{ ($scope ?? 'unit') === 'all' ? 'Current Unit' : 'All Units' }}
                            </a>
                        @endif
                    </form>

                    <div class="flex justify-between items-center mb-4 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <span class="text-gray-600">Report Date:</span>
                            <span
                                class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($date)->format('d F Y') }}</span>
                            @if($consolidated ?? false)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">All Units</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-600">Total Payments:</span>
                            <span class="font-bold text-xl text-green-600">Rp
                                {{ number_format($totalAmount, 0, ',', '.') }}</span>
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
                                        Time</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Source</th>
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
                                @forelse ($entries as $entry)
                                    <tr class="{{ ($entry['type'] ?? 'income') === 'expense' ? 'bg-red-50' : '' }}">
                                        @if($consolidated ?? false)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $entry['unit_code'] ?? '-' }}</td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry['time'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ ($entry['model_type'] ?? 'settlement') === 'settlement' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $entry['source'] ?? 'Settlement' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            @if(($entry['model_type'] ?? 'settlement') === 'settlement')
                                                @can('settlements.view')
                                                    <a href="{{ route('settlements.show', $entry['model']) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 hover:underline">{{ $entry['code'] }}</a>
                                                @else
                                                    {{ $entry['code'] }}
                                                @endcan
                                            @else
                                                @can('transactions.view')
                                                    <a href="{{ route('transactions.show', $entry['model']) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 hover:underline">{{ $entry['code'] }}</a>
                                                @else
                                                    {{ $entry['code'] }}
                                                @endcan
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry['student'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $entry['class'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <ul class="list-disc list-inside">
                                                @foreach($entry['items'] as $item)
                                                    <li>{{ $item }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right {{ $entry['amount'] < 0 ? 'text-red-600' : 'text-gray-900' }}">
                                            Rp {{ number_format($entry['amount'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ ($consolidated ?? false) ? 8 : 7 }}"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No
                                            entries found for this date.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="{{ ($consolidated ?? false) ? 7 : 6 }}"
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
