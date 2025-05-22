@props(['name', 'label', 'type' => 'text', 'required' => false, 'placeholder' => '', 'icon' => null])

<div class="form-group mb-4">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
        @if($icon)
            <span class="text-gray-500 mr-2">{!! $icon !!}</span>
        @endif
        {{ $label }}
        @if($required)
            <span class="text-red-500 ml-1">*</span>
        @endif
    </label>
    
    <div class="relative rounded-md shadow-sm">
        @if($icon)
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                {!! $icon !!}
            </div>
        @endif
        
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ old($name, $attributes->get('value')) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300 ' . ($icon ? 'pl-10 ' : '') . 'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out']) }}
        >
    </div>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
    @enderror
</div>
