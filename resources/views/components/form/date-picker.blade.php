@props(['label', 'name', 'id' => null, 'value' => null, 'required' => false, 'error' => null, 'placeholder' => 'JJ/MM/AAAA', 'format' => 'DD/MM/YYYY'])

<div x-data="{ 
    value: @js($value), 
    init() {
        this.$nextTick(() => {
            const picker = flatpickr(this.$refs.input, {
                dateFormat: 'd/m/Y',
                allowInput: true,
                altInput: true,
                altFormat: @js($format === 'DD/MM/YYYY' ? 'd/m/Y' : 'Y-m-d'),
                locale: {
                    firstDayOfWeek: 1,
                    weekdays: {
                        shorthand: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
                        longhand: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
                    },
                    months: {
                        shorthand: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
                        longhand: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre']
                    }
                }
            });
        });
    }
}" class="space-y-1">
    <label for="{{ $id ?? $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i data-lucide="calendar" class="h-5 w-5 text-gray-400"></i>
        </div>
        
        <input 
            x-ref="input"
            type="text" 
            id="{{ $id ?? $name }}" 
            name="{{ $name }}" 
            value="{{ $value }}" 
            placeholder="{{ $placeholder }}" 
            @if($required) required @endif
            {{ $attributes->merge(['class' => 'pl-10 w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200' . ($error ? ' border-red-500' : '')]) }}
        />
    </div>
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush
