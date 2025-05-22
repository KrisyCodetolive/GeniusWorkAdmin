# Vues et Composants du Module Biométrique

Cette documentation détaille les différentes vues et composants utilisés dans le module biométrique de GENIUS WORK.

## Structure des vues

Les vues du module biométrique sont organisées selon la structure suivante:

```
resources/
└── views/
    └── biometrie/
        ├── appareils/
        │   ├── index.blade.php
        │   ├── show.blade.php
        │   ├── create.blade.php
        │   ├── edit.blade.php
        │   └── components/
        │       ├── appareil-card.blade.php
        │       ├── appareil-status.blade.php
        │       └── appareil-logs.blade.php
        ├── dashboard/
        │   ├── index.blade.php
        │   └── components/
        │       ├── stats-card.blade.php
        │       ├── appareils-map.blade.php
        │       └── recent-logs.blade.php
        ├── logs/
        │   ├── index.blade.php
        │   ├── show.blade.php
        │   └── components/
        │       ├── log-table.blade.php
        │       └── log-details.blade.php
        ├── pointages/
        │   ├── index.blade.php
        │   ├── validation.blade.php
        │   └── components/
        │       ├── pointage-table.blade.php
        │       └── pointage-validation.blade.php
        ├── maintenance/
        │   ├── index.blade.php
        │   ├── planification.blade.php
        │   ├── rapport.blade.php
        │   └── components/
        │       ├── maintenance-calendar.blade.php
        │       └── diagnostic-card.blade.php
        └── layouts/
            ├── biometrie.blade.php
            └── components/
                ├── sidebar.blade.php
                └── header.blade.php
```

## Pages principales

### 1. Dashboard Biométrique

**Vue**: `biometrie/dashboard/index.blade.php`

Cette page présente une vue d'ensemble du système biométrique avec:

