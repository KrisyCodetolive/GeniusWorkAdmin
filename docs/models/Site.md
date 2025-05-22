# Modèle Site

## Description générale

Le modèle `Site` représente les emplacements physiques associés à une entreprise dans le système GENIUS WORK. Il permet de gérer les différents lieux où les employés peuvent travailler et pointer leur présence, avec des fonctionnalités avancées comme le geofencing pour la validation automatique de présence.

## Attributs

| Attribut | Type | Description | Nullable |
|----------|------|-------------|----------|
| `id` | UUID | Identifiant unique du site | Non |
| `entreprise_id` | UUID | Identifiant de l'entreprise associée | Non |
| `nom` | string | Nom du site | Non |
| `description` | text | Description détaillée du site | Oui |
| `adresse` | string | Adresse physique du site | Non |
| `code_postal` | string | Code postal | Non |
| `ville` | string | Ville | Non |
| `pays` | string | Pays | Non |
| `latitude` | decimal | Coordonnée GPS latitude | Oui |
| `longitude` | decimal | Coordonnée GPS longitude | Oui |
| `has_geofencing` | boolean | Indique si le geofencing est activé | Non (défaut: false) |
| `rayon_geofencing` | integer | Rayon en mètres pour le geofencing | Oui |
| `statut` | enum | Statut du site ('actif', 'inactif') | Non (défaut: 'actif') |
| `contact_nom` | string | Nom de la personne à contacter sur le site | Oui |
| `contact_email` | string | Email du contact | Oui |
| `contact_telephone` | string | Téléphone du contact | Oui |
| `horaires` | json | Configuration des horaires d'ouverture | Oui |
| `created_at` | timestamp | Date de création | Auto |
| `updated_at` | timestamp | Date de dernière modification | Auto |
| `deleted_at` | timestamp | Date de suppression (soft delete) | Oui |

## Relations

| Relation | Type | Modèle | Description |
|----------|------|--------|-------------|
| `entreprise` | belongsTo | Entreprise | L'entreprise à laquelle appartient le site |
| `employes` | belongsToMany | User | Les employés associés au site |
| `pointages` | hasMany | Pointage | Les pointages effectués sur ce site |

## Scopes

| Scope | Description | Paramètres |
|-------|-------------|------------|
| `actif` | Filtre les sites actifs | Aucun |
| `parEntreprise` | Filtre les sites d'une entreprise spécifique | `$entrepriseId` (UUID) |
| `avecGeofencing` | Filtre les sites avec geofencing activé | Aucun |

## Méthodes

| Méthode | Description | Paramètres | Retour |
|---------|-------------|------------|--------|
| `estDansRayon` | Vérifie si des coordonnées GPS sont dans le rayon du site | `$latitude`, `$longitude` | boolean |
| `calculerDistance` | Calcule la distance entre le site et des coordonnées GPS | `$latitude`, `$longitude` | float (mètres) |
| `getHorairesAttribute` | Accesseur pour convertir les horaires JSON en tableau | Aucun | array |
| `setHorairesAttribute` | Mutateur pour convertir un tableau d'horaires en JSON | `$value` (array) | void |

## Format des horaires

Les horaires sont stockés au format JSON avec la structure suivante :

```json
{
  "lundi": { "ouverture": "09:00", "fermeture": "18:00" },
  "mardi": { "ouverture": "09:00", "fermeture": "18:00" },
  "mercredi": { "ouverture": "09:00", "fermeture": "18:00" },
  "jeudi": { "ouverture": "09:00", "fermeture": "18:00" },
  "vendredi": { "ouverture": "09:00", "fermeture": "18:00" },
  "samedi": { "ouverture": null, "fermeture": null },
  "dimanche": { "ouverture": null, "fermeture": null }
}
```

## Fonctionnalité de Geofencing

Le geofencing permet de vérifier automatiquement si un employé se trouve bien sur le site lors du pointage. Lorsque cette fonctionnalité est activée (`has_geofencing = true`), le système utilise :

1. Les coordonnées GPS du site (`latitude`, `longitude`)
2. Le rayon de détection (`rayon_geofencing`) en mètres
3. Les coordonnées GPS de l'employé au moment du pointage

