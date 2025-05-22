@extends('layouts.admin')

@section('title', 'Carte des sites')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Carte des sites</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.sites.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Filtrer les sites</h2>
        </div>
        <div class="p-4">
            <form action="{{ route('admin.sites.carte') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-auto">
                    <label for="entreprise_id" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                    <select id="entreprise_id" name="entreprise_id" class="w-full md:w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Toutes les entreprises</option>
                        @foreach($entreprises as $entreprise)
                            <option value="{{ $entreprise->id }}" {{ $entrepriseId == $entreprise->id ? 'selected' : '' }}>
                                {{ $entreprise->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-filter mr-2"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Carte des sites</h2>
        </div>
        <div class="p-4">
            @if($sites->count() > 0)
                <div id="map" class="w-full h-96 rounded-lg border border-gray-300"></div>
                <div class="mt-4 text-sm text-gray-600">
                    <p><i class="fas fa-info-circle mr-1"></i> {{ $sites->count() }} site(s) affiché(s) sur la carte.</p>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Aucun site avec des coordonnées GPS n'a été trouvé. Veuillez ajouter des coordonnées GPS aux sites pour les voir sur la carte.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($sites->count() > 0)
            // Initialiser la carte
            const map = L.map('map').setView([46.603354, 1.888334], 5); // Vue centrée sur la France

            // Ajouter la couche de tuiles OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Ajouter les marqueurs pour chaque site
            const sites = @json($sites);
            const bounds = L.latLngBounds();
            
            sites.forEach(site => {
                if (site.latitude && site.longitude) {
                    const marker = L.marker([site.latitude, site.longitude]).addTo(map);
                    
                    // Ajouter une popup avec les informations du site
                    marker.bindPopup(`
                        <strong>${site.nom}</strong><br>
                        ${site.adresse}<br>
                        ${site.code_postal} ${site.ville}<br>
                        <a href="${route('admin.sites.show', site.id)}" class="text-blue-600 hover:underline">
                            Voir les détails
                        </a>
                    `);
                    
                    // Étendre les limites pour inclure ce marqueur
                    bounds.extend([site.latitude, site.longitude]);
                }
            });
            
            // Ajuster la vue pour montrer tous les marqueurs
            if (bounds.isValid()) {
                map.fitBounds(bounds);
            }
            
            // Ajouter le cercle de geofencing lorsqu'on clique sur un marqueur
            map.on('popupopen', function(e) {
                const marker = e.popup._source;
                const latlng = marker.getLatLng();
                const site = sites.find(s => s.latitude == latlng.lat && s.longitude == latlng.lng);
                
                // Supprimer les cercles existants
                map.eachLayer(layer => {
                    if (layer instanceof L.Circle) {
                        map.removeLayer(layer);
                    }
                });
                
                // Ajouter le cercle de geofencing si activé
                if (site.has_geofencing && site.rayon_geofencing > 0) {
                    L.circle([site.latitude, site.longitude], {
                        radius: site.rayon_geofencing,
                        color: '#3B82F6',
                        fillColor: '#93C5FD',
                        fillOpacity: 0.3
                    }).addTo(map);
                }
            });
        @endif
    });
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
@endpush

@push('head-scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endpush
@endsection
