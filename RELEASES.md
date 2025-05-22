# GeniusWork - Journal des Versions

## [1.1.0] - 2025-02-27

### Ajout du Système d'Authentification Avancée et Gestion des Rôles

#### 🔐 Authentification Multi-Facteurs
- Ajout de l'authentification par téléphone (OTP)
- Implémentation du système de code PIN pour les actions sensibles
- Gestion de la vérification du téléphone
- Système de verrouillage après tentatives échouées

##### Nouvelles Colonnes User
- `phone` : Numéro de téléphone unique
- `phone_verified_at` : Horodatage de vérification
- `pin` : Code PIN hashé pour actions sensibles
- `pin_changed_at` : Suivi des changements de PIN
- `pin_attempts` : Compteur de tentatives
- `pin_locked_until` : Verrouillage temporaire
- `require_pin_change` : Indicateur de changement requis

#### 🛡️ Système de Rôles et Permissions (Spatie)
- Intégration de spatie/laravel-permission
- Architecture multi-tenant pour les rôles
- Système de permissions hiérarchique
- Support des rôles système et personnalisés

##### Extensions des Rôles
- Support multi-tenant (par entreprise)
- Description détaillée des rôles
- Métadonnées flexibles (JSON)
- Distinction rôles système/personnalisés
- Gestion du statut (actif/inactif)
- Soft deletes pour l'historique

##### Extensions des Permissions
- Groupement logique des permissions
- Description détaillée
- Métadonnées configurables
- Distinction système/personnalisé
- Gestion du statut
- Soft deletes pour l'historique

### 🔄 Migrations
```php
// Ajout téléphone et PIN
php artisan migrate --path=database/migrations/2025_02_27_025218_add_phone_and_pin_to_users_table.php

// Installation Spatie
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

// Extensions personnalisées
php artisan migrate --path=database/migrations/2025_02_27_025219_add_custom_fields_to_roles_and_permissions_tables.php
```

### 🏗️ Modèles Mis à Jour

#### User
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    
    // Nouvelles méthodes
    public function verifyPin($pin)
    public function setPin($pin)
    public function hasVerifiedPhone()
}
```

#### Role
```php
class Role extends SpatieRole
{
    // Extensions
    protected $fillable = [
        'entreprise_id',
        'description',
        'meta_data',
        'is_system',
        'statut'
    ];
}
```

#### Permission
```php
class Permission extends SpatiePermission
{
    // Extensions
    protected $fillable = [
        'description',
        'groupe',
        'meta_data',
        'is_system',
        'statut'
    ];
}
```

### 🔒 Fonctionnalités de Sécurité
- Verrouillage après 5 tentatives de PIN échouées
- Délai de 30 minutes avant déblocage
- Hashage automatique des PINs
- Validation du téléphone requise
- Isolation multi-tenant des rôles

### 📱 Nouvelles Fonctionnalités
- Authentification par SMS
- Actions protégées par PIN
- Gestion granulaire des permissions
- Rôles personnalisables par entreprise
- Groupement logique des permissions

### 🔄 Migration des Données
- Les rôles existants restent compatibles
- Ajout transparent des nouveaux champs
- Conservation de l'historique (soft deletes)
- Support de la rétrocompatibilité

### 📋 Prochaines Étapes
- Seeder pour les rôles et permissions de base
- Interface d'administration des rôles
- Tableau de bord de sécurité
- Rapports d'audit des accès

---

## [1.0.0] - 2025-02-26
Version initiale de GeniusWork
- Système de base
- Gestion des employés
- Suivi des présences
