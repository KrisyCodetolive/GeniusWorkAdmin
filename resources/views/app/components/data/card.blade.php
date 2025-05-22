@props(['title' => null, 'footer' => null, 'hover' => true, 'padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-sm ' . ($hover ? 'hover:shadow-md transition-shadow duration-300' : '') . ' overflow-hidden']) }}>
    @if($title)
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
        </div>
    @endif
    
    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="px-5 py-4 bg-gray-50 border-t border-gray-200">
            {{ $footer }}
        </div>
    @endif
</div>
