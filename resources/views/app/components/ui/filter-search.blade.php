@props([
    'name' => 'search',
    'label' => 'Recherche',
    'placeholder' => 'Rechercher...',
    'value' => '',
])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
            <i data-lucide="search" class="h-4 w-4"></i>
        </div>
        
        <input 
            type="text" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ $value }}" 
            placeholder="{{ $placeholder }}" 
            class="w-full pl-10 pr-10 py-2 rounded-lg border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
        
        @if($value)
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <button type="button" onclick="document.getElementById('{{ $name }}').value = ''; this.closest('form').submit();" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
        @endif
    </div>
</div>
