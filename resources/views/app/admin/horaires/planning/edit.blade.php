<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier le planning de') }} {{ $employe->getNomComplet() }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Informations de l\'employé') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-gray-600">{{ __('Nom complet') }}</p>
                            <p class="font-semibold">{{ $employe->getNomComplet() }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Matricule') }}</p>
                            <p class="font-semibold">{{ $employe->matricule }}</p>
                        </div>

                        <div>
                            <p class="text-gray-600">{{ __('Département') }}</p>
                            <p class="font-semibold">{{ $employe->departement ? $employe->departement->nom : '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Copier un planning existant') }}</h3>
                    
                    <form action="{{ route('admin.horaires.planning.copy') }}" method="POST" class="mb-6">
                        @csrf
                        <input type="hidden" name="employe_id" value="{{ $employe->id }}">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-label for="source_employe_id" :value="__('Copier depuis l\'employé')" />
                                <select id="source_employe_id" name="source_employe_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="">{{ __('Sélectionner un employé') }}</option>
                                    @foreach ($autresEmployes as $autreEmploye)
                                        <option value="{{ $autreEmploye->id }}">
                                            {{ $autreEmploye->getNomComplet() }} ({{ $autreEmploye->matricule }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end">
                                <x-button class="w-full justify-center">
                                    <i class="fas fa-copy mr-2"></i>{{ __('Copier le planning') }}
                                </x-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Modifier le planning hebdomadaire') }}</h3>
                    
                    <form action="{{ route('admin.horaires.planning.update', $employe) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @php
                                $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                $joursTravail = $employe->joursTravail()->with('plagesHoraires')->get()->keyBy('jour');
                            @endphp

                            @foreach ($jours as $index => $jour)
                                @php
                                    $jourIndex = $index + 1;
                                    $jourTravail = $joursTravail[$jourIndex] ?? null;
                                    $plagesJour = $jourTravail ? $jourTravail->plagesHoraires->pluck('id')->toArray() : [];
                                @endphp
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="font-semibold mb-3">{{ $jour }}</h4>
                                    
                                    <div class="mb-3">
                                        <div class="flex items-center">
                                            <input id="jour_travaille_{{ $jourIndex }}" type="checkbox" name="jours_travailles[{{ $jourIndex }}]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $jourTravail ? 'checked' : '' }}>
                                            <label for="jour_travaille_{{ $jourIndex }}" class="ml-2 block text-sm text-gray-900">{{ __('Jour travaillé') }}</label>
                                        </div>
                                    </div>
                                    
                                    <div class="plages-container {{ $jourTravail ? '' : 'hidden' }}" id="plages_container_{{ $jourIndex }}">
                                        <div class="mb-3">
                                            <x-label for="plages_{{ $jourIndex }}" :value="__('Plages horaires')" />
                                            <select id="plages_{{ $jourIndex }}" name="plages[{{ $jourIndex }}][]" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" size="6">
                                                @foreach ($plagesStandard as $plage)
                                                    <option value="{{ $plage->id }}" {{ in_array($plage->id, $plagesJour) ? 'selected' : '' }} class="py-1 px-2 {{ $plage->est_pause ? 'bg-green-50' : '' }}">
                                                        {{ $plage->nom }} ({{ $plage->getPlageFormatee() }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1">{{ __('Maintenez Ctrl pour sélectionner plusieurs plages') }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <x-label for="plages_speciales_{{ $jourIndex }}" :value="__('Plages spéciales')" />
                                            <select id="plages_speciales_{{ $jourIndex }}" name="plages_speciales[{{ $jourIndex }}][]" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" size="4">
                                                @foreach ($plagesSpeciales as $plage)
                                                    <option value="{{ $plage->id }}" {{ in_array($plage->id, $plagesJour) ? 'selected' : '' }} class="py-1 px-2">
                                                        {{ $plage->nom }} ({{ $plage->getPlageFormatee() }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-xs text-gray-500 mt-1">{{ __('Plages horaires spéciales (optionnel)') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.horaires.planning.show', $employe) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                {{ __('Annuler') }}
                            </a>
                            <x-button>
                                {{ __('Enregistrer le planning') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer l'affichage des plages horaires en fonction de l'état des checkboxes
            const joursTravailles = document.querySelectorAll('[id^="jour_travaille_"]');
            
            joursTravailles.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const jourIndex = this.id.split('_').pop();
                    const plagesContainer = document.getElementById(`plages_container_${jourIndex}`);
                    
                    if (this.checked) {
                        plagesContainer.classList.remove('hidden');
                    } else {
                        plagesContainer.classList.add('hidden');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
