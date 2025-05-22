# Documentation du modèle Filiale

## Vue d'ensemble

Le modèle `Filiale` est un composant essentiel du système GENIUS WORK, permettant la gestion avancée des entités organisationnelles de niveau supérieur. Au-delà des fonctionnalités CRUD standard, ce modèle offre des capacités sophistiquées pour la gestion des structures organisationnelles, incluant la génération automatique de codes, les statistiques détaillées, la visualisation de structures hiérarchiques et la fusion de filiales.

## Table des matières

1. [Attributs](#attributs)
2. [Relations](#relations)
3. [Fonctionnalités avancées](#fonctionnalités-avancées)
   - [Génération de codes](#génération-de-codes)
   - [Statistiques détaillées](#statistiques-détaillées)
   - [Structure hiérarchique](#structure-hiérarchique)
   - [Fusion de filiales](#fusion-de-filiales)
   - [Vérifications de sécurité](#vérifications-de-sécurité)
4. [Méthodes principales](#méthodes-principales)
5. [Exemples d'utilisation](#exemples-dutilisation)
6. [Bonnes pratiques](#bonnes-pratiques)
7. [Exceptions](#exceptions)

## Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| id | bigint | Identifiant unique de la filiale |
| code | string | Code unique généré automatiquement |
| nom | string | Nom de la filiale |
| description | text | Description détaillée de la filiale |
| adresse | text | Adresse physique de la filiale |
| ville | string | Ville où se situe la filiale |
| pays | string | Pays où se situe la filiale |
| telephone | string | Numéro de téléphone principal |
| email | string | Email de contact principal |
| site_web | string | Site web de la filiale |
| logo | string | Chemin vers le logo de la filiale |
| statut | enum | Statut de la filiale (active, inactive) |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de dernière modification |
| deleted_at | timestamp | Date de suppression (soft delete) |

## Relations

| Relation | Type | Description |
|----------|------|-------------|
| departements | HasMany | Les départements appartenant à cette filiale |
| employes | HasManyThrough | Les employés travaillant dans cette filiale (via départements) |
| directeur | BelongsTo | L'employé directeur de la filiale |

## Fonctionnalités avancées

### Génération de codes

Le système génère automatiquement des codes uniques pour chaque filiale, facilitant l'identification et la gestion.

```php
// Exemple de génération de code
$code = $filiale->genererCode();
```

#### Structure des codes

- Les codes sont générés selon le format : `FIL{SÉQUENCE}`
- Exemple : `FIL001` pour la première filiale
- La séquence est incrémentée automatiquement pour chaque nouvelle filiale

### Statistiques détaillées

Le modèle offre des méthodes pour calculer et récupérer des statistiques détaillées sur la filiale :

- Nombre total d'employés
- Répartition des employés par département
- Budget total et budget disponible
- Taux d'occupation global
- Performance par département

```php
// Récupérer les statistiques complètes
$statistiques = $filiale->getStatistiquesCompletes();

// Récupérer le nombre total d'employés
$nombreEmployes = $filiale->getNombreTotalEmployes();

// Récupérer le budget total
$budgetTotal = $filiale->getBudgetTotal();
```

### Structure hiérarchique

Le modèle permet de visualiser et de manipuler la structure hiérarchique complète des départements au sein de la filiale.

```php
// Récupérer la structure hiérarchique complète
$structure = $filiale->getStructureHierarchique();
```

#### Représentation de la structure

La structure est représentée sous forme d'arbre, avec les départements de premier niveau comme racines et leurs sous-départements comme branches.

```php
[
    'departement_id' => 1,
    'nom' => 'Direction',
    'code' => 'FIL001-1001',
    'sous_departements' => [
        [
            'departement_id' => 2,
            'nom' => 'Ressources Humaines',
            'code' => 'FIL001-1001-001',
            'sous_departements' => [...]
        ],
        [...]
    ]
]
```

### Fusion de filiales

Le modèle permet de fusionner deux filiales, en transférant tous les départements et employés de la filiale source vers la filiale cible.

```php
// Fusionner deux filiales
$filialeCible->fusionnerAvec($filialeSource);
```

#### Processus de fusion

1. Vérification des conditions préalables
2. Transfert des départements
3. Mise à jour des codes des départements
4. Transfert des employés
5. Suppression logique de la filiale source

### Vérifications de sécurité

Le modèle intègre des vérifications de sécurité pour les opérations critiques :

- Vérification avant suppression
- Validation des opérations de fusion
- Contrôle d'accès basé sur les rôles

```php
// Vérifier si une filiale peut être supprimée
$peutEtreSupprimer = $filiale->peutEtreSupprimer();
```

## Méthodes principales

| Méthode | Description |
|---------|-------------|
| `genererCode()` | Génère un code unique pour la filiale |
| `getStatistiquesCompletes()` | Récupère les statistiques complètes de la filiale |
| `getNombreTotalEmployes()` | Récupère le nombre total d'employés de la filiale |
| `getBudgetTotal()` | Calcule le budget total de la filiale |
| `getBudgetDisponible()` | Calcule le budget disponible de la filiale |
| `getStructureHierarchique()` | Récupère la structure hiérarchique complète des départements |
| `fusionnerAvec(Filiale $source)` | Fusionne la filiale source dans cette filiale |
| `peutEtreSupprimer()` | Vérifie si la filiale peut être supprimée |
| `getTauxOccupationGlobal()` | Calcule le taux d'occupation global de la filiale |
| `getDepartementsRacines()` | Récupère les départements de premier niveau de la filiale |

## Exemples d'utilisation

### Création d'une filiale

```php
$filiale = new Filiale();
$filiale->nom = 'GENIUS WORK Paris';
$filiale->description = 'Filiale parisienne de GENIUS WORK';
$filiale->adresse = '123 Avenue des Champs-Élysées';
$filiale->ville = 'Paris';
$filiale->pays = 'France';
$filiale->telephone = '+33 1 23 45 67 89';
$filiale->email = 'paris@GENIUS WORK.com';
$filiale->site_web = 'https://paris.GENIUS WORK.com';
$filiale->save();

// Le code est généré automatiquement
echo $filiale->code; // Exemple: FIL001
```

### Récupération des statistiques

```php
// Statistiques complètes
$statistiques = $filiale->getStatistiquesCompletes();

// Affichage des statistiques principales
echo "Nombre d'employés: {$statistiques['nombre_employes']}";
echo "Budget total: {$statistiques['budget_total']} €";
echo "Budget disponible: {$statistiques['budget_disponible']} €";
echo "Taux d'occupation: {$statistiques['taux_occupation']}%";
```

### Visualisation de la structure hiérarchique

```php
$structure = $filiale->getStructureHierarchique();

// Exemple d'affichage récursif de la structure
function afficherStructure($departements, $niveau = 0) {
    foreach ($departements as $dept) {
        echo str_repeat('--', $niveau) . ' ' . $dept['nom'] . ' (' . $dept['code'] . ')' . PHP_EOL;
        if (!empty($dept['sous_departements'])) {
            afficherStructure($dept['sous_departements'], $niveau + 1);
        }
    }
}

afficherStructure($structure);
```

### Fusion de filiales

```php
try {
    DB::transaction(function () use ($filialeCible, $filialeSource) {
        $filialeCible->fusionnerAvec($filialeSource);
    });
    // Fusion réussie
} catch (FilialeException $e) {
    // Gérer l'erreur
    Log::error('Erreur lors de la fusion: ' . $e->getMessage());
}
```

## Bonnes pratiques

1. **Utiliser les transactions** pour les opérations qui modifient plusieurs enregistrements :
   ```php
   DB::transaction(function () use ($filialeCible, $filialeSource) {
       $filialeCible->fusionnerAvec($filialeSource);
   });
   ```

2. **Vérifier les conditions préalables** avant d'effectuer des opérations critiques :
   ```php
   if ($filiale->peutEtreSupprimer()) {
       $filiale->delete();
   }
   ```

3. **Gérer les exceptions** spécifiques au modèle (`FilialeException`) pour traiter correctement les erreurs.

4. **Utiliser les méthodes du modèle** pour les opérations complexes plutôt que de manipuler directement les attributs.

5. **Maintenir la cohérence des données** en utilisant les méthodes de mise à jour des codes lors des modifications de structure.

## Exceptions

Le modèle utilise la classe `FilialeException` pour gérer les erreurs spécifiques aux opérations sur les filiales.

Exemples d'exceptions :

- Tentative de fusion de filiales incompatibles
- Suppression d'une filiale avec des dépendances
- Violation des contraintes d'intégrité

```php
try {
    $filialeCible->fusionnerAvec($filialeSource);
} catch (FilialeException $e) {
    // Gérer l'erreur spécifique
    Log::error('Erreur lors de la fusion de filiales: ' . $e->getMessage());
    // Informer l'utilisateur
}
```

---

Cette documentation est maintenue par l'équipe GENIUS WORK. Pour toute question ou suggestion, veuillez contacter l'administrateur système.

Dernière mise à jour : 3 mars 2025
