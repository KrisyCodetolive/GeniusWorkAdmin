@props(['href', 'icon' => null, 'active' => false, 'badge' => null, 'badgeColor' => 'red'])

@php
    $activeClasses = $active 
        ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-600 font-medium' 
        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
        
    $badgeColorClasses = [
        'red' => 'bg-red-100 text-red-800',
        'green' => 'bg-green-100 text-green-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'pink' => 'bg-pink-100 text-pink-800',
        'gray' => 'bg-gray-100 text-gray-800',
    ][$badgeColor] ?? 'bg-red-100 text-red-800';
@endphp

<a 
    href="{{ $href }}" 
    {{ $attributes->merge(['class' => 'group flex items-center px-4 py-2 text-sm ' . $activeClasses]) }}
>
    @if($icon)
        <i data-lucide="{{ $icon }}" class="mr-3 flex-shrink-0 h-5 w-5 {{ $active ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
    @endif
    
    <span class="flex-1">{{ $slot }}</span>
    
    @if($badge)
        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColorClasses }}">
            {{ $badge }}
        </span>
    @endif
</a>
