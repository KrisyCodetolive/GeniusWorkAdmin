@props(['type' => 'button', 'variant' => 'primary', 'icon' => null, 'iconPosition' => 'left', 'size' => 'md'])

@php
    $baseClasses = 'inline-flex items-center border font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200';
    
    $variantClasses = [
        'primary' => 'border-transparent text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'border-transparent text-white bg-gray-600 hover:bg-gray-700 focus:ring-gray-500',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'warning' => 'border-transparent text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
        'info' => 'border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
        'light' => 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500',
        'dark' => 'border-transparent text-white bg-gray-800 hover:bg-gray-900 focus:ring-gray-700',
        'link' => 'border-transparent text-blue-600 bg-transparent hover:text-blue-800 hover:underline focus:ring-blue-500'
    ];
    
    $sizeClasses = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
        'xl' => 'px-6 py-3 text-base'
    ];
    
    $iconSpacing = [
        'left' => 'mr-2',
        'right' => 'ml-2'
    ];
    
    $iconSizes = [
        'xs' => 'h-3 w-3',
        'sm' => 'h-4 w-4',
        'md' => 'h-5 w-5',
        'lg' => 'h-5 w-5',
        'xl' => 'h-6 w-6'
    ];
    
    $buttonClasses = $baseClasses . ' ' . ($variantClasses[$variant] ?? $variantClasses['primary']) . ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => $buttonClasses]) }}
>
    @if($icon && $iconPosition === 'left')
        <span class="{{ $iconSpacing['left'] }} {{ $iconSizes[$size] }}">{!! $icon !!}</span>
    @endif
    
    {{ $slot }}
    
    @if($icon && $iconPosition === 'right')
        <span class="{{ $iconSpacing['right'] }} {{ $iconSizes[$size] }}">{!! $icon !!}</span>
    @endif
</button>
