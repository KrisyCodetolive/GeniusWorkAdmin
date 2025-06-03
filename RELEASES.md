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



## [1.2.0] - 2025-06-03

### ✨ Nouvelle Tarification des Abonnements et Améliorations du Workflow

#### 🚀 Système de Tarification Flexible
- **Introduction d'une nouvelle structure de plans d'abonnement** :
    - Plans **Starter** (Niveaux 1-2) pour les petites équipes.
    - Plans **Business** (Niveaux 1-3) pour les entreprises en croissance.
    - Plans **Enterprise** (Niveaux 1-10 et au-delà) pour les grandes organisations, avec une tarification dynamique pour les très grandes tailles.
- **Calcul de coût dynamique** :
    - Un coût fixe par plan.
    - Un coût additionnel de `100 FCFA` par employé.
    - Le [TarificationService](cci:2://file:///d:/ProjetCode/G-WORK/App/GeniusWork/GeniusWork/app/Services/TarificationService.php:7:0-200:1) ([app/Services/TarificationService.php](cci:7://file:///d:/ProjetCode/G-WORK/App/GeniusWork/GeniusWork/app/Services/TarificationService.php:0:0-0:0)) centralise toute la logique de détermination des plans et de calcul des coûts.
- **Fonctionnalités des plans** :
    - Chaque catégorie de plan (Starter, Business, Enterprise) et chaque niveau Enterprise supérieur (à partir du niveau 5) offre un ensemble distinct de fonctionnalités.
    - Les fonctionnalités incluent la gestion des présences, des congés, des rapports, l'application mobile, le support technique, les intégrations, l'API, etc.
- **Synchronisation avec la base de données** :
    - Nouvelle commande Artisan `php artisan abonnements:synchroniser-plans` pour créer ou mettre à jour les plans d'abonnement dans la table `plan_abonnements`.
    - Les plans stockés incluent le nom, la description, les prix mensuels/annuels, les limites d'employés, le coût par employé, le statut, la devise, la période de facturation et les fonctionnalités (sous forme de JSON).
    - Incitation pour le paiement annuel (équivalent à 10 mois au lieu de 12).

#### 🔄 Mises à Jour du Workflow d'Inscription
- **Contrôleur `WorkflowController`** ([app/Http/Controllers/WorkflowController.php](cci:7://file:///d:/ProjetCode/G-WORK/App/GeniusWork/GeniusWork/app/Http/Controllers/WorkflowController.php:0:0-0:0)) :
    - La méthode `selectSubscription` utilise maintenant [TarificationService](cci:2://file:///d:/ProjetCode/G-WORK/App/GeniusWork/GeniusWork/app/Services/TarificationService.php:7:0-200:1) pour récupérer les détails du plan et les coûts en fonction de la taille de l'entreprise fournie à l'étape précédente.
    - La méthode `storeSubscription` valide les données soumises, recalcule les coûts côté serveur pour vérification, et stocke les informations détaillées de l'abonnement en session.
    - Les informations stockées incluent le nom du plan, le coût de base, le coût par utilisateur, le coût total, la taille de l'entreprise, le coût mensuel par employé, le coût annuel, le niveau du plan et les fonctionnalités.
- **Vue de sélection d'abonnement** ([resources/views/workflow/subscription.blade.php](cci:7://file:///d:/ProjetCode/G-WORK/App/GeniusWork/GeniusWork/resources/views/workflow/subscription.blade.php:0:0-0:0)) :
    - Affichage dynamique des détails du plan recommandé, y compris le nom, le coût fixe, le coût par employé, le coût total mensuel et annuel.
    - Affichage conditionnel des fonctionnalités incluses en fonction du plan sélectionné.
    - Utilisation d'Alpine.js pour une interface utilisateur réactive lors de la sélection et de l'affichage des coûts.
    - Correction du nom de la route pour la soumission du formulaire vers `workflow.subscription.store`.

#### 🛠️ Points Techniques et Commandes
- **Service Principal** : `App\Services\TarificationService`
- **Commande Artisan** : `php artisan abonnements:synchroniser-plans`
- **Modèle Eloquent** : `App\Models\PlanAbonnement`

---
