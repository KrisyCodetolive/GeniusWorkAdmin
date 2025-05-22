<x-filament-panels::page>
    <div class="mb-4">
        {{ $this->form }}
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <h2 class="text-xl font-bold mb-2">Carte des sites</h2>
        <div id="map" style="height: 600px; width: 100%; border-radius: 0.5rem;"></div>
    </div>

    <div class="bg-white rounded-xl shadow p-4">
        <h2 class="text-xl font-bold mb-2">Légende</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="flex items-center">
                <div class="w-6 h-6 flex items-center justify-center">
                    <i class="fa-solid fa-building text-green-600"></i>
                </div>
                <span class="ml-2">Sites actifs</span>
            </div>
            <div class="flex items-center">
                <div class="w-6 h-6 flex items-center justify-center">
                    <i class="fa-solid fa-building text-red-600"></i>
                </div>
                <span class="ml-2">Sites inactifs</span>
            </div>
            <div class="flex items-center">
                <div class="w-6 h-6 rounded-full border-2 border-blue-500 mr-2"></div>
                <span>Zone de geofencing</span>
            </div>
            <div class="flex items-center">
                <div class="w-6 h-6 flex items-center justify-center bg-blue-100 rounded-full text-xs font-bold">
                    <span>42</span>
                </div>
                <span class="ml-2">Cluster de sites</span>
            </div>
        </div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        
        <!-- MarkerCluster CSS et JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
        <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
        
        <!-- Font Awesome pour les icônes -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialiser la carte
                const map = L.map('map').setView([46.603354, 1.888334], 5); // Centre sur la France
                
                // Ajouter le fond de carte OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                // Récupérer les données des sites
                const sites = @json($this->getSites());
                
                // Créer un groupe de clusters
                const markers = L.markerClusterGroup({
                    showCoverageOnHover: true,
                    zoomToBoundsOnClick: true,
                    spiderfyOnMaxZoom: true,
                    removeOutsideVisibleBounds: true,
                    iconCreateFunction: function(cluster) {
                        const count = cluster.getChildCount();
                        let size = 'small';
                        
                        if (count > 50) {
                            size = 'large';
                        } else if (count > 20) {
                            size = 'medium';
                        }
                        
                        return L.divIcon({
                            html: `<div class="cluster-icon cluster-${size}">${count}</div>`,
                            className: 'custom-cluster-icon',
                            iconSize: L.point(40, 40)
                        });
                    }
                });
                
                // Ajouter un style personnalisé pour les clusters
                const style = document.createElement('style');
                style.textContent = `
                    .custom-cluster-icon {
                        background: none;
                        border: none;
                    }
                    .cluster-icon {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(59, 130, 246, 0.8);
                        color: white;
                        border-radius: 50%;
                        font-weight: bold;
                        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
                    }
                    .cluster-small {
                        font-size: 12px;
                    }
                    .cluster-medium {
                        font-size: 14px;
                    }
                    .cluster-large {
                        font-size: 16px;
                    }
                `;
                document.head.appendChild(style);
                
                // Créer des icônes personnalisées pour les marqueurs
                function createCustomIcon(color, icon) {
                    return L.divIcon({
                        html: `<i class="${icon}" style="color: ${color}; font-size: 24px; text-shadow: 2px 2px 3px rgba(0,0,0,0.3);"></i>`,
                        className: 'custom-div-icon',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                }
                
                // Ajouter les marqueurs pour chaque site
                sites.forEach(site => {
                    // Définir l'icône en fonction du statut
                    const iconColor = site.statut === 'actif' ? '#16a34a' : '#dc2626'; // green-600 ou red-600
                    const iconClass = 'fa-solid fa-building';
                    const icon = createCustomIcon(iconColor, iconClass);
                    
                    // Créer le marqueur avec l'icône personnalisée
                    const marker = L.marker([site.latitude, site.longitude], {
                        icon: icon,
                        title: site.nom
                    });
                    
                    // Ajouter le cercle de geofencing si activé
                    if (site.has_geofencing) {
                        const geofencingCircle = L.circle([site.latitude, site.longitude], {
                            radius: site.rayon,
                            color: '#3b82f6', // blue-500
                            fillColor: '#93c5fd', // blue-300
                            fillOpacity: 0.2,
                            weight: 2
                        }).addTo(map);
                        
                        // Ajouter un popup au cercle de geofencing
                        geofencingCircle.bindTooltip(`Zone de geofencing: ${site.rayon} mètres`);
                    }
                    
                    // Créer le contenu du popup avec plus de détails
                    const popupContent = `
                        <div class="p-3 max-w-xs">
                            <h3 class="text-lg font-bold text-gray-900">${site.nom}</h3>
                            <p class="text-sm text-gray-600 mb-2">${site.entreprise}</p>
                            
                            <div class="my-2 text-sm">
                                <p class="flex items-center"><i class="fa-solid fa-location-dot mr-2 text-gray-500"></i> ${site.adresse}</p>
                                <p class="flex items-center"><i class="fa-solid fa-city mr-2 text-gray-500"></i> ${site.ville}, ${site.pays}</p>
                                <p class="flex items-center">
                                    <i class="fa-solid fa-circle-${site.statut === 'actif' ? 'check text-green-500' : 'xmark text-red-500'} mr-2"></i>
                                    ${site.statut === 'actif' ? 'Actif' : 'Inactif'}
                                </p>
                                ${site.has_geofencing ? `
                                <p class="flex items-center">
                                    <i class="fa-solid fa-map-marker-alt mr-2 text-blue-500"></i>
                                    Geofencing: ${site.rayon} m
                                </p>` : ''}
                            </div>
                            
                            <div class="mt-3 pt-2 border-t border-gray-200">
                                <a href="${site.url}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                                    <i class="fa-solid fa-eye mr-1"></i> Voir les détails
                                </a>
                            </div>
                        </div>
                    `;
                    
                    // Ajouter le popup au marqueur
                    marker.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'site-popup'
                    });
                    
                    // Ajouter le marqueur au groupe de clusters
                    markers.addLayer(marker);
                });
                
                // Ajouter le groupe de clusters à la carte
                map.addLayer(markers);
                
                // Ajuster la vue pour montrer tous les marqueurs si des sites existent
                if (sites.length > 0) {
                    map.fitBounds(markers.getBounds(), { padding: [50, 50] });
                }
                
                // Ajouter des contrôles supplémentaires
                L.control.scale({ imperial: false }).addTo(map);
                
                // Mettre à jour la carte lorsque les filtres changent
                document.addEventListener('livewire:update', function() {
                    // Réinitialiser la carte après la mise à jour des données
                    setTimeout(() => {
                        map.invalidateSize();
                    }, 100);
                });
                
                // Ajouter un style personnalisé pour les popups
                const popupStyle = document.createElement('style');
                popupStyle.textContent = `
                    .site-popup .leaflet-popup-content-wrapper {
                        border-radius: 8px;
                    }
                    .site-popup .leaflet-popup-content {
                        margin: 0;
                        padding: 0;
                    }
                `;
                document.head.appendChild(popupStyle);
            });
        </script>
    @endpush
</x-filament-panels::page>
