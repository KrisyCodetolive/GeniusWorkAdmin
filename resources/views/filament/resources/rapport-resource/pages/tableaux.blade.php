<x-filament-panels::page>
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight">Tableaux de bord en temps réel</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Période: {{ $this->dateDebut->format('d/m/Y') }} au {{ $this->dateFin->format('d/m/Y') }}
            @if($this->selectedEmployeur)
                | Employeur: {{ \App\Models\Employeur::find($this->selectedEmployeur)->nom }}
            @else
                | Tous les employeurs
            @endif
        </p>
    </div>

    <div class="mb-6">
        <nav class="flex space-x-4" aria-label="Tabs">
            <button 
                wire:click="setActiveTab('presence')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $this->activeTab === 'presence' ? 'bg-primary-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                Présences
            </button>
            <button 
                wire:click="setActiveTab('retards')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $this->activeTab === 'retards' ? 'bg-primary-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                Retards & Absences
            </button>
            <button 
                wire:click="setActiveTab('heures_supp')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $this->activeTab === 'heures_supp' ? 'bg-primary-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                Heures supplémentaires
            </button>
            <button 
                wire:click="setActiveTab('conges')" 
                class="px-3 py-2 text-sm font-medium rounded-md {{ $this->activeTab === 'conges' ? 'bg-primary-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                Congés
            </button>
        </nav>
    </div>

    <!-- Contenu des onglets -->
    <div>
        @if($this->activeTab === 'presence')
            @livewire('rapport-presence-dashboard', [
                'dateDebut' => $this->dateDebut,
                'dateFin' => $this->dateFin,
                'employeurId' => $this->selectedEmployeur
            ])
        @elseif($this->activeTab === 'retards')
            @livewire('rapport-retards-dashboard', [
                'dateDebut' => $this->dateDebut,
                'dateFin' => $this->dateFin,
                'employeurId' => $this->selectedEmployeur
            ])
        @elseif($this->activeTab === 'heures_supp')
            @livewire('rapport-heures-supp-dashboard', [
                'dateDebut' => $this->dateDebut,
                'dateFin' => $this->dateFin,
                'employeurId' => $this->selectedEmployeur
            ])
        @elseif($this->activeTab === 'conges')
            @livewire('rapport-conges-dashboard', [
                'dateDebut' => $this->dateDebut,
                'dateFin' => $this->dateFin,
                'employeurId' => $this->selectedEmployeur
            ])
        @endif
    </div>
</x-filament-panels::page>
