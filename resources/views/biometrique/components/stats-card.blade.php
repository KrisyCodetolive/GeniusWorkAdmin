@props(['title', 'value', 'icon', 'color' => 'blue', 'trend' => null, 'trendValue' => null])

@php
    $colorClasses = [
        'blue' => 'bg-blue-50 text-blue-600',
        'green' => 'bg-green-50 text-green-600',
        'red' => 'bg-red-50 text-red-600',
        'yellow' => 'bg-yellow-50 text-yellow-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'pink' => 'bg-pink-50 text-pink-600',
    ];
    
    $trendClasses = [
        'up' => 'text-green-600',
        'down' => 'text-red-600',
        'neutral' => 'text-gray-600',
    ];
    
    $iconClass = $colorClasses[$color] ?? $colorClasses['blue'];
    $trendClass = $trendClasses[$trend] ?? $trendClasses['neutral'];
@endphp

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-5">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="rounded-md p-3 {{ $iconClass }}">
                    {!! $icon !!}
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ $title }}
                    </dt>
                    <dd>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $value }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    
    @if($trend)
    <div class="bg-gray-50 px-5 py-3">
        <div class="text-sm flex items-center">
            @if($trend === 'up')
                <svg class="h-4 w-4 {{ $trendClass }} mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            @elseif($trend === 'down')
                <svg class="h-4 w-4 {{ $trendClass }} mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                </svg>
            @else
                <svg class="h-4 w-4 {{ $trendClass }} mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                </svg>
            @endif
            <span class="{{ $trendClass }}">
                {{ $trendValue }}
            </span>
            <span class="text-gray-500 ml-1">
                vs période précédente
            </span>
        </div>
    </div>
    @endif
</div>
