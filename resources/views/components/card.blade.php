@props([
    'title' => null,
    'icon' => null,
    'iconColor' => 'indigo',
    'footer' => null,
    'padding' => true,
    'hover' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden ' . ($hover ? 'card-hover' : '')]) }}>
    @if($title || $icon)
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="font-medium text-gray-700">{{ $title }}</h3>
            @if($icon)
                <div class="bg-{{ $iconColor }}-100 p-2 rounded-lg">
                    <i data-lucide="{{ $icon }}" class="h-5 w-5 text-{{ $iconColor }}-600"></i>
                </div>
            @endif
        </div>
    @endif
    
    <div class="{{ $padding ? 'p-4' : '' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $footer }}
        </div>
    @endif
</div>
