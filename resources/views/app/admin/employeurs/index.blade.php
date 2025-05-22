@extends('app.entreprise.dashboard.entreprise')

@section('title', 'Gestion des employeurs')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i data-lucide="users" class="h-6 w-6 mr-2 text-blue-600"></i>
            Gestion des employeurs
        </h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.employeurs.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                <i data-lucide="plus" class="h-4 w-4 mr-1.5"></i> Ajouter un employeur
            </a>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg shadow-sm transition-all duration-200 flex items-center">
                    <i data-lucide="more-horizontal" class="h-4 w-4 mr-1.5"></i> Actions
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg z-10 border border-gray-100 overflow-hidden">
                    <div class="py-1">
                        <a href="{{ route('admin.employeurs.statistiques') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <i data-lucide="bar-chart-2" class="h-4 w-4 mr-3 text-blue-500"></i> Statistiques avancées
                        </a>
                        <a href="{{ route('admin.employeurs.organigramme') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <i data-lucide="git-branch" class="h-4 w-4 mr-3 text-indigo-500"></i> Organigramme
                        </a>
                        <hr class="my-1 border-gray-100">
                        <a href="#" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">
                            <i data-lucide="file-text" class="h-4 w-4 mr-3 text-green-500"></i> Exporter en Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <x-data.card hover="true" class="p-0 border border-gray-100 rounded-xl overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-blue-200 group">
            <div class="flex items-center py-3 px-4">
                <div class="p-2 rounded-full bg-blue-50 text-blue-600 transition-all duration-300 group-hover:bg-blue-100 group-hover:scale-110">
                    <i data-lucide="users" class="h-5 w-5"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500">Total employeurs</p>
                    <div class="flex items-baseline">
                        <p class="text-lg font-bold text-gray-800 transition-all duration-300 group-hover:text-blue-600">{{ $statistiques['total'] }}</p>
                        <p class="ml-1 text-xs text-gray-500">employés</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 h-1"></div>
        </x-data.card>
        
        <x-data.card hover="true" class="p-0 border border-gray-100 rounded-xl overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-green-200 group">
            <div class="flex items-center py-3 px-4">
                <div class="p-2 rounded-full bg-green-50 text-green-600 transition-all duration-300 group-hover:bg-green-100 group-hover:scale-110">
                    <i data-lucide="user-check" class="h-5 w-5"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500">Employeurs actifs</p>
                    <div class="flex items-baseline">
                        <p class="text-lg font-bold text-gray-800 transition-all duration-300 group-hover:text-green-600">{{ $statistiques['actifs'] }}</p>
                        <p class="ml-1 text-xs text-gray-500">employés</p>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-3 pt-0">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-medium text-green-700">Taux d'activité</span>
                    <span class="text-xs font-bold text-green-700">{{ $statistiques['pourcentage_actifs'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ $statistiques['pourcentage_actifs'] }}%"></div>
                </div>
            </div>
        </x-data.card>
        
        <x-data.card hover="true" class="p-0 border border-gray-100 rounded-xl overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-red-200 group">
            <div class="flex items-center py-3 px-4">
                <div class="p-2 rounded-full bg-red-50 text-red-600 transition-all duration-300 group-hover:bg-red-100 group-hover:scale-110">
                    <i data-lucide="user-x" class="h-5 w-5"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500">Employeurs inactifs</p>
                    <div class="flex items-baseline">
                        <p class="text-lg font-bold text-gray-800 transition-all duration-300 group-hover:text-red-600">{{ $statistiques['inactifs'] }}</p>
                        <p class="ml-1 text-xs text-gray-500">employés</p>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-3 pt-0">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-medium text-red-700">Taux d'inactivité</span>
                    <span class="text-xs font-bold text-red-700">{{ 100 - $statistiques['pourcentage_actifs'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-red-400 to-red-600 h-1.5 rounded-full transition-all duration-500" style="width: {{ 100 - $statistiques['pourcentage_actifs'] }}%"></div>
                </div>
            </div>
        </x-data.card>
        
        <x-data.card hover="true" class="p-0 border border-gray-100 rounded-xl overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-purple-200 group">
            <div class="flex items-center py-3 px-4">
                <div class="p-2 rounded-full bg-purple-50 text-purple-600 transition-all duration-300 group-hover:bg-purple-100 group-hover:scale-110">
                    <i data-lucide="clock" class="h-5 w-5"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500">Ancienneté moyenne</p>
                    <div class="flex items-baseline">
                        <p class="text-lg font-bold text-gray-800 transition-all duration-300 group-hover:text-purple-600">{{ $statistiques['anciennete_moyenne'] }}</p>
                        <p class="ml-1 text-xs text-gray-500">ans</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-r from-purple-50 to-purple-100 h-1"></div>
        </x-data.card>
    </div>

    <!-- Filtres -->
    <x-ui.filter-bar 
        route="{{ route('admin.employeurs.index') }}" 
        method="GET"
        :filters="$filters ?? []"
        class="mb-8"
    >
        <x-ui.filter-select 
            name="entreprise_id"
            label="Entreprise"
            icon="building"
            placeholder="Toutes les entreprises"
            :options="$entreprises->pluck('nom', 'id')->toArray()"
            :selected="$filters['entreprise_id'] ?? ''"
        />
        
        <x-ui.filter-select 
            name="departement_id"
            label="Département"
            icon="briefcase"
            placeholder="Tous les départements"
            :options="$departements->pluck('nom', 'id')->toArray()"
            :selected="$filters['departement_id'] ?? ''"
        />
        
        <x-ui.filter-select 
            name="statut"
            label="Statut"
            icon="user-check"
            placeholder="Tous les statuts"
            :options="['actif' => 'Actif', 'inactif' => 'Inactif']"
            :selected="$filters['statut'] ?? ''"
        />
        
        <x-ui.filter-select 
            name="type_contrat"
            label="Type de contrat"
            icon="file-text"
            placeholder="Tous les types"
            :options="$typesContrat"
            :selected="$filters['type_contrat'] ?? ''"
        />
        
        <x-ui.filter-search 
            name="search"
            label="Recherche"
            placeholder="Nom, email, matricule..."
            :value="$filters['search'] ?? ''"
            class="md:col-span-2 lg:col-span-4"
        />
    </x-ui.filter-bar>

    <!-- Liste des employeurs -->
    <x-data.card title="Liste des employeurs" class="border border-gray-100 rounded-xl shadow-sm">
        @php
            // Préparation des données pour le data-table
            $columns = [
                'employe' => 'Nom',
                'code' => 'Code / Matricule',
                'departement' => 'Département',
                'poste' => 'Poste',
                'type_contrat' => 'Type contrat',
                'salaire' => 'Salaire',
                'statut' => 'Statut'
            ];
            
            $tableData = [];
            foreach($employeurs as $employeur) {
                // Formater les données pour chaque colonne
                $nomComplet = $employeur->getNomComplet();
                $email = $employeur->email;
                $photo = $employeur->photo ? Storage::url($employeur->photo) : null;
                
                $employe = view('app.admin.employeurs._partials.employe-cell', [
                    'nom' => $nomComplet,
                    'email' => $email,
                    'photo' => $photo
                ])->render();
                
                $code = view('app.admin.employeurs._partials.code-cell', [
                    'code' => $employeur->code_employe,
                    'matricule' => $employeur->matricule
                ])->render();
                
                $departement = view('app.admin.employeurs._partials.departement-cell', [
                    'departement' => $employeur->departement->nom ?? 'Non défini',
                    'entreprise' => $employeur->entreprise->nom ?? 'Non défini'
                ])->render();
                
                $poste = $employeur->poste ?: 'Non défini';
                
                $typeContrat = view('app.admin.employeurs._partials.contrat-cell', [
                    'type' => $employeur->type_contrat ?: 'Non défini',
                    'date_debut' => $employeur->date_embauche ? date('d/m/Y', strtotime($employeur->date_embauche)) : null,
                    'date_fin' => $employeur->date_fin_contrat ? date('d/m/Y', strtotime($employeur->date_fin_contrat)) : null
                ])->render();
                
                $salaire = view('app.admin.employeurs._partials.salaire-cell', [
                    'montant' => number_format($employeur->salaire ?: 0, 0, ',', ' '),
                    'devise' => 'FCFA',
                    'frequence' => $employeur->frequence_paiement ?: 'Mensuel'
                ])->render();
                
                $statut = view('app.admin.employeurs._partials.badge-cell', [
                    'text' => $employeur->statut == 'actif' ? 'Actif' : 'Inactif',
                    'color' => $employeur->statut == 'actif' ? 'green' : 'red'
                ])->render();
                
                $tableData[] = [
                    'id' => $employeur->id,
                    'employe' => $employe,
                    'code' => $code,
                    'departement' => $departement,
                    'poste' => $poste,
                    'type_contrat' => $typeContrat,
                    'salaire' => $salaire,
                    'statut' => $statut
                ];
            }
        @endphp
        
        <x-data.data-table
            :columns="$columns"
            :data="$tableData"
            :searchable="true"
            :sortable="true"
            :pagination="true"
            :per-page="10"
            :bordered="true"
            :hover="true"
            :striped="true"
            empty-message="Aucun employeur trouvé."
            id="employeurs-table"
        >
            @foreach($tableData as $row)
                <div class="flex items-center space-x-2">
                    <x-ui.tooltip text="Voir les détails" position="top">
                        <a href="{{ route('admin.employeurs.show', $row['id']) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 p-1.5 rounded-lg transition-colors duration-200">
                            <i data-lucide="eye" class="h-4 w-4"></i>
                        </a>
                    </x-ui.tooltip>
                    
                    <x-ui.tooltip text="Modifier" position="top">
                        <a href="{{ route('admin.employeurs.edit', $row['id']) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 p-1.5 rounded-lg transition-colors duration-200">
                            <i data-lucide="edit" class="h-4 w-4"></i>
                        </a>
                    </x-ui.tooltip>
                    
                    <x-ui.tooltip text="Supprimer" position="top">
                        <form action="{{ route('admin.employeurs.destroy', $row['id']) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet employeur ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 p-1.5 rounded-lg transition-colors duration-200">
                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                            </button>
                        </form>
                    </x-ui.tooltip>
                </div>
            @endforeach
        </x-data.data-table>
    </x-data.card>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // S'assurer que Alpine.js est chargé et initialisé
        if (typeof Alpine === 'undefined') {
            console.warn('Alpine.js n\'est pas chargé. Chargement depuis CDN...');
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js';
            script.defer = true;
            document.head.appendChild(script);
            
            script.onload = function() {
                console.log('Alpine.js chargé avec succès. Initialisation...');
                if (typeof Alpine !== 'undefined') {
                    Alpine.start();
                }
                initializeComponents();
            };
        } else {
            initializeComponents();
        }
        
        function initializeComponents() {
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
            
            // Initialiser les icônes Lucide pour les nouveaux composants
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    });
</script>
@endpush
@endsection
