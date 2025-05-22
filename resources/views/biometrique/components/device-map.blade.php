@props(['appareils', 'height' => '400px'])

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="p-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Carte des appareils</h3>
            <div class="flex space-x-2">
                <button id="refresh-map" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                    Actualiser
                </button>
                <select id="map-filter" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="all">Tous les appareils</option>
                    <option value="actif">Actifs uniquement</option>
                    <option value="inactif">Inactifs uniquement</option>
                    <option value="maintenance">En maintenance</option>
                    <option value="erreur">En erreur</option>
                </select>
            </div>
        </div>
    </div>
    
    <div id="devices-map" style="height: {{ $height }};" class="w-full"></div>
    
    <div class="p-3 bg-gray-50 border-t border-gray-200">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex space-x-4">
                <div class="flex items-center">
                    <span class="h-3 w-3 bg-green-500 rounded-full mr-1"></span>
                    <span>Actif</span>
                </div>
                <div class="flex items-center">
                    <span class="h-3 w-3 bg-red-500 rounded-full mr-1"></span>
                    <span>Erreur</span>
                </div>
                <div class="flex items-center">
                    <span class="h-3 w-3 bg-yellow-500 rounded-full mr-1"></span>
                    <span>Maintenance</span>
                </div>
                <div class="flex items-center">
                    <span class="h-3 w-3 bg-gray-500 rounded-full mr-1"></span>
                    <span>Inactif</span>
                </div>
            </div>
            <span>Total: <span id="map-device-count">{{ count($appareils) }}</span> appareils</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la carte Leaflet
        var map = L.map('devices-map').setView([46.603354, 1.888334], 5); // Centre sur la France
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Ajouter les marqueurs pour chaque appareil
        var markers = {};
        var deviceLayers = {
            'actif': L.layerGroup(),
            'inactif': L.layerGroup(),
            'maintenance': L.layerGroup(),
            'erreur': L.layerGroup()
        };
        
        @foreach($appareils as $appareil)
            @if($appareil->site && $appareil->site->latitude && $appareil->site->longitude)
                var marker = L.marker([{{ $appareil->site->latitude }}, {{ $appareil->site->longitude }}], {
                    icon: L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div class="marker-pin bg-{{ $appareil->statut === 'actif' ? 'green' : ($appareil->statut === 'erreur' ? 'red' : ($appareil->statut === 'maintenance' ? 'yellow' : 'gray')) }}-500">
                                <span class="text-white text-xs font-bold">{{ substr($appareil->nom, 0, 1) }}</span>
                              </div>`,
                        iconSize: [30, 42],
                        iconAnchor: [15, 42]
                    })
                }).bindPopup(`
                    <div class="text-center">
                        <h3 class="font-semibold">{{ $appareil->nom }}</h3>
                        <p class="text-sm text-gray-600">{{ $appareil->modele }}</p>
                        <p class="text-xs text-gray-500">{{ $appareil->site->nom }}</p>
                        <div class="mt-2">
                            <a href="{{ route('biometrique.appareils.show', $appareil->id) }}" class="text-xs text-blue-600 hover:text-blue-800">Voir détails</a>
                        </div>
                    </div>
                `);
                
                markers['{{ $appareil->id }}'] = marker;
                deviceLayers['{{ $appareil->statut }}'].addLayer(marker);
            @endif
        @endforeach
        
        // Ajouter tous les groupes de calques à la carte
        Object.values(deviceLayers).forEach(function(layer) {
            map.addLayer(layer);
        });
        
        // Filtrer les appareils sur la carte
        document.getElementById('map-filter').addEventListener('change', function() {
            var filter = this.value;
            
            Object.values(deviceLayers).forEach(function(layer) {
                map.removeLayer(layer);
            });
            
            if (filter === 'all') {
                Object.values(deviceLayers).forEach(function(layer) {
                    map.addLayer(layer);
                });
            } else {
                map.addLayer(deviceLayers[filter]);
            }
            
            // Mettre à jour le compteur
            var visibleCount = 0;
            if (filter === 'all') {
                visibleCount = Object.values(markers).length;
            } else {
                visibleCount = deviceLayers[filter].getLayers().length;
            }
            document.getElementById('map-device-count').textContent = visibleCount;
        });
        
        // Actualiser la carte
        document.getElementById('refresh-map').addEventListener('click', function() {
            // Ici, vous pourriez faire une requête AJAX pour obtenir les dernières données
            // Pour cet exemple, nous simulons juste un rechargement
            this.classList.add('animate-spin');
            setTimeout(() => {
                this.classList.remove('animate-spin');
            }, 1000);
        });
    });
</script>

<style>
    .marker-pin {
        width: 30px;
        height: 30px;
        border-radius: 50% 50% 50% 0;
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin: -15px 0 0 -15px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .marker-pin span {
        transform: rotate(45deg);
    }
</style>
@endpush
