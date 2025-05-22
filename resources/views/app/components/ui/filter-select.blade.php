@props([
    'name',
    'label',
    'icon' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => 'Sélectionner une option',
    'disabled' => false,
])

<div {{ $attributes }} x-data="{ open: false, selectedLabel: '{{ $selected ? ($options[$selected] ?? $placeholder) : $placeholder }}' }">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    <div class="relative">
        <input type="hidden" name="{{ $name }}" x-ref="input" value="{{ $selected }}" />
        
        <button 
            type="button"
            @click="open = !open" 
            @keydown.escape.window="open = false"
            @click.away="open = false"
            :aria-expanded="open"
            aria-haspopup="listbox"
            class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm pl-3 pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-300 transition ease-in-out duration-150 {{ $disabled ? 'opacity-75 cursor-not-allowed' : 'hover:border-gray-400' }} {{ $icon ? 'pl-10' : '' }}"
            :class="{ 'ring-2 ring-blue-200 border-blue-300': open }"
            {{ $disabled ? 'disabled' : '' }}
        >
            @if($icon)
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                    <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
                </span>
            @endif
            
            <span class="block truncate text-gray-700" x-text="selectedLabel"></span>
            
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <i data-lucide="chevron-down" class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="{ 'transform rotate-180': open }"></i>
            </span>
        </button>

        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
            style="display: none; min-width: 100%;"
            x-cloak
        >
            <div class="py-1">
                <a 
                    href="#" 
                    @click.prevent="$refs.input.value = ''; selectedLabel = '{{ $placeholder }}'; open = false;"
                    class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 cursor-pointer w-full whitespace-normal"
                >
                    {{ $placeholder }}
                </a>
                
                <div class="max-h-48 overflow-y-auto">
                    @foreach($options as $value => $optionLabel)
                        <a 
                            href="#" 
                            @click.prevent="$refs.input.value = '{{ $value }}'; selectedLabel = '{{ $optionLabel }}'; open = false;"
                            class="block px-4 py-2.5 text-sm {{ $selected == $value ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} cursor-pointer w-full whitespace-normal"
                        >
                            {{ $optionLabel }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
