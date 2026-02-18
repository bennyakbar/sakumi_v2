<x-master-page :title="__('Student Detail')">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-gray-500">{{ __('app.label.name') }}</div>
            <div class="font-medium">{{ $student->name }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('app.label.nis_nisn') }}</div>
            <div class="font-medium">{{ $student->nis }} / {{ $student->nisn ?? '-' }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('app.label.class') }}</div>
            <div class="font-medium">{{ $student->schoolClass?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('app.label.category') }}</div>
            <div class="font-medium">{{ $student->category?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('app.label.status') }}</div>
            <div class="font-medium">{{ $student->status }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('app.label.enrollment_date') }}</div>
            <div class="font-medium">{{ optional($student->enrollment_date)->format('Y-m-d') }}</div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('master.students.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
            {{ __('app.button.back') }}
        </a>
    </div>
</x-master-page>
