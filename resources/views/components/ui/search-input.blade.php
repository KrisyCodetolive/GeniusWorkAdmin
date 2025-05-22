@props(['placeholder' => 'Rechercher...', 'name' => 'search', 'value' => null])

<div class="relative">
    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>
    <input 
        type="search" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="{{ $value }}"
        placeholder="{{ $placeholder }}" 
        {{ $attributes->merge(['class' => 'pl-10 pr-4 py-2 w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200']) }}
    />
    @if($value)
        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
            <button type="button" onclick="document.getElementById('{{ $name }}').value = ''; this.closest('form').submit();" class="text-gray-400 hover:text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    @endif
</div>
