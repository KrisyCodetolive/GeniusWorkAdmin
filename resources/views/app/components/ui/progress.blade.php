@props(['value' => 0, 'max' => 100, 'height' => 'md', 'color' => 'blue', 'showLabel' => true, 'animated' => true])

@php
    // Ensure value is between 0 and max
    $value = max(0, min($max, $value));
    
    // Calculate percentage
    $percentage = $max > 0 ? round(($value / $max) * 100) : 0;
    
    // Height classes
    $heightClasses = [
        'xs' => 'h-1',
        'sm' => 'h-2',
        'md' => 'h-3',
        'lg' => 'h-4',
        'xl' => 'h-5',
    ][$height] ?? 'h-3';
    
    // Color classes
    $colorClasses = [
        'blue' => 'bg-blue-600',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
        'yellow' => 'bg-yellow-500',
        'indigo' => 'bg-indigo-600',
        'purple' => 'bg-purple-600',
        'pink' => 'bg-pink-500',
    ][$color] ?? 'bg-blue-600';
@endphp

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if($showLabel)
        <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-gray-700">{{ $slot }}</span>
            <span class="text-sm font-medium text-gray-700">{{ $percentage }}%</span>
        </div>
    @endif
    
    <div class="bg-gray-200 rounded-full overflow-hidden {{ $heightClasses }}">
        <div 
            class="{{ $colorClasses }} rounded-full {{ $animated ? 'transition-all duration-500 ease-out' : '' }}" 
            style="width: {{ $percentage }}%"
            role="progressbar" 
            aria-valuenow="{{ $value }}" 
            aria-valuemin="0" 
            aria-valuemax="{{ $max }}"
        ></div>
    </div>
</div>
