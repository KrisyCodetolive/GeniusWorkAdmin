@extends('layouts.app')

@section('title', 'Déplacement du département ' . $departement->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Déplacement du département {{ $departement->nom }}</h1>
        <a href="{{ route('admin.departements.show', $departement) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Informations du département</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-blue-700 mb-2">Code</h3>
                <p class="text-2xl font-bold text-blue-800">{{ $departement->code }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-green-700 mb-2">Filiale actuelle</h3>
                <p class="text-2xl font-bold text-green-800">{{ $departement->filiale->nom }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-purple-700 mb-2">Département parent actuel</h3>
                <p class="text-2xl font-bold text-purple-800">{{ $departement->departementParent ? $departement->departementParent->nom : 'Aucun (département racine)' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Informations importantes</h2>
        </div>
        <div class="p-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Attention :</strong> Le déplacement d'un département peut avoir des conséquences sur sa structure hiérarchique et ses codes.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-700 mb-2">Conséquences du déplacement :</h3>
                <ul class="list-disc pl-5 text-gray-600 space-y-1">
                    <li>Le code du département sera mis à jour en fonction de sa nouvelle position</li>
                    <li>Les codes de tous les sous-départements seront également mis à jour</li>
                    <li>Le niveau hiérarchique du département et de ses sous-départements sera recalculé</li>
                    <li>Les employés resteront attachés au département</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Formulaire de déplacement</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.departements.deplacement', $departement) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="filiale_id" class="block text-sm font-medium text-gray-700 mb-2">Nouvelle filiale</label>
                    <select id="filiale_id" name="filiale_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('filiale_id') border-red-500 @enderror" required>
                        @foreach($filiales as $filiale)
                            <option value="{{ $filiale->id }}" {{ old('filiale_id', $departement->filiale_id) == $filiale->id ? 'selected' : '' }}>
                                {{ $filiale->nom }} ({{ $filiale->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('filiale_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label for="departement_parent_id" class="block text-sm font-medium text-gray-700 mb-2">Nouveau département parent (optionnel)</label>
                    <select id="departement_parent_id" name="departement_parent_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('departement_parent_id') border-red-500 @enderror">
                        <option value="">Aucun (département racine)</option>
                    </select>
                    @error('departement_parent_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Laissez vide pour faire de ce département un département racine dans la filiale sélectionnée.</p>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Déplacer le département
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filialeSelect = document.getElementById('filiale_id');
        const parentSelect = document.getElementById('departement_parent_id');
        const currentDepartementId = {{ $departement->id }};
        
        // Données des départements par filiale
        const departementsByFiliale = {
            @foreach($filiales as $filiale)
                {{ $filiale->id }}: [
                    @foreach($filiale->departements as $dept)
                        @if($dept->id != $departement->id)
                        {
                            id: {{ $dept->id }},
                            nom: "{{ $dept->nom }}",
                            code: "{{ $dept->code }}"
                        },
                        @endif
                    @endforeach
                ],
            @endforeach
        };
        
        // Fonction pour mettre à jour les départements parents en fonction de la filiale sélectionnée
        function updateParentDepartements() {
            const filialeId = filialeSelect.value;
            
            // Réinitialiser le sélecteur de département parent
            parentSelect.innerHTML = '<option value="">Aucun (département racine)</option>';
            
            if (filialeId) {
                const departements = departementsByFiliale[filialeId] || [];
                
                // Ajouter les options de départements parents
                departements.forEach(dept => {
                    // Ne pas inclure le département actuel comme option de parent
                    if (dept.id != currentDepartementId) {
                        const option = document.createElement('option');
                        option.value = dept.id;
                        option.textContent = `${dept.nom} (${dept.code})`;
                        
                        // Sélectionner le parent actuel si on reste dans la même filiale
                        if (filialeId == {{ $departement->filiale_id }} && dept.id == {{ $departement->departement_parent_id ?? 'null' }}) {
                            option.selected = true;
                        }
                        
                        parentSelect.appendChild(option);
                    }
                });
            }
        }
        
        // Ajouter un écouteur d'événement
        filialeSelect.addEventListener('change', updateParentDepartements);
        
        // Initialiser les départements parents
        updateParentDepartements();
    });
</script>
@endsection
