<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Créer un nouveau planning') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Sélection des employés') }}</h3>
                    
                    <form action="{{ route('admin.horaires.planning.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-6">
                            <x-label for="employe_type" :value="__('Type de sélection')" />
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="employe_type" value="individual" class="form-radio" checked>
                                    <span class="ml-2">{{ __('Employé individuel') }}</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="employe_type" value="department" class="form-radio">
                                    <span class="ml-2">{{ __('Département entier') }}</span>
                                </label>
                            </div>
                        </div>
                        
                        <div id="individual_selection" class="mb-6">
                            <x-label for="employe_id" :value="__('Employé')" />
                            <select id="employe_id" name="employe_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('Sélectionner un employé') }}</option>
                                @foreach ($employes as $employe)
                                    <option value="{{ $employe->id }}">
                                        {{ $employe->getNomComplet() }} ({{ $employe->matricule }}) - {{ $employe->departement ? $employe->departement->nom : 'Sans département' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employe_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="department_selection" class="mb-6 hidden">
                            <x-label for="departement_id" :value="__('Département')" />
                            <select id="departement_id" name="departement_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('Sélectionner un département') }}</option>
                                @foreach ($departements as $departement)
                                    <option value="{{ $departement->id }}">
                                        {{ $departement->nom }} ({{ $departement->employes()->count() }} employés)
                                    </option>
                                @endforeach
                            </select>
                            @error('departement_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <x-label for="template_type" :value="__('Type de planning')" />
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="template_type" value="custom" class="form-radio" checked>
                                    <span class="ml-2">{{ __('Planning personnalisé') }}</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="template_type" value="template" class="form-radio">
                                    <span class="ml-2">{{ __('Utiliser un modèle') }}</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="template_type" value="copy" class="form-radio">
                                    <span class="ml-2">{{ __('Copier depuis un employé') }}</span>
                                </label>
                            </div>
                        </div>
                        
                        <div id="template_selection" class="mb-6 hidden">
                            <x-label for="template_id" :value="__('Modèle de planning')" />
                            <select id="template_id" name="template_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('Sélectionner un modèle') }}</option>
                                <option value="standard_35h">{{ __('Standard 35h (Lun-Ven, 9h-17h)') }}</option>
                                <option value="standard_39h">{{ __('Standard 39h (Lun-Ven, 8h30-17h30)') }}</option>
                                <option value="service_client">{{ __('Service client (Lun-Sam, horaires variables)') }}</option>
                                <option value="production">{{ __('Production (2x8, équipes alternées)') }}</option>
                            </select>
                            @error('template_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="copy_selection" class="mb-6 hidden">
                            <x-label for="source_employe_id" :value="__('Copier depuis l\'employé')" />
                            <select id="source_employe_id" name="source_employe_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('Sélectionner un employé source') }}</option>
                                @foreach ($employes as $employe)
                                    <option value="{{ $employe->id }}">
                                        {{ $employe->getNomComplet() }} ({{ $employe->matricule }}) - {{ $employe->departement ? $employe->departement->nom : 'Sans département' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('source_employe_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="custom_planning" class="mb-6">
                            <h4 class="font-medium text-lg mb-4">{{ __('Configuration du planning personnalisé') }}</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                @php
                                    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                                @endphp

                                @foreach ($jours as $index => $jour)
                                    @php
                                        $jourIndex = $index + 1;
                                    @endphp
                                    
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h4 class="font-semibold mb-3">{{ $jour }}</h4>
                                        
                                        <div class="mb-3">
                                            <div class="flex items-center">
                                                <input id="jour_travaille_{{ $jourIndex }}" type="checkbox" name="jours_travailles[{{ $jourIndex }}]" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" {{ $jourIndex <= 5 ? 'checked' : '' }}>
                                                <label for="jour_travaille_{{ $jourIndex }}" class="ml-2 block text-sm text-gray-900">{{ __('Jour travaillé') }}</label>
                                            </div>
                                        </div>
                                        
                                        <div class="plages-container {{ $jourIndex <= 5 ? '' : 'hidden' }}" id="plages_container_{{ $jourIndex }}">
                                            <div class="mb-3">
                                                <x-label for="plages_{{ $jourIndex }}" :value="__('Plages horaires')" />
                                                <select id="plages_{{ $jourIndex }}" name="plages[{{ $jourIndex }}][]" multiple class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" size="6">
                                                    @foreach ($plagesStandard as $plage)
                                                        <option value="{{ $plage->id }}" class="py-1 px-2 {{ $plage->est_pause ? 'bg-green-50' : '' }}">
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
                                                        <option value="{{ $plage->id }}" class="py-1 px-2">
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
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.horaires.planning.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                {{ __('Annuler') }}
                            </a>
                            <x-button>
                                {{ __('Créer le planning') }}
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
            // Gestion du type de sélection (individuel ou département)
            const employeTypeRadios = document.querySelectorAll('input[name="employe_type"]');
            const individualSelection = document.getElementById('individual_selection');
            const departmentSelection = document.getElementById('department_selection');
            
            employeTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'individual') {
                        individualSelection.classList.remove('hidden');
                        departmentSelection.classList.add('hidden');
                    } else {
                        individualSelection.classList.add('hidden');
                        departmentSelection.classList.remove('hidden');
                    }
                });
            });
            
            // Gestion du type de template
            const templateTypeRadios = document.querySelectorAll('input[name="template_type"]');
            const templateSelection = document.getElementById('template_selection');
            const copySelection = document.getElementById('copy_selection');
            const customPlanning = document.getElementById('custom_planning');
            
            templateTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    templateSelection.classList.add('hidden');
                    copySelection.classList.add('hidden');
                    customPlanning.classList.add('hidden');
                    
                    if (this.value === 'template') {
                        templateSelection.classList.remove('hidden');
                    } else if (this.value === 'copy') {
                        copySelection.classList.remove('hidden');
                    } else {
                        customPlanning.classList.remove('hidden');
                    }
                });
            });
            
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
