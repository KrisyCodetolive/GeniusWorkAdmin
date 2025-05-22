@extends('layouts.app')

@section('title', 'Transfert d\'employés du département ' . $departement->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Transfert d'employés du département {{ $departement->nom }}</h1>
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
                <h3 class="text-lg font-medium text-green-700 mb-2">Filiale</h3>
                <p class="text-2xl font-bold text-green-800">{{ $departement->filiale->nom }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg shadow-sm">
                <h3 class="text-lg font-medium text-purple-700 mb-2">Nombre d'employés</h3>
                <p class="text-2xl font-bold text-purple-800">{{ count($employes) }}</p>
            </div>
        </div>
    </div>

    @if(count($employes) === 0)
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-8">
        <div class="p-6">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Information :</strong> Ce département ne contient aucun employé à transférer.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-700">Formulaire de transfert</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.departements.transfert', $departement) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="departement_cible_id" class="block text-sm font-medium text-gray-700 mb-2">Département cible</label>
                    <select id="departement_cible_id" name="departement_cible_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('departement_cible_id') border-red-500 @enderror" required>
                        <option value="">Sélectionnez un département</option>
                        @foreach($departements as $dept)
                            <option value="{{ $dept->id }}" {{ old('departement_cible_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->nom }} ({{ $dept->code }}) - {{ $dept->filiale->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('departement_cible_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employés à transférer</label>
                    
                    <div class="mb-4 flex justify-end">
                        <button type="button" id="selectAll" class="text-sm text-blue-600 hover:text-blue-800">Tout sélectionner</button>
                        <span class="mx-2 text-gray-400">|</span>
                        <button type="button" id="deselectAll" class="text-sm text-blue-600 hover:text-blue-800">Tout désélectionner</button>
                    </div>
                    
                    <div class="border border-gray-300 rounded-md p-4 max-h-96 overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($employes as $employe)
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="employe_{{ $employe->id }}" name="employe_ids[]" type="checkbox" value="{{ $employe->id }}" class="employe-checkbox focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ in_array($employe->id, old('employe_ids', [])) ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="employe_{{ $employe->id }}" class="font-medium text-gray-700">{{ $employe->nom }} {{ $employe->prenom }}</label>
                                        <p class="text-gray-500">{{ $employe->poste }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('employe_ids')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if(!$errors->has('employe_ids') && old('employe_ids') === null)
                        <p class="mt-2 text-sm text-gray-500">Veuillez sélectionner au moins un employé à transférer.</p>
                    @endif
                </div>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Note :</strong> Le transfert d'employés est une opération qui peut être annulée ultérieurement si nécessaire.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Transférer les employés sélectionnés
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllBtn = document.getElementById('selectAll');
        const deselectAllBtn = document.getElementById('deselectAll');
        const checkboxes = document.querySelectorAll('.employe-checkbox');
        
        if (selectAllBtn && deselectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                });
            });
            
            deselectAllBtn.addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
            });
        }
    });
</script>
@endsection