- Statistiques globales (nombre d'appareils, statuts, synchronisations)
- Carte interactive des appareils par site
- Graphiques d'activité récente
- Alertes et notifications

**Composants utilisés**:
- `stats-card`: Affiche les statistiques clés
- `appareils-map`: Carte interactive des appareils
- `recent-logs`: Tableau des logs récents

**Capture d'écran**:
![Dashboard Biométrique](../assets/images/biometrie-dashboard.png)

### 2. Gestion des Appareils

**Vue**: `biometrie/appareils/index.blade.php`

Cette page permet de gérer l'ensemble des appareils biométriques:

- Liste des appareils avec filtres (site, statut, modèle)
- Actions rapides (synchroniser, tester connexion, redémarrer)
- Ajout et modification d'appareils

**Sous-vues**:
- `show.blade.php`: Détails d'un appareil spécifique
- `create.blade.php`: Formulaire d'ajout d'appareil
- `edit.blade.php`: Formulaire de modification d'appareil

**Composants utilisés**:
- `appareil-card`: Carte d'information d'un appareil
- `appareil-status`: Indicateur visuel de statut
- `appareil-logs`: Affichage des logs récents d'un appareil

### 3. Logs des Appareils

**Vue**: `biometrie/logs/index.blade.php`

Cette page affiche les logs des appareils biométriques:

- Tableau paginé des logs avec filtres avancés
- Visualisation des détails d'un log
- Export des logs (CSV, Excel, PDF)

**Sous-vues**:
- `show.blade.php`: Détails d'un log spécifique

**Composants utilisés**:
- `log-table`: Tableau des logs avec pagination
- `log-details`: Affichage détaillé d'un log

### 4. Pointages Biométriques

**Vue**: `biometrie/pointages/index.blade.php`

Cette page permet de gérer les pointages générés par les appareils biométriques:

- Tableau des pointages avec filtres (utilisateur, date, site)
- Validation des pointages
- Correction des anomalies

**Sous-vues**:
- `validation.blade.php`: Interface de validation des pointages

**Composants utilisés**:
- `pointage-table`: Tableau des pointages
- `pointage-validation`: Interface de validation

### 5. Maintenance des Appareils

**Vue**: `biometrie/maintenance/index.blade.php`

Cette page permet de gérer la maintenance des appareils biométriques:

- Calendrier des maintenances planifiées
- Diagnostics des appareils
- Rapports de maintenance

**Sous-vues**:
- `planification.blade.php`: Planification des maintenances
- `rapport.blade.php`: Génération de rapports

**Composants utilisés**:
- `maintenance-calendar`: Calendrier des maintenances
- `diagnostic-card`: Carte de diagnostic d'appareil

## Composants réutilisables

### Composants d'appareil

#### appareil-card

Affiche les informations principales d'un appareil dans une carte.

```html
<x-biometrie.appareil-card :appareil="$appareil" />
```

**Propriétés**:
- `appareil`: Instance d'AppareilBiometrique
- `showActions` (optionnel): Affiche les boutons d'action
- `showLogs` (optionnel): Affiche les logs récents

#### appareil-status

Affiche le statut d'un appareil avec un indicateur visuel.

```html
<x-biometrie.appareil-status :status="$appareil->statut" />
```

**Propriétés**:
- `status`: Statut de l'appareil (actif, inactif, maintenance, erreur)

### Composants de dashboard

#### stats-card

Affiche une carte de statistiques avec titre, valeur et icône.

```html
<x-biometrie.stats-card 
    title="Appareils actifs" 
    :value="$statsService->getAppareilsActifsCount()" 
    icon="device-desktop" 
    color="green" 
/>
```

**Propriétés**:
- `title`: Titre de la statistique
- `value`: Valeur à afficher
- `icon`: Icône (utilise Heroicons)
- `color`: Couleur de la carte (green, blue, red, yellow)
- `trend` (optionnel): Tendance (up, down)
- `trendValue` (optionnel): Valeur de la tendance

#### appareils-map

Affiche une carte interactive des appareils par site.

```html
<x-biometrie.appareils-map :appareils="$appareils" :sites="$sites" />
```

**Propriétés**:
- `appareils`: Collection d'appareils
- `sites`: Collection de sites
- `height` (optionnel): Hauteur de la carte

### Composants de logs

#### log-table

Affiche un tableau paginé des logs avec filtres.

```html
<x-biometrie.log-table :logs="$logs" :filtres="$filtres" />
```

**Propriétés**:
- `logs`: Collection de logs
- `filtres` (optionnel): Filtres actifs
- `pagination` (optionnel): Nombre d'éléments par page

### Composants de pointage

#### pointage-table

Affiche un tableau des pointages avec filtres.

```html
<x-biometrie.pointage-table :pointages="$pointages" :filtres="$filtres" />
```

**Propriétés**:
- `pointages`: Collection de pointages
- `filtres` (optionnel): Filtres actifs
- `showValidation` (optionnel): Affiche les boutons de validation

### Composants de maintenance

#### maintenance-calendar

Affiche un calendrier des maintenances planifiées.

```html
<x-biometrie.maintenance-calendar :maintenances="$maintenances" />
```

**Propriétés**:
- `maintenances`: Collection de maintenances
- `editable` (optionnel): Permet l'édition des événements
- `view` (optionnel): Vue du calendrier (mois, semaine, jour)

#### diagnostic-card

Affiche les résultats de diagnostic d'un appareil.

```html
<x-biometrie.diagnostic-card :diagnostic="$diagnostic" />
```

**Propriétés**:
- `diagnostic`: Résultats du diagnostic
- `showDetails` (optionnel): Affiche les détails complets

## Layouts

### biometrie.blade.php

Layout principal pour les pages du module biométrique.

```html
@extends('layouts.app')

@section('sidebar')
    <x-biometrie.sidebar />
@endsection

@section('header')
    <x-biometrie.header :title="$title" />
@endsection

@section('content')
    {{ $slot }}
@endsection
```

### sidebar.blade.php

Barre latérale avec navigation du module biométrique.

```html
<nav>
    <ul>
        <li><a href="{{ route('biometrie.dashboard') }}">Dashboard</a></li>
        <li><a href="{{ route('biometrie.appareils.index') }}">Appareils</a></li>
        <li><a href="{{ route('biometrie.logs.index') }}">Logs</a></li>
        <li><a href="{{ route('biometrie.pointages.index') }}">Pointages</a></li>
        <li><a href="{{ route('biometrie.maintenance.index') }}">Maintenance</a></li>
    </ul>
</nav>
```

## Intégration JavaScript

### Composants interactifs

Le module utilise plusieurs composants JavaScript pour les fonctionnalités interactives:

1. **Carte des appareils**: Utilise Leaflet.js pour afficher la carte des sites et appareils
2. **Graphiques statistiques**: Utilise Chart.js pour les visualisations de données
3. **Calendrier de maintenance**: Utilise FullCalendar pour la planification
4. **Tableaux de données**: Utilise DataTables pour les tableaux paginés et filtrables

### Exemple d'intégration Leaflet

```javascript
// resources/js/biometrie/appareils-map.js
import L from 'leaflet';

document.addEventListener('DOMContentLoaded', function() {
    const mapElement = document.getElementById('appareils-map');
    if (!mapElement) return;
    
    const appareils = JSON.parse(mapElement.dataset.appareils);
    const sites = JSON.parse(mapElement.dataset.sites);
    
    const map = L.map('appareils-map').setView([46.227638, 2.213749], 5);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    sites.forEach(site => {
        const marker = L.marker([site.latitude, site.longitude]).addTo(map);
        
        const appareilsSite = appareils.filter(a => a.site_id === site.id);
        
        let popupContent = `
            <h3>${site.nom}</h3>
            <p>Appareils: ${appareilsSite.length}</p>
            <ul>
        `;
        
        appareilsSite.forEach(appareil => {
            popupContent += `
                <li>
                    ${appareil.nom} - 
                    <span class="status-${appareil.statut}">${appareil.statut}</span>
                </li>
            `;
        });
        
        popupContent += '</ul>';
        
        marker.bindPopup(popupContent);
    });
});
```

## Styles et thèmes

Le module biométrique utilise un ensemble de styles spécifiques:

```scss
// resources/sass/biometrie.scss

// Variables
$color-success: #10b981;
$color-warning: #f59e0b;
$color-danger: #ef4444;
$color-info: #3b82f6;

// Statuts d'appareil
.status {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    
    &-actif {
        background-color: rgba($color-success, 0.1);
        color: $color-success;
    }
    
    &-inactif {
        background-color: rgba($color-warning, 0.1);
        color: $color-warning;
    }
    
    &-maintenance {
        background-color: rgba($color-info, 0.1);
        color: $color-info;
    }
    
    &-erreur {
        background-color: rgba($color-danger, 0.1);
        color: $color-danger;
    }
}

// Cartes d'appareil
.appareil-card {
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    background-color: white;
    
    &__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    &__body {
        margin-bottom: 1rem;
    }
    
    &__footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }
}

// Tableaux de données
.data-table {
    width: 100%;
    border-collapse: collapse;
    
    th, td {
        padding: 0.75rem 1rem;
        text-align: left;
    }
    
    thead {
        background-color: #f9fafb;
        
        th {
            font-weight: 500;
            color: #374151;
        }
    }
    
    tbody {
        tr {
            border-bottom: 1px solid #e5e7eb;
            
            &:hover {
                background-color: #f9fafb;
            }
        }
    }
}
```

## Accessibilité

Les vues du module biométrique respectent les normes d'accessibilité WCAG 2.1:

- Utilisation appropriée des attributs ARIA
- Contraste suffisant pour les textes et éléments visuels
- Navigation au clavier possible
- Messages d'erreur explicites
- Textes alternatifs pour les images et icônes

## Responsive Design

Toutes les vues sont conçues pour s'adapter aux différentes tailles d'écran:

- Layout fluide basé sur une grille
- Points de rupture pour mobile, tablette et desktop
- Images et tableaux responsifs
- Menus adaptés aux appareils mobiles

## Exemples d'utilisation

### Affichage du dashboard

```php
// BiometrieDashboardController.php

public function index()
{
    $statsService = app(AppareilBiometriqueStatsService::class);
    
    return view('biometrie.dashboard.index', [
        'title' => 'Dashboard Biométrique',
        'appareils' => AppareilBiometrique::with('site')->get(),
        'sites' => Site::whereHas('appareilsBiometriques')->get(),
        'statsService' => $statsService,
        'logsRecents' => LogAppareilBiometrique::latest()->take(10)->get()
    ]);
}
```

### Affichage des appareils

```php
// AppareilBiometriqueController.php

public function index(Request $request)
{
    $query = AppareilBiometrique::with('site');
    
    // Appliquer les filtres
    if ($request->has('site')) {
        $query->where('site_id', $request->site);
    }
    
    if ($request->has('statut')) {
        $query->where('statut', $request->statut);
    }
    
    $appareils = $query->paginate(15);
    
    return view('biometrie.appareils.index', [
        'title' => 'Gestion des Appareils',
        'appareils' => $appareils,
        'sites' => Site::all(),
        'filtres' => $request->only(['site', 'statut'])
    ]);
}