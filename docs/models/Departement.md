# Documentation du modèle Departement

## Vue d'ensemble

Le modèle `Departement` est une composante centrale du système GENIUS WORK, permettant une gestion avancée de la structure organisationnelle des entreprises. Il offre des fonctionnalités qui vont bien au-delà des simples opérations CRUD, avec une gestion hiérarchique sophistiquée, des mécanismes de fusion et de déplacement, ainsi que des calculs statistiques avancés.

## Table des matières

1. [Attributs](#attributs)
2. [Relations](#relations)
3. [Fonctionnalités avancées](#fonctionnalités-avancées)
   - [Génération de codes](#génération-de-codes)
   - [Gestion hiérarchique](#gestion-hiérarchique)
   - [Fusion de départements](#fusion-de-départements)
   - [Transfert d'employés](#transfert-demployés)
   - [Statistiques](#statistiques)
   - [Vérifications de sécurité](#vérifications-de-sécurité)
4. [Méthodes principales](#méthodes-principales)
5. [Exemples d'utilisation](#exemples-dutilisation)
6. [Bonnes pratiques](#bonnes-pratiques)
7. [Exceptions](#exceptions)

## Attributs

| Attribut | Type | Description |
|----------|------|-------------|
| id | bigint | Identifiant unique du département |
| code | string | Code unique généré automatiquement |
| nom | string | Nom du département |
| description | text | Description détaillée du département |
| filiale_id | bigint | Identifiant de la filiale à laquelle appartient le département |
| departement_parent_id | bigint | Identifiant du département parent (null si département racine) |
| niveau | integer | Niveau hiérarchique du département |
| budget | decimal | Budget alloué au département |
| limite_employes | integer | Nombre maximum d'employés autorisés |
| objectifs | text | Objectifs du département |
| statut | enum | Statut du département (actif, inactif) |
| created_at | timestamp | Date de création |
| updated_at | timestamp | Date de dernière modification |
| deleted_at | timestamp | Date de suppression (soft delete) |

## Relations

| Relation | Type | Description |
|----------|------|-------------|
| filiale | BelongsTo | La filiale à laquelle appartient le département |
| departementParent | BelongsTo | Le département parent de ce département |
| sousDepartements | HasMany | Les sous-départements de ce département |
| employes | HasMany | Les employés assignés à ce département |
| responsable | BelongsTo | L'employé responsable du département |

## Fonctionnalités avancées

### Génération de codes

Le système génère automatiquement des codes uniques pour chaque département, suivant une structure hiérarchique qui reflète l'organisation de l'entreprise.

```php
// Exemple de génération de code
$code = $departement->genererCode();
```

#### Structure des codes

- Les codes sont générés selon le format : `{CODE_FILIALE}-{NIVEAU}{SÉQUENCE}`
- Exemple : `FIL001-1001` pour un département de premier niveau dans la filiale FIL001
- Pour les sous-départements : `FIL001-2001-001` où 2001 est le code du département parent

### Gestion hiérarchique

Le modèle permet une gestion complète de la hiérarchie des départements, avec des fonctionnalités pour :

- Déterminer le niveau hiérarchique
- Récupérer l'arborescence complète
- Déplacer un département dans la hiérarchie
- Gérer les sous-départements

```php
// Récupérer l'arborescence complète
$arborescence = $departement->getArborescence();

// Déplacer un département
$departement->deplacer($nouvelleFiliale, $nouveauParent);
```

### Fusion de départements

Le modèle permet de fusionner deux départements, en transférant tous les employés et sous-départements du département source vers le département cible.

```php
// Fusionner deux départements
$departementCible->fusionnerAvec($departementSource);
```

#### Processus de fusion

1. Vérification des conditions préalables
2. Transfert des employés
3. Réaffectation des sous-départements
4. Mise à jour des codes
5. Suppression logique du département source

### Transfert d'employés

Le modèle offre des méthodes pour transférer des employés entre départements, avec des vérifications de capacité et des mises à jour automatiques des statistiques.

```php
// Transférer des employés vers un autre département
$departement->transfererEmployes($departementCible, $employeIds);
```

### Statistiques

Le modèle calcule diverses statistiques utiles pour la gestion des départements :

- Taux d'occupation
- Budget disponible
- Nombre d'employés par niveau hiérarchique
- Répartition des coûts

```php
// Calculer le taux d'occupation
$tauxOccupation = $departement->calculerTauxOccupation();

// Calculer le budget disponible
$budgetDisponible = $departement->calculerBudgetDisponible();
```

### Vérifications de sécurité

Le modèle intègre des vérifications de sécurité pour les opérations critiques :

- Vérification avant suppression
- Contrôle des limites d'employés
- Validation des opérations de fusion et de déplacement

```php
// Vérifier si un département peut être supprimé
$peutEtreSupprimer = $departement->peutEtreSupprimer();
```

## Méthodes principales

| Méthode | Description |
|---------|-------------|
| `genererCode()` | Génère un code unique pour le département |
| `getArborescence()` | Récupère l'arborescence complète du département |
| `deplacer(Filiale $filiale, ?Departement $parent)` | Déplace le département vers une nouvelle filiale et/ou un nouveau parent |
| `fusionnerAvec(Departement $source)` | Fusionne le département source dans ce département |
| `transfererEmployes(Departement $cible, array $employeIds)` | Transfère les employés spécifiés vers le département cible |
| `calculerTauxOccupation()` | Calcule le taux d'occupation du département |
| `calculerBudgetDisponible()` | Calcule le budget disponible du département |
| `peutEtreSupprimer()` | Vérifie si le département peut être supprimé |
| `mettreAJourNiveaux()` | Met à jour les niveaux hiérarchiques du département et de ses sous-départements |
| `mettreAJourCodes()` | Met à jour les codes du département et de ses sous-départements |

## Exemples d'utilisation

### Création d'un département

```php
$departement = new Departement();
$departement->nom = 'Ressources Humaines';
$departement->description = 'Département de gestion des ressources humaines';
$departement->filiale_id = $filiale->id;
$departement->budget = 100000;
$departement->limite_employes = 20;
$departement->save();

// Le code est généré automatiquement
echo $departement->code; // Exemple: FIL001-1001
```

### Création d'un sous-département

```php
$sousDepartement = new Departement();
$sousDepartement->nom = 'Recrutement';
$sousDepartement->description = 'Service de recrutement';
$sousDepartement->filiale_id = $filiale->id;
$sousDepartement->departement_parent_id = $departement->id;
$sousDepartement->budget = 50000;
$sousDepartement->limite_employes = 10;
$sousDepartement->save();

// Le code est généré automatiquement en tenant compte du parent
echo $sousDepartement->code; // Exemple: FIL001-1001-001
```

### Déplacement d'un département

```php
// Déplacer un département vers une autre filiale
$departement->deplacer($nouvelleFiliale, null);

// Déplacer un département sous un nouveau parent
$departement->deplacer($filiale, $nouveauParent);
```

### Fusion de départements

```php
try {
    $departementCible->fusionnerAvec($departementSource);
    // Fusion réussie
} catch (DepartementException $e) {
    // Gérer l'erreur
    echo $e->getMessage();
}
```

### Calcul de statistiques

```php
// Taux d'occupation
$tauxOccupation = $departement->calculerTauxOccupation();
echo "Taux d'occupation: {$tauxOccupation}%";

// Budget disponible
$budgetDisponible = $departement->calculerBudgetDisponible();
echo "Budget disponible: {$budgetDisponible} €";
```

## Bonnes pratiques

1. **Toujours utiliser les méthodes du modèle** pour les opérations complexes (fusion, déplacement, transfert) plutôt que de manipuler directement les attributs.

2. **Gérer les exceptions** spécifiques au modèle (`DepartementException`) pour traiter correctement les erreurs.

3. **Vérifier les conditions préalables** avant d'effectuer des opérations critiques :
   ```php
   if ($departement->peutEtreSupprimer()) {
       $departement->delete();
   }
   ```

4. **Utiliser les transactions** pour les opérations qui modifient plusieurs enregistrements :
   ```php
   DB::transaction(function () use ($departementCible, $departementSource) {
       $departementCible->fusionnerAvec($departementSource);
   });
   ```

5. **Respecter la hiérarchie** lors de la création de nouveaux départements pour maintenir une structure organisationnelle cohérente.

## Exceptions

Le modèle utilise la classe `DepartementException` pour gérer les erreurs spécifiques aux opérations sur les départements.

Exemples d'exceptions :

- Tentative de fusion de départements incompatibles
- Dépassement de la limite d'employés
- Création d'une boucle dans la hiérarchie
- Suppression d'un département avec des dépendances

```php
try {
    $departement->deplacer($filiale, $nouveauParent);
} catch (DepartementException $e) {
    // Gérer l'erreur spécifique
    Log::error('Erreur lors du déplacement du département: ' . $e->getMessage());
    // Informer l'utilisateur
}
```

---

Cette documentation est maintenue par l'équipe GENIUS WORK. Pour toute question ou suggestion, veuillez contacter l'administrateur système.

Dernière mise à jour : 3 mars 2025
