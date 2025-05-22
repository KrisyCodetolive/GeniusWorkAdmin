# GENIUS WORK Component Library

Cette bibliothèque contient des composants Blade réutilisables pour l'application GENIUS WORK. Ces composants sont conçus pour être utilisés dans toutes les vues de l'application afin d'assurer une cohérence visuelle et fonctionnelle.

## Composants disponibles

### Mise en page
- `page-header` - En-tête de page avec titre, badge optionnel et boutons d'action
- `section-card` - Carte de section avec titre optionnel
- `tabs` - Système d'onglets pour organiser le contenu
- `modal` - Fenêtre modale réutilisable
- `confirmation-dialog` - Boîte de dialogue de confirmation pour les actions destructives
- `slide-over` - Panneau latéral coulissant pour afficher du contenu supplémentaire
- `sidebar` - Navigation latérale responsive
- `sidebar-item` - Élément de navigation pour la sidebar

### Formulaires
- `form-input` - Champ de saisie de texte
- `form-textarea` - Zone de texte multiligne
- `form-select` - Liste déroulante
- `form-checkbox` - Case à cocher
- `form-radio` - Bouton radio
- `form-file-upload` - Téléchargement de fichier avec prévisualisation
- `form-group` - Groupe de champs de formulaire
- `form-actions` - Actions de formulaire (boutons Enregistrer/Annuler)
- `department-select` - Sélecteur de département avec chargement dynamique
- `date-picker` - Sélecteur de date avec Flatpickr
- `rich-editor` - Éditeur de texte riche avec CKEditor
- `employee-select` - Sélecteur d'employés avec recherche et filtrage

### Affichage de données
- `data-table` - Tableau de données
- `stats-card` - Carte de statistiques
- `employee-card` - Carte d'employé
- `department-card` - Carte de département
- `card` - Conteneur de carte générique et réutilisable
- `badge` - Badge pour étiqueter des éléments
- `alert` - Message d'alerte
- `empty-state` - État vide pour les listes sans données
- `pagination` - Pagination pour les listes de données
- `timeline` - Affichage chronologique d'événements

### Interaction
- `button` - Bouton avec différents styles et tailles
- `search-input` - Champ de recherche
- `filter-dropdown` - Menu déroulant pour les filtres
- `tooltip` - Infobulles avec positionnement configurable
- `stepper` - Indicateur d'étapes pour les processus multi-étapes
- `progress` - Barres de progression personnalisables

## Utilisation

### Exemple de page avec composants

```blade
<x-app-layout>
    <x-page-header title="Gestion des employés" :badge="['text' => $employeesCount, 'color' => 'blue']">
        <x-button href="{{ route('entreprise.employe.create') }}" icon="plus">
            Ajouter un employé
        </x-button>
    </x-page-header>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <x-stats-card 
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' />"
            color="blue"
            label="Total des employés"
            value="{{ $stats['total'] }}"
        />
        
        <x-stats-card 
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' />"
            color="green"
            label="Employés actifs"
            value="{{ $stats['active'] }}"
            subvalue="{{ number_format($stats['activePercentage'], 1) }}% du total"
        />
        
        <x-stats-card 
            icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' />"
            color="yellow"
            label="Contrats à renouveler"
            value="{{ $stats['expiring'] }}"
            subvalue="Dans les 30 prochains jours"
        />
    </div>
    
    <x-section-card>
        <form action="{{ route('entreprise.employe.index') }}" method="GET" class="mb-6">
            <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                <div class="flex-1">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Rechercher un employé..." />
                </div>
                <div class="flex space-x-3">
                    <x-form-select name="departement_id" :options="$departements" selected="{{ request('departement_id') }}" placeholder="Tous les départements">
                        <option value="">Tous les départements</option>
                        @foreach($departements as $id => $nom)
                            <option value="{{ $id }}" {{ request('departement_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                        @endforeach
                    </x-form-select>
                    
                    <x-form-select name="status" :options="$statuses" selected="{{ request('status') }}" placeholder="Tous les statuts">
                        <option value="">Tous les statuts</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-form-select>
                    
                    <x-button type="submit">Filtrer</x-button>
                </div>
            </div>
        </form>
        
        @if($employees->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($employees as $employee)
                    <x-employee-card :employee="$employee" />
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $employees->links('app.components.pagination') }}
            </div>
        @else
            <x-empty-state 
                title="Aucun employé trouvé" 
                message="Aucun employé ne correspond à vos critères de recherche."
                icon="users"
                action="true"
                actionText="Ajouter un employé"
                actionUrl="{{ route('entreprise.employe.create') }}"
            />
        @endif
    </x-section-card>
</x-app-layout>
```

