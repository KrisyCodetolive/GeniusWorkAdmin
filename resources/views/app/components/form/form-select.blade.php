@props(['label', 'name', 'id' => null, 'options' => [], 'selected' => null])

<div class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <select 
        id="{{ $id ?? $name }}" 
        name="{{ $name }}" 
        {{ $attributes->merge(['class' => 'w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200']) }}
    >
        {{ $slot }}
    </select>
</div>
