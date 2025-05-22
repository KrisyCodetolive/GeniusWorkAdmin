@props(['icon', 'color', 'label', 'value', 'subvalue' => null])

<div class="bg-white p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="flex items-center">
        <div class="p-3 rounded-full bg-{{ $color }}-100 text-{{ $color }}-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
            <p class="text-xl font-bold">{{ $value }}</p>
            @if($subvalue)
                <p class="text-xs text-gray-500">{{ $subvalue }}</p>
            @endif
        </div>
    </div>
</div>
