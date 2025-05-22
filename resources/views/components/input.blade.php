@props([
    'label' => null,
    'name',
    'id' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'helpText' => null,
    'disabled' => false,
    'required' => false,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
    $id = $id ?? $name;
@endphp

<div {{ $attributes }}>
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative rounded-md shadow-sm">
        @if($icon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="{{ $icon }}" class="h-5 w-5 text-gray-400"></i>
            </div>
        @endif
        
        <input 
            type="{{ $type }}" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            value="{{ $value }}" 
            placeholder="{{ $placeholder }}" 
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $error ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500' : '' }} {{ $icon && $iconPosition === 'left' ? 'pl-10' : '' }} {{ $icon && $iconPosition === 'right' ? 'pr-10' : '' }} {{ $disabled ? 'bg-gray-100 cursor-not-allowed' : '' }}"
            {{ $attributes->except(['class']) }}
        />
        
        @if($icon && $iconPosition === 'right')
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i data-lucide="{{ $icon }}" class="h-5 w-5 text-gray-400"></i>
            </div>
        @endif
    </div>
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
    
    @if($helpText)
        <p class="mt-1 text-sm text-gray-500">{{ $helpText }}</p>
    @endif
</div>
