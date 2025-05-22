<x-filament::section>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight">
                Mode impersonation actif
            </h2>
            
            @php
                $originalUser = $this->getOriginalUserData();
            @endphp
            
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Vous êtes actuellement connecté en tant qu'un autre utilisateur. Votre compte d'origine est : 
                <span class="font-medium text-primary-600 dark:text-primary-400">
                    {{ $originalUser['name'] }} ({{ ucfirst($originalUser['role']) }})
                </span>
            </p>
        </div>
        
        <div class="flex items-center gap-4">
            <x-filament::button
                color="danger"
                wire:click="stopImpersonating"
                wire:loading.attr="disabled"
            >
                <span class="flex items-center gap-1">
                    <x-heroicon-o-arrow-left-circle class="h-5 w-5" />
                    Revenir à mon compte
                </span>
            </x-filament::button>
        </div>
    </div>
</x-filament::section>
