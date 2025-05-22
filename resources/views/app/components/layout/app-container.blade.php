@props(['fluid' => false, 'padding' => true])

<div {{ $attributes->merge(['class' => ($fluid ? 'w-full' : 'max-w-7xl mx-auto') . ' ' . ($padding ? 'px-4 sm:px-6 lg:px-8' : '')]) }}>
    {{ $slot }}
</div>