La méthode `estDansRayon($latitude, $longitude)` calcule la distance entre les coordonnées fournies et celles du site, puis vérifie si cette distance est inférieure ou égale au rayon de geofencing configuré.

## Validation

Les règles de validation pour la création et la mise à jour d'un site incluent :

- `entreprise_id` : obligatoire, doit exister dans la table entreprises
- `nom` : obligatoire, chaîne de caractères, max 255 caractères
- `adresse` : obligatoire, chaîne de caractères, max 255 caractères
- `code_postal` : obligatoire, chaîne de caractères, max 20 caractères
- `ville` : obligatoire, chaîne de caractères, max 100 caractères
- `pays` : obligatoire, chaîne de caractères, max 100 caractères
- `latitude` : optionnel, numérique
- `longitude` : optionnel, numérique
- `rayon_geofencing` : optionnel, numérique, minimum 0
- `has_geofencing` : booléen
- `statut` : obligatoire, doit être 'actif' ou 'inactif'
- `contact_nom` : optionnel, chaîne de caractères, max 255 caractères
- `contact_email` : optionnel, email valide, max 255 caractères
- `contact_telephone` : optionnel, chaîne de caractères, max 20 caractères

## Exemple d'utilisation

```php
// Création d'un site
$site = Site::create([
    'entreprise_id' => $entreprise->id,
    'nom' => 'Siège Social',
    'description' => 'Siège principal de l\'entreprise',
    'adresse' => '123 Avenue Principale',
    'code_postal' => '75001',
    'ville' => 'Paris',
    'pays' => 'France',
    'latitude' => 48.8566,
    'longitude' => 2.3522,
    'has_geofencing' => true,
    'rayon_geofencing' => 100,
    'statut' => 'actif',
    'contact_nom' => 'Jean Dupont',
    'contact_email' => 'jean.dupont@example.com',
    'contact_telephone' => '01 23 45 67 89',
    'horaires' => [
        'lundi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
        'mardi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
        'mercredi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
        'jeudi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
        'vendredi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
        'samedi' => ['ouverture' => null, 'fermeture' => null],
        'dimanche' => ['ouverture' => null, 'fermeture' => null]
    ]
]);

// Association d'employés au site
$site->employes()->attach($employe->id);

// Vérification si un employé est dans le rayon du site
$estPresent = $site->estDansRayon($latitude, $longitude);

// Récupération des sites actifs d'une entreprise
$sitesActifs = Site::actif()->parEntreprise($entreprise->id)->get();

// Récupération des sites avec geofencing
$sitesAvecGeofencing = Site::avecGeofencing()->get();
```

## Interfaces utilisateur

Le module de gestion des sites comprend les vues suivantes :

1. **Liste des sites** (`index.blade.php`) - Affichage paginé avec filtres et recherche
2. **Création de site** (`create.blade.php`) - Formulaire de création avec sections organisées
3. **Détails du site** (`show.blade.php`) - Vue détaillée avec toutes les informations du site
4. **Édition de site** (`edit.blade.php`) - Formulaire d'édition similaire à la création
5. **Carte des sites** (`carte.blade.php`) - Visualisation géographique avec Leaflet.js
6. **Statistiques des sites** (`statistiques.blade.php`) - Tableaux de bord et graphiques avec Chart.js

## Considérations techniques

- Le modèle utilise les traits `SoftDeletes` et `HasUuids` de Laravel
- Les coordonnées GPS sont stockées en format décimal pour faciliter les calculs de distance
- Le calcul de distance utilise la formule de Haversine pour tenir compte de la courbure terrestre
- Les horaires sont stockés en JSON pour permettre une structure flexible

## Intégration avec d'autres modules

Le modèle Site est intégré avec :

1. **Module Entreprise** - Chaque site appartient à une entreprise
2. **Module Employé** - Les employés sont associés à des sites spécifiques
3. **Module Pointage** - Les pointages sont effectués sur des sites
4. **Module Statistiques** - Analyse des données par site

## Sécurité et permissions

L'accès à la gestion des sites est contrôlé par la permission `gerer-sites`. Seuls les utilisateurs ayant cette permission peuvent :
- Voir la liste des sites
- Créer de nouveaux sites
- Modifier les sites existants
- Supprimer des sites
- Accéder aux statistiques et à la carte des sites
