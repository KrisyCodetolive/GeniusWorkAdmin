@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Générer un rapport d'heures de travail</h1>
                
                <form action="{{ route('rapports.heures-travail.generer') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Type de rapport -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="type_rapport" class="block text-sm font-medium text-gray-700 mb-1">Type de rapport</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type_rapport" value="journalier" class="form-radio h-5 w-5 text-indigo-600" checked>
                                    <span class="ml-2 text-gray-700">Journalier</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type_rapport" value="hebdomadaire" class="form-radio h-5 w-5 text-indigo-600">
                                    <span class="ml-2 text-gray-700">Hebdomadaire</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type_rapport" value="mensuel" class="form-radio h-5 w-5 text-indigo-600">
                                    <span class="ml-2 text-gray-700">Mensuel</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="type_rapport" value="personnalise" class="form-radio h-5 w-5 text-indigo-600">
                                    <span class="ml-2 text-gray-700">Période personnalisée</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Date de début -->
                        <div>
                            <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut" value="{{ now()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <!-- Date de fin (pour période personnalisée) -->
                        <div id="date_fin_container">
                            <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin" value="{{ now()->format('Y-m-d') }}" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        
                        <!-- Site -->
                        <div class="col-span-1 md:col-span-2">
                            <label for="site_id" class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                            <select name="site_id" id="site_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Tous les sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Générer le rapport
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeRapportRadios = document.querySelectorAll('input[name="type_rapport"]');
        const dateDebutInput = document.getElementById('date_debut');
        const dateFinInput = document.getElementById('date_fin');
        const dateFinContainer = document.getElementById('date_fin_container');
        
        // Fonction pour mettre à jour l'affichage des champs de date
        function updateDateFields() {
            const selectedType = document.querySelector('input[name="type_rapport"]:checked').value;
            
            if (selectedType === 'journalier') {
                dateFinContainer.style.display = 'none';
                dateFinInput.value = dateDebutInput.value;
            } else if (selectedType === 'hebdomadaire' || selectedType === 'mensuel') {
                dateFinContainer.style.display = 'none';
            } else {
                dateFinContainer.style.display = 'block';
            }
        }
        
        // Initialiser l'affichage
        updateDateFields();
        
        // Mettre à jour l'affichage quand le type de rapport change
        typeRapportRadios.forEach(radio => {
            radio.addEventListener('change', updateDateFields);
        });
        
        // Mettre à jour la date de fin quand la date de début change (pour le type journalier)
        dateDebutInput.addEventListener('change', function() {
            const selectedType = document.querySelector('input[name="type_rapport"]:checked').value;
            if (selectedType === 'journalier') {
                dateFinInput.value = dateDebutInput.value;
            }
        });
    });
</script>
@endpush
@endsection
