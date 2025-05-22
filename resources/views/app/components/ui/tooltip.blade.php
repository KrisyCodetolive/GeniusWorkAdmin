@props(['text', 'position' => 'top'])

@php
    $positions = [
        'top' => 'bottom-full left-1/2 transform -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 transform -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 transform -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 transform -translate-y-1/2 ml-2',
    ];
    
    $arrows = [
        'top' => 'top-full left-1/2 transform -translate-x-1/2 border-t-gray-700',
        'bottom' => 'bottom-full left-1/2 transform -translate-x-1/2 border-b-gray-700',
        'left' => 'left-full top-1/2 transform -translate-y-1/2 border-l-gray-700',
        'right' => 'right-full top-1/2 transform -translate-y-1/2 border-r-gray-700',
    ];
    
    $positionClass = $positions[$position] ?? $positions['top'];
    $arrowClass = $arrows[$position] ?? $arrows['top'];
@endphp

<div x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false" class="relative inline-block">
    <div {{ $attributes }}>
        {{ $slot }}
    </div>
    
    <div 
        x-show="show" 
        x-transition:enter="transition ease-out duration-200" 
        x-transition:enter-start="opacity-0 scale-95" 
        x-transition:enter-end="opacity-100 scale-100" 
        x-transition:leave="transition ease-in duration-150" 
        x-transition:leave-start="opacity-100 scale-100" 
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 {{ $positionClass }} px-2 py-1 text-xs font-medium text-white bg-gray-700 rounded-md shadow-sm whitespace-nowrap"
        style="display: none;"
    >
        {{ $text }}
        
        @if($position === 'top')
            <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 rotate-45 bg-gray-700"></div>
        @elseif($position === 'bottom')
            <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 rotate-45 bg-gray-700"></div>
        @elseif($position === 'left')
            <div class="absolute -right-1 top-1/2 transform -translate-y-1/2 w-2 h-2 rotate-45 bg-gray-700"></div>
        @elseif($position === 'right')
            <div class="absolute -left-1 top-1/2 transform -translate-y-1/2 w-2 h-2 rotate-45 bg-gray-700"></div>
        @endif
    </div>
</div>
