@props(['name', 'label', 'required' => false, 'icon' => null])

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
        
        <select 
            name="{{ $name }}" 
            id="{{ $name }}" 
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300 ' . ($icon ? 'pl-10 ' : '') . 'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out appearance-none bg-white']) }}
        >
            {{ $slot }}
        </select>
        
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
    @enderror
</div>
