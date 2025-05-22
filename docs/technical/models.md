# Documentation Technique - GeniusWork

## Table des matières
1. [Introduction](#introduction)
2. [Architecture des Modèles](#architecture-des-modèles)
3. [Modèles](#modèles)
4. [Migrations](#migrations)
5. [Relations](#relations)
6. [Fonctionnalités Clés](#fonctionnalités-clés)

## Introduction

GeniusWork est une plateforme SaaS de gestion des présences et des employés, conçue avec une architecture multi-tenant. Le système utilise Laravel comme framework principal avec une base de données MySQL.

### Caractéristiques Principales
- Multi-tenant (isolation par entreprise)
- Authentification multi-rôles
- Tracking des présences avec géolocalisation
- Gestion des congés et permutations
- Système de notification avancé
- QR Codes pour pointage

## Architecture des Modèles

### Traits Communs
Tous les modèles utilisent les traits suivants :
- `HasFactory` : Pour la génération de données de test
- `SoftDeletes` : Pour la suppression douce
- `HasUuids` : Pour l'utilisation d'UUID comme clés primaires

### Convention de Nommage
- Tables : pluriel, snake_case (ex: `methode_pointages`)
- Modèles : singulier, PascalCase (ex: `MethodePointage`)
- Relations : camelCase (ex: `entreprise()`)
- Scopes : camelCase avec préfixe 'scope' (ex: `scopeActif()`)

## Modèles

### User
```php
class User extends Authenticatable
```
Gère les utilisateurs du système avec différents rôles.

#### Attributs Principaux
- `role` : super_admin, admin, entreprise, employeur
- `settings` : Configuration personnalisée (JSON)
- `preferences` : Préférences utilisateur (JSON)

#### Méthodes Clés
- `hasPermissionFor($action)` : Vérifie les permissions
- `updateLastLogin()` : Met à jour la dernière connexion

### Employeur
```php
class Employeur extends Model
```
Gère les informations des employés.

#### Attributs Principaux
- `code_employe` : Code unique (format: EMP-XXX-YYNNNN)
- `qr_code_secret` : Secret pour le QR Code
- `qr_code_expires_at` : Date d'expiration du QR
- `statut` : État de l'employé

#### Méthodes Clés
- `generateEmployeeCode()` : Génère un code unique
- `generateQRCodeSecret()` : Crée un nouveau QR Code
- `validateQRCode($secret)` : Valide un QR Code

### MethodePointage
```php
class MethodePointage extends Model
```
Configure les méthodes de pointage disponibles.

#### Attributs Principaux
- `necessite_photo` : Exige une photo
- `necessite_geolocalisation` : Exige la géolocalisation
- `rayon_geofencing` : Rayon autorisé en mètres

#### Méthodes Clés
- `verifierGeofencing()` : Valide la position GPS
- `verifierValidationRegles()` : Vérifie les règles

### Tracking
```php
class Tracking extends Model
```
Enregistre les pointages des employés.

#### Attributs Principaux
- `type` : entree, sortie, pause_debut, pause_fin
- `latitude`, `longitude` : Coordonnées GPS
- `methode_pointage` : Méthode utilisée

#### Méthodes Clés
- `verifierLocalisation()` : Valide la position
- `valider()`, `invalider()` : Gestion du statut

## Migrations

### Structure de Base
```php
// Exemple de migration
public function up()
{
    Schema::create('table_name', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->foreignUuid('entreprise_id')->constrained();
        $table->timestamps();
        $table->softDeletes();
    });
}
```

### Migrations Principales

#### create_users_table
- Authentification
- Rôles et permissions
- Préférences et paramètres

#### create_entreprises_table
- Informations de l'entreprise
- Configuration multi-tenant
- Paramètres de facturation

#### create_employeurs_table
- Informations des employés
- Codes uniques et QR Codes
- Données professionnelles

#### create_presences_table
- Enregistrement des présences
- Validation et statuts
- Liens avec les pointages

#### create_methode_pointages_table
- Configuration des méthodes
- Règles de validation
- Paramètres de géofencing

## Relations

### Hiérarchie Principal
```
Entreprise
├── Departements
│   └── Employeurs
├── MethodePointages
└── Politiques
```

### Relations Détaillées
- User -> Entreprise (belongsTo)
- Employeur -> Entreprise (belongsTo)
- Presence -> Employeur (belongsTo)
- Tracking -> Presence (belongsTo)

## Fonctionnalités Clés

### Génération de Codes
```php
protected function generateEmployeeCode()
{
    $prefix = 'EMP';
    $entrepriseCode = $this->entreprise->code;
    $year = date('y');
    $sequence = // logique de séquence
    return "{$prefix}-{$entrepriseCode}-{$year}{$sequence}";
}
```

### Validation QR Code
```php
public function validateQRCode($secret)
{
    return $this->qr_code_active && 
           $this->qr_code_secret === $secret && 
           !$this->qr_code_expires_at->isPast();
}
```

### Géofencing
```php
public function verifierGeofencing($latitude, $longitude, $site)
{
    if ($this->autoriser_hors_site) return true;
    $distance = // calcul de la distance
    return $distance <= $this->rayon_geofencing;
}
```

## Bonnes Pratiques

### Sécurité
- Utilisation d'UUID pour les IDs
- Validation stricte des entrées
- Expiration automatique des tokens
- Isolation multi-tenant

### Performance
- Indexes sur les colonnes fréquemment utilisées
- Relations eager loading quand nécessaire
- Soft deletes pour préserver l'historique
- Caching des données fréquentes

### Maintenance
- Documentation PHPDoc complète
- Tests unitaires et d'intégration
- Logging des actions importantes
- Versioning des migrations
