@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null,
])

@php
    $typeClasses = [
        'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        'success' => 'bg-green-50 text-green-800 border-green-200',
        'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
        'error' => 'bg-red-50 text-red-800 border-red-200',
    ];
    
    $iconMap = [
        'info' => $icon ?? 'info',
        'success' => $icon ?? 'check-circle',
        'warning' => $icon ?? 'alert-triangle',
        'error' => $icon ?? 'alert-circle',
    ];
    
    $iconColors = [
        'info' => 'text-blue-500',
        'success' => 'text-green-500',
        'warning' => 'text-yellow-500',
        'error' => 'text-red-500',
    ];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    {{ $attributes->merge(['class' => 'rounded-lg border p-4 flex items-start ' . $typeClasses[$type]]) }}
>
    <div class="flex-shrink-0 mr-3">
        <i data-lucide="{{ $iconMap[$type] }}" class="h-5 w-5 {{ $iconColors[$type] }}"></i>
    </div>
    
    <div class="flex-1 pt-0.5">
        {{ $slot }}
    </div>
    
    @if($dismissible)
        <button 
            @click="show = false" 
            type="button" 
            class="flex-shrink-0 ml-3 -mt-1 -mr-1 p-1 rounded-full hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-{{ substr($type, 0, strpos($type, '-') ?: strlen($type)) }}-50 focus:ring-{{ substr($type, 0, strpos($type, '-') ?: strlen($type)) }}-500"
        >
            <i data-lucide="x" class="h-4 w-4 {{ $iconColors[$type] }}"></i>
        </button>
    @endif
</div>
