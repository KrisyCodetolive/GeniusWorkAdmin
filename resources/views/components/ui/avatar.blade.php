@props(['src' => null, 'alt' => '', 'size' => 'md', 'initials' => null, 'status' => null])

@php
    $sizeClasses = [
        'xs' => 'h-6 w-6 text-xs',
        'sm' => 'h-8 w-8 text-sm',
        'md' => 'h-10 w-10 text-base',
        'lg' => 'h-12 w-12 text-lg',
        'xl' => 'h-16 w-16 text-xl',
        '2xl' => 'h-20 w-20 text-2xl',
    ];
    
    $statusClasses = [
        'online' => 'bg-green-400',
        'offline' => 'bg-gray-400',
        'busy' => 'bg-red-400',
        'away' => 'bg-yellow-400',
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $statusClass = isset($statusClasses[$status]) ? $statusClasses[$status] : null;
@endphp

<div class="relative inline-block">
    @if($src)
        <img 
            src="{{ $src }}" 
            alt="{{ $alt }}" 
            {{ $attributes->merge(['class' => $sizeClass . ' rounded-full object-cover']) }}
        />
    @else
        <div 
            {{ $attributes->merge(['class' => $sizeClass . ' rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-medium']) }}
        >
            @if($initials)
                <span>{{ $initials }}</span>
            @else
                <svg class="h-3/4 w-3/4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            @endif
        </div>
    @endif
    
    @if($status)
        <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white {{ $statusClass }}"></span>
    @endif
</div>
