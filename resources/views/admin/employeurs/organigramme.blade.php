@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Organigramme des employeurs')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Organigramme des employeurs</h1>
        <a href="{{ route('admin.employeurs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-left mr-2"></i> Retour
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Filtres</h2>
        </div>
        <div class="p-4">
            <form action="{{ route('admin.employeurs.organigramme') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="entreprise_id" class="block text-sm font-medium text-gray-700 mb-1">Entreprise</label>
                    <select id="entreprise_id" name="entreprise_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Toutes les entreprises</option>
                        @foreach($entreprises as $entreprise)
                            <option value="{{ $entreprise->id }}" {{ isset($filters['entreprise_id']) && $filters['entreprise_id'] == $entreprise->id ? 'selected' : '' }}>
                                {{ $entreprise->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="departement_id" class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                    <select id="departement_id" name="departement_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <option value="">Tous les départements</option>
                        @foreach($departements as $departement)
                            <option value="{{ $departement->id }}" {{ isset($filters['departement_id']) && $filters['departement_id'] == $departement->id ? 'selected' : '' }}>
                                {{ $departement->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                        <i class="fas fa-filter mr-2"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Options d'affichage -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Options d'affichage</h2>
        </div>
        <div class="p-4 flex flex-wrap gap-4">
            <div>
                <label for="zoom" class="block text-sm font-medium text-gray-700 mb-1">Zoom</label>
                <input type="range" id="zoom" min="0.5" max="2" step="0.1" value="1" class="w-full">
            </div>
            <div>
                <label for="layout" class="block text-sm font-medium text-gray-700 mb-1">Disposition</label>
                <select id="layout" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="vertical">Verticale</option>
                    <option value="horizontal">Horizontale</option>
                </select>
            </div>
            <div>
                <label for="show-details" class="block text-sm font-medium text-gray-700 mb-1">Détails</label>
                <div class="flex items-center">
                    <input type="checkbox" id="show-details" checked class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <label for="show-details" class="ml-2 text-sm text-gray-700">Afficher les détails</label>
                </div>
            </div>
            <div class="ml-auto">
                <button id="print-organigramme" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-print mr-2"></i> Imprimer
                </button>
                <button id="export-organigramme" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded ml-2">
                    <i class="fas fa-file-export mr-2"></i> Exporter
                </button>
            </div>
        </div>
    </div>

    <!-- Organigramme -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Organigramme</h2>
        </div>
        <div class="p-4">
            <div id="organigramme-container" class="overflow-auto" style="height: 600px;">
                <div id="organigramme" class="transform-origin-top-left"></div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .org-node {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background-color: white;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        width: 220px;
        padding: 1rem;
        transition: all 0.3s;
    }
    
    .org-node:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }
    
    .org-node.responsable {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }
    
    .org-node.inactif {
        opacity: 0.6;
    }
    
    .org-line {
        stroke: #cbd5e0;
        stroke-width: 1px;
    }
    
    .org-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        background-color: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.8.5/d3.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Données de l'organigramme
        const orgData = {!! json_encode($organigramme) !!};
        
        // Configuration
        let config = {
            zoom: 1,
            layout: 'vertical',
            showDetails: true
        };
        
        // Initialisation de l'organigramme
        renderOrganigramme();
        
        // Gestion des événements
        document.getElementById('zoom').addEventListener('input', function(e) {
            config.zoom = parseFloat(e.target.value);
            updateZoom();
        });
        
        document.getElementById('layout').addEventListener('change', function(e) {
            config.layout = e.target.value;
            renderOrganigramme();
        });
        
        document.getElementById('show-details').addEventListener('change', function(e) {
            config.showDetails = e.target.checked;
            updateDetails();
        });
        
        document.getElementById('print-organigramme').addEventListener('click', function() {
            window.print();
        });
        
        document.getElementById('export-organigramme').addEventListener('click', function() {
            // Logique d'export (SVG, PNG, etc.)
            const svgData = document.getElementById('organigramme').innerHTML;
            const svgBlob = new Blob([svgData], {type: 'image/svg+xml;charset=utf-8'});
            const svgUrl = URL.createObjectURL(svgBlob);
            
            const downloadLink = document.createElement('a');
            downloadLink.href = svgUrl;
            downloadLink.download = 'organigramme.svg';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        });
        
        // Filtrage dynamique des départements en fonction de l'entreprise sélectionnée
        const entrepriseSelect = document.getElementById('entreprise_id');
        const departementSelect = document.getElementById('departement_id');
        
        if (entrepriseSelect && departementSelect) {
            entrepriseSelect.addEventListener('change', function() {
                const entrepriseId = this.value;
                
                // Réinitialiser le select des départements
                departementSelect.innerHTML = '<option value="">Tous les départements</option>';
                
                if (entrepriseId) {
                    // Charger les départements de l'entreprise sélectionnée via AJAX
                    fetch(`/api/entreprises/${entrepriseId}/departements`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(departement => {
                                const option = document.createElement('option');
                                option.value = departement.id;
                                option.textContent = departement.nom;
                                departementSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Erreur lors du chargement des départements:', error));
                }
            });
        }
        
        // Fonction pour mettre à jour le zoom
        function updateZoom() {
            const orgElement = document.getElementById('organigramme');
            orgElement.style.transform = `scale(${config.zoom})`;
        }
        
        // Fonction pour mettre à jour l'affichage des détails
        function updateDetails() {
            const detailElements = document.querySelectorAll('.org-details');
            detailElements.forEach(el => {
                el.style.display = config.showDetails ? 'block' : 'none';
            });
        }
        
        // Fonction pour générer l'organigramme
        function renderOrganigramme() {
            const container = document.getElementById('organigramme');
            container.innerHTML = '';
            
            // Utiliser D3.js pour générer l'organigramme
            const width = 1200;
            const height = 800;
            
            const svg = d3.select('#organigramme')
                .append('svg')
                .attr('width', width)
                .attr('height', height);
                
            // Créer une hiérarchie à partir des données
            const root = d3.hierarchy(orgData);
            
            // Définir le layout de l'arbre
            const treeLayout = config.layout === 'vertical' 
                ? d3.tree().size([width - 100, height - 200])
                : d3.tree().size([height - 200, width - 100]);
                
            // Calculer les positions des nœuds
            treeLayout(root);
            
            // Créer les liens entre les nœuds
            const links = svg.append('g')
                .selectAll('path')
                .data(root.links())
                .enter()
                .append('path')
                .attr('class', 'org-line')
                .attr('d', d => {
                    if (config.layout === 'vertical') {
                        return `M${d.source.x},${d.source.y} C${d.source.x},${(d.source.y + d.target.y) / 2} ${d.target.x},${(d.source.y + d.target.y) / 2} ${d.target.x},${d.target.y}`;
                    } else {
                        return `M${d.source.y},${d.source.x} C${(d.source.y + d.target.y) / 2},${d.source.x} ${(d.source.y + d.target.y) / 2},${d.target.x} ${d.target.y},${d.target.x}`;
                    }
                });
                
            // Créer les nœuds
            const nodes = svg.append('g')
                .selectAll('foreignObject')
                .data(root.descendants())
                .enter()
                .append('foreignObject')
                .attr('x', d => config.layout === 'vertical' ? d.x - 110 : d.y - 110)
                .attr('y', d => config.layout === 'vertical' ? d.y - 60 : d.x - 60)
                .attr('width', 220)
                .attr('height', 150)
                .html(d => {
                    const employeur = d.data;
                    const nodeClass = `org-node ${employeur.responsable ? 'responsable' : ''} ${employeur.statut === 'inactif' ? 'inactif' : ''}`;
                    
                    let html = `<div class="${nodeClass}">`;
                    html += `<div class="flex items-center mb-2">`;
                    
                    if (employeur.photo) {
                        html += `<img src="${employeur.photo}" alt="${employeur.nom}" class="org-avatar">`;
                    } else {
                        html += `<div class="org-avatar"><i class="fas fa-user"></i></div>`;
                    }
                    
                    html += `<div class="ml-3">`;
                    html += `<div class="font-semibold text-sm">${employeur.nom} ${employeur.prenom}</div>`;
                    html += `<div class="text-xs text-gray-600">${employeur.poste}</div>`;
                    html += `</div>`;
                    html += `</div>`;
                    
                    if (config.showDetails) {
                        html += `<div class="org-details text-xs text-gray-600">`;
                        html += `<div class="flex justify-between mb-1"><span>Département:</span><span>${employeur.departement}</span></div>`;
                        html += `<div class="flex justify-between"><span>Ancienneté:</span><span>${employeur.anciennete} ans</span></div>`;
                        html += `</div>`;
                    }
                    
                    html += `<div class="mt-2 text-center">`;
                    html += `<a href="/admin/employeurs/${employeur.id}" class="text-blue-600 hover:text-blue-800 text-xs">Voir profil</a>`;
                    html += `</div>`;
                    
                    html += `</div>`;
                    return html;
                });
                
            // Appliquer le zoom initial
            updateZoom();
            
            // Mettre à jour l'affichage des détails
            updateDetails();
        }
    });
</script>
@endpush
@endsection
