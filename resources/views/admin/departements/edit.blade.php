@extends('layouts.app')

@section('title', 'Modifier le département ' . $departement->nom)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.departements.show', $departement) }}" class="text-blue-600 hover:text-blue-800 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Modifier le département: {{ $departement->nom }}</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.departements.update', $departement) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations générales -->
                <div class="col-span-2">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Informations générales</h2>
                </div>

                <!-- Nom -->
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom du département <span class="text-red-600">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom', $departement->nom) }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('nom') border-red-500 @enderror">
                    @error('nom')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $departement->code) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Filiale -->
                <div>
                    <label for="filiale_id" class="block text-sm font-medium text-gray-700 mb-1">Filiale <span class="text-red-600">*</span></label>
                    <select name="filiale_id" id="filiale_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('filiale_id') border-red-500 @enderror">
                        <option value="">Sélectionner une filiale</option>
                        @foreach($filiales as $filiale)
                            <option value="{{ $filiale->id }}" {{ old('filiale_id', $departement->filiale_id) == $filiale->id ? 'selected' : '' }}>
                                {{ $filiale->nom }} ({{ $filiale->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('filiale_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Département parent -->
                <div>
                    <label for="departement_parent_id" class="block text-sm font-medium text-gray-700 mb-1">Département parent</label>
                    <select name="departement_parent_id" id="departement_parent_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('departement_parent_id') border-red-500 @enderror">
                        <option value="">Aucun (département racine)</option>
                        @foreach($departements as $dept)
                            @if($dept->id !== $departement->id && !in_array($dept->id, $sousDepartementIds))
                                <option value="{{ $dept->id }}" {{ old('departement_parent_id', $departement->departement_parent_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->nom }} ({{ $dept->filiale->nom }})
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('departement_parent_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Responsable -->
                <div>
                    <label for="responsable_id" class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                    <select name="responsable_id" id="responsable_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('responsable_id') border-red-500 @enderror">
                        <option value="">Sélectionner un responsable</option>
                        @foreach($responsables as $responsable)
                            <option value="{{ $responsable->id }}" {{ old('responsable_id', $departement->responsable_id) == $responsable->id ? 'selected' : '' }}>
                                {{ $responsable->nom }} {{ $responsable->prenom }}
                            </option>
                        @endforeach
                    </select>
                    @error('responsable_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Statut -->
                <div>
                    <label for="statut" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="statut" id="statut" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('statut') border-red-500 @enderror">
                        <option value="actif" {{ old('statut', $departement->statut) === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ old('statut', $departement->statut) === 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                    @error('statut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description', $departement->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Configuration -->
                <div class="col-span-2 mt-4">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Configuration</h2>
                </div>

                <!-- Budget -->
                <div>
                    <label for="budget" class="block text-sm font-medium text-gray-700 mb-1">Budget</label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">€</span>
                        </div>
                        <input type="number" name="budget" id="budget" value="{{ old('budget', $departement->configuration['budget'] ?? '') }}" min="0" step="0.01" class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('budget') border-red-500 @enderror">
                    </div>
                    @error('budget')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Limite d'employés -->
                <div>
                    <label for="limite_employes" class="block text-sm font-medium text-gray-700 mb-1">Limite d'employés</label>
                    <input type="number" name="limite_employes" id="limite_employes" value="{{ old('limite_employes', $departement->configuration['limite_employes'] ?? '') }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('limite_employes') border-red-500 @enderror">
                    @error('limite_employes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Objectifs -->
                <div class="col-span-2">
                    <label for="objectifs" class="block text-sm font-medium text-gray-700 mb-1">Objectifs</label>
                    <textarea name="objectifs" id="objectifs" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('objectifs') border-red-500 @enderror">{{ old('objectifs', $departement->configuration['objectifs'] ?? '') }}</textarea>
                    @error('objectifs')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="{{ route('admin.departements.show', $departement) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-2">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <h2 class="text-lg font-semibold text-red-700">Zone de danger</h2>
        </div>
        <div class="p-6">
            <form action="{{ route('admin.departements.destroy', $departement) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce département ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <p class="text-gray-700 mb-4">La suppression de ce département est irréversible. Tous les employés associés seront détachés de ce département.</p>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg">
                    Supprimer ce département
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
