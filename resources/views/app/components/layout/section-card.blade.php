@props(['title' => null])

<div class="bg-white rounded-xl shadow-sm hover:shadow transition-shadow duration-300 overflow-hidden mb-6">
    @if($title)
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-800">{{ $title }}</h2>
    </div>
    @endif
    <div {{ $attributes->merge(['class' => 'p-5']) }}>
        {{ $slot }}
    </div>
</div>
