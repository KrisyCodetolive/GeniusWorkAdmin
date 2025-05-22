@props(['label', 'name', 'id' => null, 'value' => null, 'placeholder' => null, 'required' => false, 'rows' => 3, 'error' => null])

<div class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <textarea 
        id="{{ $id ?? $name }}" 
        name="{{ $name }}" 
        rows="{{ $rows }}" 
        placeholder="{{ $placeholder }}" 
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200' . ($error ? ' border-red-500' : '')]) }}
    >{{ $value }}</textarea>
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
