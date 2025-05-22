<x-filament-panels::page>
    <form wire:submit.prevent="generate">
        {{ $this->form }}
        
        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" wire:loading.attr="disabled">
                Générer le bulletin
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
