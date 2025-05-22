@props([
    'type' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'loading' => false,
    'disabled' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg focus:outline-none transition-all';
    
    $sizeClasses = [
        'xs' => 'text-xs px-2.5 py-1.5',
        'sm' => 'text-sm px-3 py-2',
        'md' => 'text-sm px-4 py-2',
        'lg' => 'text-base px-5 py-3',
        'xl' => 'text-base px-6 py-3.5',
    ];
    
    $typeClasses = [
        'primary' => 'gradient-bg text-white hover:shadow-lg transform hover:-translate-y-0.5',
        'secondary' => 'bg-gray-100 text-gray-800 hover:bg-gray-200',
        'success' => 'success-gradient-bg text-white hover:shadow-lg transform hover:-translate-y-0.5',
        'danger' => 'danger-gradient-bg text-white hover:shadow-lg transform hover:-translate-y-0.5',
        'warning' => 'warning-gradient-bg text-white hover:shadow-lg transform hover:-translate-y-0.5',
        'outline' => 'bg-transparent border border-gray-300 text-gray-700 hover:bg-gray-50',
        'outline-primary' => 'bg-transparent border border-indigo-500 text-indigo-600 hover:bg-indigo-50',
        'link' => 'bg-transparent text-indigo-600 hover:text-indigo-800 hover:underline',
    ];
    
    $disabledClasses = 'opacity-50 cursor-not-allowed pointer-events-none';
    
    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $typeClasses[$type] . ' ' . ($disabled ? $disabledClasses : '');
@endphp

@if($href)
    <a 
        href="{{ $disabled ? '#' : $href }}" 
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled) aria-disabled="true" tabindex="-1" @endif
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 {{ $type === 'outline' || $type === 'outline-primary' || $type === 'link' ? 'text-indigo-600' : 'text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="h-5 w-5 mr-2 -ml-1"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="h-5 w-5 ml-2 -mr-1"></i>
        @endif
    </a>
@else
    <button 
        {{ $attributes->merge(['class' => $classes, 'type' => 'button', 'disabled' => $disabled]) }}
    >
        @if($loading)
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 {{ $type === 'outline' || $type === 'outline-primary' || $type === 'link' ? 'text-indigo-600' : 'text-white' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="h-5 w-5 mr-2 -ml-1"></i>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="h-5 w-5 ml-2 -mr-1"></i>
        @endif
    </button>
@endif
