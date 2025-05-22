@props(['title', 'buttons' => []])

<div class="flex justify-between items-center mb-8">
    <div class="flex items-center space-x-3">
        <h1 class="text-3xl font-bold text-gray-800">{{ $title }}</h1>
        {{ $slot ?? '' }}
    </div>
    <div class="flex space-x-3">
        @foreach($buttons as $button)
            {{ $button }}
        @endforeach
    </div>
</div>
