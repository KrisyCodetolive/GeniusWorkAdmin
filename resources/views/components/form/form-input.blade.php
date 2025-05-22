@props(['label', 'name', 'id' => null, 'type' => 'text', 'value' => null, 'placeholder' => null, 'required' => false, 'disabled' => false, 'error' => null])

<div class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <input 
        type="{{ $type }}" 
        id="{{ $id ?? $name }}" 
        name="{{ $name }}" 
        value="{{ $value }}" 
        placeholder="{{ $placeholder }}" 
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200' . ($error ? ' border-red-500' : '')]) }}
    />
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
