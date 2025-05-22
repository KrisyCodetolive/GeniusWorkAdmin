@props(['submitText' => 'Enregistrer', 'cancelText' => 'Annuler', 'cancelUrl' => null])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end space-x-3 pt-5 border-t border-gray-200 mt-8']) }}>
    @if($cancelUrl)
        <a 
            href="{{ $cancelUrl }}" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            {{ $cancelText }}
        </a>
    @endif
    
    <button 
        type="submit" 
        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white gradient-bg hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition hover:-translate-y-0.5"
    >
        {{ $submitText }}
    </button>
    
    {{ $slot }}
</div>
