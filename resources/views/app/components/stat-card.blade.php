@props([
    'title',
    'value',
    'icon',
    'color' => 'indigo',
    'trend' => null,
    'trendValue' => null,
    'trendUp' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg shadow-sm border border-gray-100 p-4 card-hover']) }}>
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500 mb-1">{{ $title }}</p>
            <h3 class="text-2xl font-semibold text-gray-800">{{ $value }}</h3>
            
            @if($trend)
                <div class="flex items-center mt-2">
                    <span class="{{ $trendUp ? 'text-green-600' : 'text-red-600' }} text-xs font-medium flex items-center">
                        <i data-lucide="{{ $trendUp ? 'trending-up' : 'trending-down' }}" class="h-3 w-3 mr-1"></i>
                        {{ $trendValue }}
                    </span>
                    <span class="text-xs text-gray-500 ml-1">{{ $trend }}</span>
                </div>
            @endif
        </div>
        
        <div class="bg-{{ $color }}-100 p-3 rounded-lg">
            <i data-lucide="{{ $icon }}" class="h-6 w-6 text-{{ $color }}-600"></i>
        </div>
    </div>
</div>
