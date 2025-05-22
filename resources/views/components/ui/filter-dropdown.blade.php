@props(['label', 'name', 'options' => [], 'selected' => null])

<div x-data="{ open: false }" class="relative">
    <button 
        @click="open = !open" 
        type="button" 
        class="inline-flex justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
    >
        {{ $label }}
        <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div 
        x-show="open" 
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 z-10 w-full mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
    >
        <div class="py-1">
            @foreach($options as $value => $text)
                <a 
                    href="#" 
                    @click.prevent="document.getElementById('{{ $name }}_form').elements.{{ $name }}.value = '{{ $value }}'; document.getElementById('{{ $name }}_form').submit(); open = false"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $selected == $value ? 'bg-gray-100 font-medium' : '' }}"
                >
                    {{ $text }}
                </a>
            @endforeach
        </div>
    </div>
</div>
