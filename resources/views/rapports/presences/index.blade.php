@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800 mb-6">Génération de rapport de présences</h1>
                
                <form action="{{ route('rapports.presences.generer') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Type de rapport -->
                        <div class="col-span-1">
                            <label for="type_rapport" class="block text-sm font-medium text-gray-700 mb-1">Type de rapport</label>
                            <select id="type_rapport" name="type_rapport" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="journalier">Journalier (aujourd'hui)</option>
                                <option value="mensuel">Mensuel (mois en cours)</option>
                                <option value="personnalise">Période personnalisée</option>
                            </select>
                        </div>
                        
                        <!-- Site -->
                        <div class="col-span-1">
                            <label for="site_id" class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                            <select id="site_id" name="site_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">Tous les sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Dates -->
                        <div class="col-span-1" id="date_debut_container">
                            <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                            <input type="date" name="date_debut" id="date_debut" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ date('Y-m-d') }}">
                        </div>
                        
                        <div class="col-span-1" id="date_fin_container">
                            <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                            <input type="date" name="date_fin" id="date_fin" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
        const typeRapport = document.getElementById('type_rapport');
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');
        const dateDebutContainer = document.getElementById('date_debut_container');
        const dateFinContainer = document.getElementById('date_fin_container');
        
        function updateDateFields() {
            const today = new Date();
            
            switch(typeRapport.value) {
                case 'journalier':
                    // Aujourd'hui pour les deux dates
                    const todayStr = today.toISOString().split('T')[0];
                    dateDebut.value = todayStr;
                    dateFin.value = todayStr;
                    dateDebutContainer.classList.add('hidden');
                    dateFinContainer.classList.add('hidden');
                    break;
                    
                case 'mensuel':
                    // Premier jour du mois en cours à aujourd'hui
                    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    dateDebut.value = firstDayOfMonth.toISOString().split('T')[0];
                    dateFin.value = today.toISOString().split('T')[0];
                    dateDebutContainer.classList.add('hidden');
                    dateFinContainer.classList.add('hidden');
                    break;
                    
                case 'personnalise':
                    // Afficher les champs de date
                    dateDebutContainer.classList.remove('hidden');
                    dateFinContainer.classList.remove('hidden');
                    break;
            }
        }
        
        // Initialiser les dates
        updateDateFields();
        
        // Mettre à jour les dates lorsque le type de rapport change
        typeRapport.addEventListener('change', updateDateFields);
    });
</script>
@endpush
@endsection
