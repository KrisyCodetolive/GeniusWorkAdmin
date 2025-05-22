<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
        <!-- Panneau de filtres et légende -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Filtres -->
            <div class="fi-card p-4 rounded-xl shadow-sm border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
                <h2 class="text-lg font-bold mb-3 fi-section-header-heading text-gray-950 dark:text-white flex items-center">
                    <x-heroicon-o-adjustments-horizontal class="w-5 h-5 mr-2 text-primary-500 dark:text-primary-400" />
                    Filtres
                </h2>
                <div class="space-y-4">
                    {{ $this->form }}
                </div>
            </div>

            <!-- Légende -->
            <div class="fi-card p-4 rounded-xl shadow-sm border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
                <h2 class="text-lg font-bold mb-3 fi-section-header-heading text-gray-950 dark:text-white flex items-center">
                    <x-heroicon-o-information-circle class="w-5 h-5 mr-2 text-primary-500 dark:text-primary-400" />
                    Légende
                </h2>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="fa-solid fa-building text-success-500 dark:text-success-400"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sites actifs</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="fa-solid fa-building text-danger-500 dark:text-danger-400"></i>
                        </div>
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sites inactifs</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full border-2 border-primary-500 dark:border-primary-400 mr-2"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Zone de geofencing</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 flex items-center justify-center bg-primary-100 dark:bg-primary-900 rounded-full text-xs font-bold text-primary-700 dark:text-primary-300">
                            <span>42</span>
                        </div>
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Cluster de sites</span>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="fi-card p-4 rounded-xl shadow-sm border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700">
                <h2 class="text-lg font-bold mb-3 fi-section-header-heading text-gray-950 dark:text-white flex items-center">
                    <x-heroicon-o-chart-bar class="w-5 h-5 mr-2 text-primary-500 dark:text-primary-400" />
                    Statistiques
                </h2>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Sites affichés:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ count($this->getSites()) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Avec geofencing:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ collect($this->getSites())->where('has_geofencing', true)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte principale -->
        <div class="lg:col-span-3">
            <div class="fi-card rounded-xl shadow-sm border border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-700 overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold fi-section-header-heading text-gray-950 dark:text-white flex items-center">
                        <x-heroicon-o-map class="w-6 h-6 mr-2 text-primary-500 dark:text-primary-400" />
                        Carte des sites
                    </h2>
                </div>
                <div id="map" style="height: 700px; width: 100%;"></div>
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
                // Détecter le mode sombre
                const isDarkMode = document.documentElement.classList.contains('dark');
                
                // Initialiser la carte avec le style adapté au mode
                const map = L.map('map').setView([46.603354, 1.888334], 5); // Centre sur la France
                
                // Ajouter le fond de carte adapté au mode
                if (isDarkMode) {
                    // Fond de carte sombre
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                        maxZoom: 19
                    }).addTo(map);
                } else {
                    // Fond de carte clair
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                }
                
                // Récupérer les données des sites
                const sites = @json($this->getSites());
                
                // Récupérer les couleurs du thème Filament
                const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-500') || '#3b82f6';
                const primaryColorLight = getComputedStyle(document.documentElement).getPropertyValue('--primary-300') || '#93c5fd';
                const successColor = getComputedStyle(document.documentElement).getPropertyValue('--success-500') || '#16a34a';
                const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--danger-500') || '#dc2626';
                
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
                        background-color: ${primaryColor}dd;
                        color: white;
                        border-radius: 50%;
                        font-weight: bold;
                        box-shadow: 0 0 0 4px ${primaryColor}33;
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
                    const iconColor = site.statut === 'actif' ? successColor : dangerColor;
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
                            color: primaryColor,
                            fillColor: primaryColorLight,
                            fillOpacity: 0.2,
                            weight: 2
                        }).addTo(map);
                        
                        // Ajouter un popup au cercle de geofencing
                        geofencingCircle.bindTooltip(`Zone de geofencing: ${site.rayon} mètres`);
                    }
                    
                    // Créer le contenu du popup avec plus de détails
                    const popupContent = `
                        <div class="p-3 max-w-xs">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">${site.nom}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">${site.entreprise}</p>
                            
                            <div class="my-2 text-sm">
                                <p class="flex items-center"><i class="fa-solid fa-location-dot mr-2 text-gray-500 dark:text-gray-400"></i> ${site.adresse || 'Non renseignée'}</p>
                                <p class="flex items-center"><i class="fa-solid fa-city mr-2 text-gray-500 dark:text-gray-400"></i> ${site.ville || 'Non renseignée'}, ${site.pays || 'Non renseigné'}</p>
                                <p class="flex items-center">
                                    <i class="fa-solid fa-circle-${site.statut === 'actif' ? 'check text-success-500 dark:text-success-400' : 'xmark text-danger-500 dark:text-danger-400'} mr-2"></i>
                                    ${site.statut === 'actif' ? 'Actif' : 'Inactif'}
                                </p>
                                ${site.has_geofencing ? `
                                <p class="flex items-center">
                                    <i class="fa-solid fa-map-marker-alt mr-2 text-primary-500 dark:text-primary-400"></i>
                                    Geofencing: ${site.rayon} m
                                </p>` : ''}
                            </div>
                            
                            <div class="mt-3 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <a href="${site.url}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors">
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
                
                // Détecter les changements de mode clair/sombre
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            const isDarkModeNow = document.documentElement.classList.contains('dark');
                            if (isDarkModeNow !== isDarkMode) {
                                // Recharger la page pour mettre à jour la carte
                                window.location.reload();
                            }
                        }
                    });
                });
                
                observer.observe(document.documentElement, { attributes: true });
            });
        </script>
    @endpush
</x-filament-panels::page>
