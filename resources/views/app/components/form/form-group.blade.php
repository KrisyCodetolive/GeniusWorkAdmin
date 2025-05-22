@props(['title' => null, 'description' => null])

<div {{ $attributes->merge(['class' => 'space-y-4 p-5 bg-gray-50 rounded-lg border border-gray-200']) }}>
    @if($title)
        <div class="mb-2">
            <h3 class="text-lg font-medium text-gray-900">{{ $title }}</h3>
            @if($description)
                <p class="text-sm text-gray-500">{{ $description }}</p>
            @endif
        </div>
    @endif
    
    <div class="space-y-4">
        {{ $slot }}
    </div>
</div>