### Exemples d'utilisation des nouveaux composants

#### Stepper

```blade
<x-stepper :steps="[
    ['label' => 'Informations personnelles', 'status' => 'complete'],
    ['label' => 'Informations professionnelles', 'status' => 'current'],
    ['label' => 'Documents', 'status' => 'upcoming'],
    ['label' => 'Validation', 'status' => 'upcoming']
]" />

<!-- Version verticale -->
<x-stepper :steps="[
    'Informations personnelles',
    'Informations professionnelles',
    'Documents',
    'Validation'
]" :current-step="2" vertical="true" />
```

#### Timeline

```blade
<x-timeline :items="[
    [
        'title' => 'Création du compte',
        'date' => '01/01/2025',
        'icon' => 'user-plus',
        'color' => 'green',
        'content' => 'Compte créé par Admin'
    ],
    [
        'title' => 'Modification du profil',
        'date' => '15/01/2025',
        'icon' => 'edit',
        'color' => 'blue',
        'content' => 'Mise à jour des informations personnelles'
    ],
    [
        'title' => 'Changement de département',
        'date' => '01/02/2025',
        'icon' => 'git-branch',
        'color' => 'yellow',
        'content' => 'Transfert du département Marketing vers Ventes',
        'actions' => [
            ['label' => 'Voir détails', 'url' => '#', 'color' => 'blue']
        ]
    ]
]" />
```

#### Tooltip

```blade
<x-tooltip text="Informations supplémentaires" position="top">
    <button class="text-gray-500 hover:text-gray-700">
        <i data-lucide="info" class="h-5 w-5"></i>
    </button>
</x-tooltip>
```

#### Rich Editor

```blade
<x-rich-editor 
    label="Description du poste" 
    name="description" 
    :value="$job->description ?? ''" 
    toolbar="full" 
    required 
/>
```

#### Sidebar

```blade
<x-sidebar>
    <x-sidebar-item href="{{ route('dashboard') }}" icon="home" :active="request()->routeIs('dashboard')">
        Tableau de bord
    </x-sidebar-item>
    
    <x-sidebar-item href="{{ route('entreprise.employe.index') }}" icon="users" :active="request()->routeIs('entreprise.employe.*')" :badge="$newEmployeesCount">
        Employés
    </x-sidebar-item>
    
    <x-sidebar-item href="{{ route('entreprise.departements.index') }}" icon="briefcase" :active="request()->routeIs('entreprise.departements.*')">
        Départements
    </x-sidebar-item>
</x-sidebar>
```

## Bonnes pratiques

1. Utilisez les composants existants plutôt que de créer du HTML personnalisé
2. Respectez la cohérence visuelle en utilisant les classes Tailwind définies dans les composants
3. Étendez les composants existants plutôt que d'en créer de nouveaux si possible
4. Documentez tout nouveau composant dans ce README
5. Utilisez les icônes Lucide pour une cohérence visuelle (via l'attribut data-lucide)
6. Préférez l'utilisation d'Alpine.js pour l'interactivité côté client
7. Testez la réactivité des composants sur différentes tailles d'écran
