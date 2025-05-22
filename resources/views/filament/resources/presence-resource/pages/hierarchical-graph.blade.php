<x-filament-panels::page>
    <div class="space-y-6">
        <!-- En-tête avec sélection de site -->
        <div class="p-4 bg-white rounded-lg shadow-md sm:p-6 border border-gray-100 hover:shadow-lg transition-shadow duration-300">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Présences en temps réel - Vue hiérarchique
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Visualisez la répartition des présences dans votre organisation</p>
                </div>
                
                <div class="w-full sm:w-auto flex space-x-2">
                    <button id="expand-all" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                        </svg>
                        Tout développer
                    </button>
                    <button id="collapse-all" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7" />
                        </svg>
                        Tout réduire
                    </button>
                </div>
            </div>
            
            <!-- Statistiques rapides -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-blue-800">Employés présents</h3>
                            <p class="text-2xl font-bold text-blue-900" id="present-count">{{ $presenceStats->currently_present ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-blue-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">En pause</h3>
                            <p class="text-2xl font-bold text-yellow-900" id="pause-count">{{ $presenceStats->on_break ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-yellow-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-green-800">Sites actifs</h3>
                            <p class="text-2xl font-bold text-green-900" id="sites-count">0</p>
                        </div>
                        <div class="p-3 bg-green-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-purple-800">Taux de présence</h3>
                            <p class="text-2xl font-bold text-purple-900" id="presence-rate">0%</p>
                        </div>
                        <div class="p-3 bg-purple-500 text-white rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Conteneur du graphe -->
        <div class="bg-white rounded-lg shadow-md p-4 overflow-hidden">
            <div id="hierarchical-graph" class="w-full" style="height: 700px;"></div>
        </div>
        
        <!-- Légende -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Légende</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                    <span class="text-sm">Entreprise</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
                    <span class="text-sm">Site</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-purple-500 mr-2"></div>
                    <span class="text-sm">Département</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-gray-500 mr-2"></div>
                    <span class="text-sm">Employé</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-green-300 mr-2"></div>
                    <span class="text-sm">Présent</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-yellow-300 mr-2"></div>
                    <span class="text-sm">En pause</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 rounded-full bg-red-300 mr-2"></div>
                    <span class="text-sm">Absent</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Inclusion des scripts D3.js -->
    @push('scripts')
        <script src="https://d3js.org/d3.v7.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Récupération des données hiérarchiques depuis le contrôleur
                const hierarchicalData = @json($hierarchicalData);
                
                // Configuration du graphe
                const width = document.getElementById('hierarchical-graph').offsetWidth;
                const height = 700;
                const margin = {top: 20, right: 120, bottom: 20, left: 120};
                
                // Création du SVG
                const svg = d3.select("#hierarchical-graph")
                    .append("svg")
                    .attr("width", width)
                    .attr("height", height)
                    .append("g")
                    .attr("transform", `translate(${margin.left},${margin.top})`);
                
                // Création de la hiérarchie
                const root = d3.hierarchy(hierarchicalData);
                
                // Configuration de l'arbre
                const treeLayout = d3.tree()
                    .size([height - margin.top - margin.bottom, width - margin.left - margin.right]);
                
                // Calcul des positions des nœuds
                treeLayout(root);
                
                // Couleurs selon le type de nœud
                const nodeColors = {
                    'entreprise': '#3b82f6', // blue-500
                    'site': '#10b981', // green-500
                    'departement': '#8b5cf6', // purple-500
                    'employee': '#6b7280' // gray-500
                };
                
                // Couleurs selon le statut de l'employé
                const statusColors = {
                    'present': '#86efac', // green-300
                    'en_pause': '#fcd34d', // yellow-300
                    'absent': '#fca5a5', // red-300
                    'unknown': '#d1d5db' // gray-300
                };
                
                // Création des liens entre les nœuds
                svg.selectAll(".link")
                    .data(root.links())
                    .join("path")
                    .attr("class", "link")
                    .attr("d", d3.linkHorizontal()
                        .x(d => d.y)
                        .y(d => d.x)
                    )
                    .attr("fill", "none")
                    .attr("stroke", "#d1d5db")
                    .attr("stroke-width", 1.5);
                
                // Création des nœuds
                const nodes = svg.selectAll(".node")
                    .data(root.descendants())
                    .join("g")
                    .attr("class", "node")
                    .attr("transform", d => `translate(${d.y},${d.x})`)
                    .attr("cursor", "pointer")
                    .on("click", function(event, d) {
                        // Toggle des enfants lors du clic
                        if (d.children) {
                            d._children = d.children;
                            d.children = null;
                        } else {
                            d.children = d._children;
                            d._children = null;
                        }
                        
                        // Mise à jour du graphe
                        update(root);
                    });
                
                // Ajout des cercles pour les nœuds
                nodes.append("circle")
                    .attr("r", d => {
                        if (d.data.type === 'employee') return 8;
                        return 12;
                    })
                    .attr("fill", d => {
                        if (d.data.type === 'employee') {
                            return statusColors[d.data.status] || statusColors.unknown;
                        }
                        return nodeColors[d.data.type] || "#6b7280";
                    })
                    .attr("stroke", "#fff")
                    .attr("stroke-width", 2);
                
                // Ajout des labels
                nodes.append("text")
                    .attr("dy", d => d.data.type === 'employee' ? -12 : 4)
                    .attr("x", d => d.children || d._children ? -13 : 13)
                    .attr("text-anchor", d => d.children || d._children ? "end" : "start")
                    .text(d => {
                        if (d.data.type === 'departement') {
                            return `${d.data.name} (${d.data.count})`;
                        }
                        return d.data.name;
                    })
                    .attr("font-size", d => {
                        if (d.data.type === 'employee') return "10px";
                        if (d.data.type === 'departement') return "12px";
                        return "14px";
                    })
                    .attr("fill", d => {
                        if (d.data.type === 'employee') return "#4b5563";
                        return "#1f2937";
                    });
                
                // Ajout de l'heure d'arrivée pour les employés
                nodes.filter(d => d.data.type === 'employee')
                    .append("text")
                    .attr("dy", 0)
                    .attr("x", 13)
                    .attr("text-anchor", "start")
                    .text(d => d.data.time || "")
                    .attr("font-size", "8px")
                    .attr("fill", "#6b7280");
                
                // Fonction pour mettre à jour le graphe
                function update(source) {
                    // Recalcul des positions
                    treeLayout(root);
                    
                    // Mise à jour des liens
                    const links = svg.selectAll(".link")
                        .data(root.links());
                    
                    links.transition()
                        .duration(750)
                        .attr("d", d3.linkHorizontal()
                            .x(d => d.y)
                            .y(d => d.x)
                        );
                    
                    links.enter()
                        .append("path")
                        .attr("class", "link")
                        .attr("d", d3.linkHorizontal()
                            .x(d => d.y)
                            .y(d => d.x)
                        )
                        .attr("fill", "none")
                        .attr("stroke", "#d1d5db")
                        .attr("stroke-width", 1.5);
                    
                    links.exit().remove();
                    
                    // Mise à jour des nœuds
                    const node = svg.selectAll(".node")
                        .data(root.descendants(), d => d.id || (d.id = ++i));
                    
                    node.transition()
                        .duration(750)
                        .attr("transform", d => `translate(${d.y},${d.x})`);
                    
                    node.exit().remove();
                    
                    // Mise à jour des statistiques
                    updateStats(root);
                }
                
                // Fonction pour mettre à jour les statistiques
                function updateStats(root) {
                    let presentCount = 0;
                    let pauseCount = 0;
                    let sitesCount = 0;
                    let totalEmployees = 0;
                    
                    // Parcours de l'arbre pour compter
                    root.descendants().forEach(d => {
                        if (d.data.type === 'employee') {
                            totalEmployees++;
                            if (d.data.status === 'present') presentCount++;
                            if (d.data.status === 'en_pause') pauseCount++;
                        }
                        if (d.data.type === 'site') sitesCount++;
                    });
                    
                    // Mise à jour des compteurs
                    document.getElementById('present-count').textContent = presentCount;
                    document.getElementById('pause-count').textContent = pauseCount;
                    document.getElementById('sites-count').textContent = sitesCount;
                    
                    // Calcul du taux de présence
                    const presenceRate = totalEmployees > 0 
                        ? Math.round((presentCount + pauseCount) / totalEmployees * 100) 
                        : 0;
                    document.getElementById('presence-rate').textContent = `${presenceRate}%`;
                }
                
                // Gestion des boutons d'expansion/réduction
                document.getElementById('expand-all').addEventListener('click', function() {
                    expandAll(root);
                    update(root);
                });
                
                document.getElementById('collapse-all').addEventListener('click', function() {
                    collapseAll(root);
                    update(root);
                });
                
                // Fonction pour développer tous les nœuds
                function expandAll(d) {
                    if (d._children) {
                        d.children = d._children;
                        d._children = null;
                    }
                    if (d.children) d.children.forEach(expandAll);
                }
                
                // Fonction pour réduire tous les nœuds
                function collapseAll(d) {
                    if (d.children) {
                        d._children = d.children;
                        d.children = null;
                    }
                    if (d._children) d._children.forEach(collapseAll);
                }
                
                // Initialisation des statistiques
                updateStats(root);
                
                // Polling pour rafraîchir les données
                setInterval(() => {
                    Livewire.dispatch('refresh-hierarchical-data');
                }, 30000); // Rafraîchir toutes les 30 secondes
            });
        </script>
    @endpush
</x-filament-panels::page>
