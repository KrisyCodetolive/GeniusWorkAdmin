# Sécurité et Séparation des Données dans GENIUS WORK (SaaS)

Ce document présente les mécanismes mis en place pour garantir la souveraineté des données, la fiabilité et la séparation stricte des données en fonction de l'utilisateur connecté dans l'application GENIUS WORK.

## Architecture SaaS Multi-Tenant

GENIUS WORK est conçu comme une application SaaS (Software as a Service) multi-tenant où chaque entreprise représente un "tenant" distinct. Cette architecture permet à plusieurs entreprises d'utiliser la même instance de l'application tout en maintenant leurs données strictement séparées.

### Clé de Séparation : `entreprise_id`

- **Identifiant unique** : Chaque entreprise possède un identifiant unique sous forme d'UUID pour une sécurité renforcée
- **Clé étrangère** : La plupart des tables de la base de données contiennent une colonne `entreprise_id` qui sert de clé étrangère
- **Relations indirectes** : Les tables qui n'ont pas directement `entreprise_id` sont liées à des tables qui l'ont (ex: Congés → Employeur → Entreprise)

## Rôles Spéciaux avec Accès Complet

Certains rôles spéciaux ont besoin d'accéder à toutes les données de l'application, indépendamment de l'entreprise :

- **SuperAdmin** : Administrateurs de la plateforme avec accès complet à toutes les données
- **Support** : Équipe de support technique pouvant accéder aux données de toutes les entreprises pour résoudre les problèmes

Ces rôles sont exemptés des restrictions de séparation des données par entreprise dans tous les mécanismes de sécurité.

## Mécanismes de Sécurité Implémentés

### 1. Trait `BelongsToEntreprise`

Ce trait peut être utilisé par tous les modèles qui ont une relation avec une entreprise pour:

- Appliquer automatiquement le scope global `EntrepriseScope`
- Définir la relation `entreprise()`
- Fournir des scopes pratiques comme `currentEntreprise()` et `forEntreprise()`
- Gérer les exceptions pour les rôles SuperAdmin et Support

```php
use App\Traits\BelongsToEntreprise;

class MonModel extends Model
{
    use BelongsToEntreprise;
    
    // Le reste du modèle...
}
```

### 2. Scope Global `EntrepriseScope`

Ce scope global filtre automatiquement toutes les requêtes pour ne retourner que les données appartenant à l'entreprise de l'utilisateur connecté, sauf pour les SuperAdmin et Support:

```php
// Appliqué automatiquement via le trait BelongsToEntreprise
// Pour les utilisateurs normaux: WHERE entreprise_id = {id de l'entreprise de l'utilisateur}
// Pour SuperAdmin et Support: Aucune restriction
$users = User::all();
```

### 3. Middleware `EntrepriseAccessMiddleware`

Ce middleware robuste:

- Vérifie si l'utilisateur est authentifié
- Autorise l'accès complet aux SuperAdmin et Support
- Pour les autres utilisateurs:
  - Vérifie si l'utilisateur a une entreprise associée
  - Vérifie si l'entreprise est active
  - Empêche l'accès aux données d'autres entreprises via les paramètres d'URL
- Journalise les tentatives d'accès non autorisées

```php
// Dans routes/web.php
Route::middleware(['auth', 'entreprise.access'])->group(function () {
    // Routes protégées...
});
```

### 4. Filtrage Explicite dans les Services et Contrôleurs

En plus des mécanismes automatiques, les services et contrôleurs peuvent appliquer un filtrage explicite:

```php
// Exemple dans un service
public function getPresences($filters = [])
{
    $query = Presence::query();
    
    // Filtrage explicite par entreprise sauf pour SuperAdmin et Support
    if (Auth::user()->entreprise_id && !Auth::user()->isSuperAdmin() && !Auth::user()->isSupport()) {
        $query->where('entreprise_id', Auth::user()->entreprise_id);
    }
    
    // Autres filtres...
    
    return $query->get();
}
```

## Bonnes Pratiques pour le Développement

1. **Utiliser le trait `BelongsToEntreprise`** pour tous les modèles liés à une entreprise
2. **Appliquer le middleware `EntrepriseAccessMiddleware`** à toutes les routes qui accèdent à des données spécifiques à une entreprise
3. **Vérifier explicitement l'entreprise** dans les requêtes manuelles ou complexes
4. **Journaliser les accès** aux données sensibles
5. **Tester régulièrement** la séparation des données avec des utilisateurs de différentes entreprises
6. **Gérer correctement les rôles spéciaux** en s'assurant qu'ils ont accès à toutes les données nécessaires

## Schéma de Relations

```
User
└── entreprise_id → Entreprise

Employeur
└── entreprise_id → Entreprise

Conge
└── employeur_id → Employeur → entreprise_id → Entreprise

Site
└── entreprise_id → Entreprise

Presence
└── employeur_id → Employeur → entreprise_id → Entreprise
└── site_id → Site → entreprise_id → Entreprise
```

## Audit et Surveillance

- Toutes les tentatives d'accès non autorisées sont journalisées
- Les actions sensibles (modification de données, suppression) sont tracées
- Des alertes peuvent être configurées pour les comportements suspects
- Les accès des SuperAdmin et Support sont également journalisés pour des raisons d'audit

---

Cette architecture garantit que les utilisateurs n'ont accès qu'aux données de leur propre entreprise, même en cas de tentative d'accès direct via URL ou API, assurant ainsi la souveraineté et la sécurité des données de chaque client. Les rôles spéciaux (SuperAdmin et Support) peuvent accéder à toutes les données pour des raisons administratives et de support technique.
