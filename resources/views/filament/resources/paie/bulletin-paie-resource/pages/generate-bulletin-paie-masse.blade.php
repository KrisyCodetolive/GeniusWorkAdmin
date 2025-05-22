<x-filament-panels::page>
    <form wire:submit.prevent="generateMasse">
        {{ $this->form }}
        
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                Générer les bulletins
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
