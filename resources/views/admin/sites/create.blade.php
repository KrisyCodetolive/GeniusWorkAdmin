@extends('layouts.admin')

@section('title', 'Créer un nouveau site')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Créer un nouveau site</h1>
        <a href="{{ route('admin.sites.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Informations du site</h2>
        </div>
        <form action="{{ route('admin.sites.store') }}" method="POST" class="p-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations générales -->
                <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Informations générales</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="entreprise_id" class="block text-sm font-medium text-gray-700 mb-1">Entreprise <span class="text-red-600">*</span></label>
                            <select id="entreprise_id" name="entreprise_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('entreprise_id') border-red-500 @enderror">
                                <option value="">Sélectionner une entreprise</option>
                                @foreach($entreprises as $entreprise)
                                    <option value="{{ $entreprise->id }}" {{ old('entreprise_id') == $entreprise->id ? 'selected' : '' }}>
                                        {{ $entreprise->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('entreprise_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom du site <span class="text-red-600">*</span></label>
                            <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('nom') border-red-500 @enderror">
                            @error('nom')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="statut" class="block text-sm font-medium text-gray-700 mb-1">Statut <span class="text-red-600">*</span></label>
                            <select id="statut" name="statut" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('statut') border-red-500 @enderror">
                                <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="inactif" {{ old('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @error('statut')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea id="description" name="description" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Adresse -->
                <div class="col-span-2 md:col-span-1 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Adresse</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse <span class="text-red-600">*</span></label>
                            <input type="text" id="adresse" name="adresse" value="{{ old('adresse') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('adresse') border-red-500 @enderror">
                            @error('adresse')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="code_postal" class="block text-sm font-medium text-gray-700 mb-1">Code postal <span class="text-red-600">*</span></label>
                                <input type="text" id="code_postal" name="code_postal" value="{{ old('code_postal') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('code_postal') border-red-500 @enderror">
                                @error('code_postal')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville <span class="text-red-600">*</span></label>
                                <input type="text" id="ville" name="ville" value="{{ old('ville') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('ville') border-red-500 @enderror">
                                @error('ville')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="pays" class="block text-sm font-medium text-gray-700 mb-1">Pays <span class="text-red-600">*</span></label>
                            <input type="text" id="pays" name="pays" value="{{ old('pays', 'France') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('pays') border-red-500 @enderror">
                            @error('pays')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Coordonnées GPS et Geofencing -->
                <div class="col-span-2 md:col-span-1 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Coordonnées GPS et Geofencing</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                                <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('latitude') border-red-500 @enderror">
                                @error('latitude')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                                <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('longitude') border-red-500 @enderror">
                                @error('longitude')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="has_geofencing" name="has_geofencing" value="1" {{ old('has_geofencing') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="has_geofencing" class="ml-2 block text-sm text-gray-700">Activer le geofencing</label>
                        </div>

                        <div id="geofencing_options" class="{{ old('has_geofencing') ? '' : 'hidden' }}">
                            <label for="rayon_geofencing" class="block text-sm font-medium text-gray-700 mb-1">Rayon (en mètres)</label>
                            <input type="number" id="rayon_geofencing" name="rayon_geofencing" value="{{ old('rayon_geofencing', 100) }}" min="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('rayon_geofencing') border-red-500 @enderror">
                            @error('rayon_geofencing')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="button" id="locate_button" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded">
                                <i class="fas fa-map-marker-alt mr-2"></i> Localiser sur la carte
                            </button>
                        </div>

                        <div id="map_container" class="hidden mt-4">
                            <div id="map" class="h-64 w-full rounded-lg border border-gray-300"></div>
                        </div>
                    </div>
                </div>

                <!-- Informations de contact -->
                <div class="col-span-2 md:col-span-1 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Informations de contact</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="contact_nom" class="block text-sm font-medium text-gray-700 mb-1">Nom du contact</label>
                            <input type="text" id="contact_nom" name="contact_nom" value="{{ old('contact_nom') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contact_nom') border-red-500 @enderror">
                            @error('contact_nom')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Email du contact</label>
                            <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contact_email') border-red-500 @enderror">
                            @error('contact_email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone du contact</label>
                            <input type="text" id="contact_telephone" name="contact_telephone" value="{{ old('contact_telephone') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('contact_telephone') border-red-500 @enderror">
                            @error('contact_telephone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Horaires -->
                <div class="col-span-2 md:col-span-1 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Horaires d'ouverture</h3>
                    <p class="text-sm text-gray-500 mb-4">Définissez les horaires d'ouverture du site. Laissez vide pour les jours fermés.</p>
                    
                    <div class="space-y-4">
                        @php
                            $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                        @endphp

                        @foreach($jours as $jour)
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <div class="text-sm font-medium text-gray-700 capitalize">{{ $jour }}</div>
                                <div>
                                    <input type="time" name="horaires[{{ $jour }}][ouverture]" value="{{ old('horaires.'.$jour.'.ouverture') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                                <div>
                                    <input type="time" name="horaires[{{ $jour }}][fermeture]" value="{{ old('horaires.'.$jour.'.fermeture') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Employés associés -->
                <div class="col-span-2 bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-md font-semibold text-gray-700 mb-4">Employés associés</h3>
                    <p class="text-sm text-gray-500 mb-4">Sélectionnez les employés qui peuvent pointer sur ce site.</p>
                    
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="select_all_employes" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="select_all_employes" class="ml-2 block text-sm font-medium text-gray-700">Sélectionner tous les employés</label>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto p-2 border border-gray-200 rounded-md">
                        @foreach($employes as $employe)
                            <div class="flex items-center">
                                <input type="checkbox" id="employe_{{ $employe->id }}" name="employes[]" value="{{ $employe->id }}" 
                                    {{ (is_array(old('employes')) && in_array($employe->id, old('employes'))) ? 'checked' : '' }}
                                    class="employe-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <label for="employe_{{ $employe->id }}" class="ml-2 block text-sm text-gray-700">{{ $employe->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('admin.sites.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                    Annuler
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-save mr-2"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Geofencing toggle
        const hasGeofencingCheckbox = document.getElementById('has_geofencing');
        const geofencingOptions = document.getElementById('geofencing_options');
        
        hasGeofencingCheckbox.addEventListener('change', function() {
            geofencingOptions.classList.toggle('hidden', !this.checked);
        });
        
        // Select all employees
        const selectAllCheckbox = document.getElementById('select_all_employes');
        const employeCheckboxes = document.querySelectorAll('.employe-checkbox');
        
        selectAllCheckbox.addEventListener('change', function() {
            employeCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Map functionality (placeholder - would need to be implemented with a mapping library like Leaflet or Google Maps)
        const locateButton = document.getElementById('locate_button');
        const mapContainer = document.getElementById('map_container');
        
        locateButton.addEventListener('click', function() {
            mapContainer.classList.remove('hidden');
            // Here you would initialize your map
            alert('Cette fonctionnalité nécessite l\'intégration d\'une bibliothèque de cartographie comme Leaflet ou Google Maps.');
        });
    });
</script>
@endpush
@endsection
