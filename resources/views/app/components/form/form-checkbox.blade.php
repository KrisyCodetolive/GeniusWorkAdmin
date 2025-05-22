@props(['label', 'name', 'id' => null, 'value' => '1', 'checked' => false, 'description' => null, 'error' => null])

<div class="relative flex items-start">
    <div class="flex items-center h-5">
        <input 
            type="checkbox" 
            id="{{ $id ?? $name }}" 
            name="{{ $name }}" 
            value="{{ $value }}" 
            @if($checked) checked @endif
            {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500' . ($error ? ' border-red-500' : '')]) }}
        />
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $id ?? $name }}" class="font-medium text-gray-700">{{ $label }}</label>
        @if($description)
            <p class="text-gray-500">{{ $description }}</p>
        @endif
        @if($error)
            <p class="text-sm text-red-600 mt-1">{{ $error }}</p>
        @endif
    </div>
</div>
