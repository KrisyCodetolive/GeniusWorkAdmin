@props(['title', 'icon' => null, 'color' => 'blue'])

<div class="form-section mb-6 bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 transition-all duration-200 hover:shadow-md">
    <div class="bg-gradient-to-r from-{{ $color }}-500 to-{{ $color }}-600 p-4 flex items-center">
        @if($icon)
            <span class="text-white mr-2">{!! $icon !!}</span>
        @endif
        <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
    </div>
    
    <div class="p-6 bg-white">
        {{ $slot }}
    </div>
</div>
