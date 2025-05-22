@props(['label', 'name', 'id' => null, 'value' => null, 'checked' => false, 'description' => null, 'error' => null])

<div class="relative flex items-start">
    <div class="flex items-center h-5">
        <input 
            type="radio" 
            id="{{ $id ?? $name . '_' . $value }}" 
            name="{{ $name }}" 
            value="{{ $value }}" 
            @if($checked) checked @endif
            {{ $attributes->merge(['class' => 'h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500' . ($error ? ' border-red-500' : '')]) }}
        />
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $id ?? $name . '_' . $value }}" class="font-medium text-gray-700">{{ $label }}</label>
        @if($description)
            <p class="text-gray-500">{{ $description }}</p>
        @endif
    </div>
</div>
