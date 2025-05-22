@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Ajouter un employeur')

@push('styles')
<style>
    .form-input-transition {
        transition: all 0.2s ease-in-out;
    }
    .form-input-transition:focus {
        transform: scale(1.01);
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-3">
            <h1 class="text-3xl font-bold text-gray-800">Ajouter un employeur</h1>
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">Nouveau</span>
        </div>
        <a href="{{ route('admin.employeurs.index') }}" class="flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form action="{{ route('admin.employeurs.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Informations personnelles -->
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <h2 class="text-xl font-bold text-gray-800">Informations personnelles</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label for="nom" class="text-sm font-medium text-gray-700">Nom *</label>
                            <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('nom')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="prenom" class="text-sm font-medium text-gray-700">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('prenom')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="email" class="text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('email')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="telephone" class="text-sm font-medium text-gray-700">Téléphone *</label>
                            <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('telephone')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="date_naissance" class="text-sm font-medium text-gray-700">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('date_naissance')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="col-span-2">
                            <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Photo</label>
                            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="photo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Télécharger une photo</span>
                                            <input id="photo" name="photo" type="file" class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG jusqu'à 10MB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informations professionnelles -->
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-100">
                    <div class="flex items-center mb-6">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <h2 class="text-xl font-bold text-gray-800">Informations professionnelles</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label for="entreprise_id" class="text-sm font-medium text-gray-700">Entreprise *</label>
                            <select id="entreprise_id" name="entreprise_id" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                                <option value="">Sélectionner une entreprise</option>
                                @foreach($entreprises as $entreprise)
                                    <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                        {{ $entreprise->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('entreprise_id')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="departement_id" class="text-sm font-medium text-gray-700">Département *</label>
                            <select id="departement_id" name="departement_id" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                                <option value="">Sélectionner un département</option>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}" {{ old('departement_id') == $departement->id ? 'selected' : '' }}>
                                        {{ $departement->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('departement_id')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="poste" class="text-sm font-medium text-gray-700">Poste *</label>
                            <input type="text" id="poste" name="poste" value="{{ old('poste') }}" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('poste')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="type_contrat" class="text-sm font-medium text-gray-700">Type de contrat *</label>
                            <select id="type_contrat" name="type_contrat" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                                <option value="">Sélectionner un type</option>
                                @foreach($typesContrat as $key => $label)
                                    <option value="{{ $key }}" {{ old('type_contrat') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_contrat')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="date_embauche" class="text-sm font-medium text-gray-700">Date d'embauche *</label>
                            <input type="date" id="date_embauche" name="date_embauche" value="{{ old('date_embauche') }}" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('date_embauche')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="statut" class="text-sm font-medium text-gray-700">Statut *</label>
                            <select id="statut" name="statut" required 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                                <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @error('statut')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Options avancées -->
            <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 mb-8">
                <div class="flex items-center mb-6">
                    <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <h2 class="text-xl font-bold text-gray-800">Options avancées</h2>
                </div>
                
                <div class="flex items-center mb-4 p-4 bg-white rounded-lg border border-gray-200">
                    <input type="checkbox" id="creer_compte" name="creer_compte" value="1" {{ old('creer_compte') ? 'checked' : '' }}
                        class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <label for="creer_compte" class="ml-3 text-sm font-medium text-gray-700">
                        Créer un compte utilisateur pour cet employeur
                    </label>
                </div>
                
                <div id="compte_options" class="pl-6 border-l-2 border-blue-200 mt-4 {{ old('creer_compte') ? '' : 'hidden' }} transition-all duration-300">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label for="password" class="text-sm font-medium text-gray-700">Mot de passe</label>
                            <input type="password" id="password" name="password" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                            @error('password')
                                <p class="flex items-center text-red-600 text-sm mt-1">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        
                        <div class="space-y-1">
                            <label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" 
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 form-input-transition">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.employeurs.index') }}" 
                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler
                </a>
                <button type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de l'affichage des options de compte utilisateur avec animation
        const creerCompteCheckbox = document.getElementById('creer_compte');
        const compteOptions = document.getElementById('compte_options');
        
        if (creerCompteCheckbox && compteOptions) {
            creerCompteCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    compteOptions.classList.remove('hidden');
                    compteOptions.classList.add('opacity-100');
                    compteOptions.classList.remove('opacity-0');
                } else {
                    compteOptions.classList.add('opacity-0');
                    compteOptions.classList.remove('opacity-100');
                    setTimeout(() => {
                        compteOptions.classList.add('hidden');
                    }, 300);
                }
            });
        }
        
        // Filtrage dynamique des départements avec retour visuel de chargement
        const entrepriseSelect = document.getElementById('entreprise_id');
        const departementSelect = document.getElementById('departement_id');
        
        if (entrepriseSelect && departementSelect) {
            entrepriseSelect.addEventListener('change', function() {
                const entrepriseId = this.value;
                
                // Afficher un indicateur de chargement
                departementSelect.disabled = true;
                departementSelect.classList.add('opacity-50');
                
                // Réinitialiser le select des départements
                departementSelect.innerHTML = '<option value="">Chargement des départements...</option>';
                
                if (entrepriseId) {
                    fetch(`/api/entreprises/${entrepriseId}/departements`)
                        .then(response => response.json())
                        .then(data => {
                            departementSelect.innerHTML = '<option value="">Sélectionner un département</option>';
                            data.forEach(departement => {
                                const option = document.createElement('option');
                                option.value = departement.id;
                                option.textContent = departement.nom;
                                departementSelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Erreur lors du chargement des départements:', error);
                            departementSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                        })
                        .finally(() => {
                            departementSelect.disabled = false;
                            departementSelect.classList.remove('opacity-50');
                        });
                }
            });
        }
        
        // Prévisualisation de la photo
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('img');
                        preview.src = e.target.result;
                        preview.classList.add('mt-2', 'rounded-lg', 'max-h-32', 'mx-auto');
                        
                        const container = photoInput.closest('div').querySelector('.space-y-1');
                        const existingPreview = container.querySelector('img');
                        if (existingPreview) {
                            existingPreview.src = e.target.result;
                        } else {
                            container.appendChild(preview);
                        }
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    });
</script>
@endpush
@endsection
