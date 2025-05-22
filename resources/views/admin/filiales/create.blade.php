@extends('layouts.app')

@section('title', 'Créer une filiale')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center mb-6">
        <a href="{{ route('admin.filiales.index') }}" class="text-blue-600 hover:text-blue-800 mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Créer une nouvelle filiale</h1>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <form action="{{ route('admin.filiales.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations générales -->
                <div class="col-span-2">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Informations générales</h2>
                </div>

                <!-- Nom -->
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom de la filiale <span class="text-red-600">*</span></label>
                    <input type="text" name="nom" id="nom" value="{{ old('nom') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('nom') border-red-500 @enderror">
                    @error('nom')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Code (laissez vide pour générer automatiquement)</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Adresse -->
                <div>
                    <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                    <input type="text" name="adresse" id="adresse" value="{{ old('adresse') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('adresse') border-red-500 @enderror">
                    @error('adresse')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ville -->
                <div>
                    <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                    <input type="text" name="ville" id="ville" value="{{ old('ville') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('ville') border-red-500 @enderror">
                    @error('ville')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pays -->
                <div>
                    <label for="pays" class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                    <input type="text" name="pays" id="pays" value="{{ old('pays') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('pays') border-red-500 @enderror">
                    @error('pays')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" id="telephone" value="{{ old('telephone') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('telephone') border-red-500 @enderror">
                    @error('telephone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Site web -->
                <div>
                    <label for="site_web" class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                    <input type="url" name="site_web" id="site_web" value="{{ old('site_web') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('site_web') border-red-500 @enderror">
                    @error('site_web')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Configuration -->
                <div class="col-span-2 mt-4">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Configuration</h2>
                </div>

                <!-- Responsable -->
                <div>
                    <label for="responsable_id" class="block text-sm font-medium text-gray-700 mb-1">Responsable principal</label>
                    <select name="responsable_id" id="responsable_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('responsable_id') border-red-500 @enderror">
                        <option value="">Sélectionner un responsable</option>
                        @foreach($responsables as $responsable)
                            <option value="{{ $responsable->id }}" {{ old('responsable_id') == $responsable->id ? 'selected' : '' }}>
                                {{ $responsable->nom }} {{ $responsable->prenom }}
                            </option>
                        @endforeach
                    </select>
                    @error('responsable_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Limite d'employés -->
                <div>
                    <label for="limite_employes" class="block text-sm font-medium text-gray-700 mb-1">Limite d'employés</label>
                    <input type="number" name="limite_employes" id="limite_employes" value="{{ old('limite_employes') }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('limite_employes') border-red-500 @enderror">
                    @error('limite_employes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Horaires -->
                <div>
                    <label for="horaire_debut" class="block text-sm font-medium text-gray-700 mb-1">Heure de début</label>
                    <input type="time" name="horaire_debut" id="horaire_debut" value="{{ old('horaire_debut') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('horaire_debut') border-red-500 @enderror">
                    @error('horaire_debut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="horaire_fin" class="block text-sm font-medium text-gray-700 mb-1">Heure de fin</label>
                    <input type="time" name="horaire_fin" id="horaire_fin" value="{{ old('horaire_fin') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('horaire_fin') border-red-500 @enderror">
                    @error('horaire_fin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jours de travail -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jours de travail</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                            $oldJours = old('jours_travail', []);
                        @endphp
                        
                        @foreach($jours as $jour)
                            <div class="flex items-center">
                                <input type="checkbox" name="jours_travail[]" id="jour_{{ $jour }}" value="{{ $jour }}" 
                                    {{ in_array($jour, $oldJours) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="jour_{{ $jour }}" class="ml-2 text-sm text-gray-700">{{ ucfirst($jour) }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('jours_travail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Méthodes de pointage -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Méthodes de pointage autorisées</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach($methodePointages as $methode)
                            <div class="flex items-center">
                                <input type="checkbox" name="methode_pointage_ids[]" id="methode_{{ $methode->id }}" value="{{ $methode->id }}" 
                                    {{ in_array($methode->id, old('methode_pointage_ids', [])) ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="methode_{{ $methode->id }}" class="ml-2 text-sm text-gray-700">{{ $methode->nom }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('methode_pointage_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Siège social -->
                <div class="col-span-2">
                    <div class="flex items-center">
                        <input type="checkbox" name="est_siege_social" id="est_siege_social" value="1" 
                            {{ old('est_siege_social') ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="est_siege_social" class="ml-2 text-sm text-gray-700">Définir comme siège social</label>
                    </div>
                    @error('est_siege_social')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="{{ route('admin.filiales.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg mr-2">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                    Créer la filiale
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
