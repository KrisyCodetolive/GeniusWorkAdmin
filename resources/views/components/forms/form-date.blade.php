@props(['name', 'label', 'required' => false, 'value' => '', 'icon' => null])

<div class="form-group mb-4" x-data="{ focused: false }">
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
            type="date" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="{{ old($name, $value) }}"
            @if($required) required @endif
            x-on:focus="focused = true"
            x-on:blur="focused = false"
            {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300 ' . ($icon ? 'pl-10 ' : '') . 'focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out']) }}
        >
        
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" 
                :class="{ 'text-blue-500': focused }">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>
    
    @error($name)
        <p class="mt-1 text-sm text-red-600 animate-pulse">{{ $message }}</p>
    @enderror
</div>
