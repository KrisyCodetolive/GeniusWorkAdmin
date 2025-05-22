# Gestion des Entreprises dans GENIUS WORK

## État Actuel

La gestion des entreprises dans GENIUS WORK est actuellement implémentée à travers deux flux principaux :

1. **Création par le SuperAdmin via le dashboard Filament**
   - Interface en wizard avec 5 étapes (Informations entreprise, Adresse, Abonnement, Configuration, Administrateur)
   - Calcul automatique des coûts d'abonnement selon le nombre d'utilisateurs
   - Gestion des codes promo et réductions
   - Création automatique d'un compte administrateur

2. **Inscription en self-service via le workflow public**
   - Processus en 4 étapes (Compte utilisateur, Compte entreprise, Abonnement, Paiement)
   - Intégration avec des passerelles de paiement (Stripe, Paystack)
   - Activation automatique après paiement validé
   - Génération de factures

## Modèle de Données

Le modèle `Entreprise` gère les informations suivantes :
- Informations générales (nom, code, description, statut)
- Coordonnées (email, téléphone, site web)
- Informations fiscales (raison sociale, RCCM, NIF)
- Localisation (adresse, code postal, ville, pays, coordonnées GPS)
- Configuration (devise, fuseau horaire, langue, paramètres de notification)

## Relations Principales

- **Utilisateurs** : Une entreprise peut avoir plusieurs utilisateurs
- **Abonnements** : Une entreprise peut avoir plusieurs abonnements (dont un actif)
- **Facturations** : Une entreprise peut avoir plusieurs facturations
- **Départements** : Une entreprise peut avoir plusieurs départements
- **Sites** : Une entreprise peut avoir plusieurs sites physiques

## Système d'Autorisation

GENIUS WORK implémente un système d'autorisation robuste pour la gestion des entreprises à travers des policies Laravel :

### EntreprisePolicy

La policy `EntreprisePolicy` gère les autorisations pour les ressources `EntrepriseResource` et `MonEntrepriseResource` :

```php
class EntreprisePolicy
{
    use HandlesAuthorization;

    // Seuls SuperAdmin et Support peuvent voir la liste complète des entreprises
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    // Contrôle qui peut voir une entreprise spécifique
    public function view(User $user, Entreprise $entreprise): bool
    {
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        return $user->entreprise_id === $entreprise->id;
    }

    // Seuls SuperAdmin et Support peuvent créer des entreprises
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    // Contrôle qui peut mettre à jour une entreprise
    public function update(User $user, Entreprise $entreprise): bool
    {
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        if ($user->isAdmin() && $user->entreprise_id === $entreprise->id) {
            return true;
        }
        return false;
    }

    // Méthodes spécifiques pour MonEntrepriseResource
    public function viewOwn(User $user): bool
    {
        return $user->entreprise_id !== null;
    }

    public function updateOwn(User $user, Entreprise $entreprise): bool
    {
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        if ($user->isAdmin() && $user->entreprise_id === $entreprise->id) {
            return true;
        }
        return false;
    }
}
```

### Intégration avec Filament

Les ressources Filament utilisent cette policy pour contrôler l'accès :

```php
// EntrepriseResource
public static function canAccess(): bool
{
    $user = auth()->user();
    return $user && $user->can('viewAny', Entreprise::class);
}

// MonEntrepriseResource
public static function canAccess(): bool
{
    $user = Auth::user();
    return $user && $user->can('viewOwn', Entreprise::class);
}

public static function canEdit(Model $record): bool
{
    $user = Auth::user();
    return $user && $user->can('updateOwn', $record);
}
```

## Améliorations Proposées

### 1. Gestion Multi-Entreprises

Actuellement, les utilisateurs avec les rôles SuperAdmin et Support peuvent accéder à toutes les entreprises. Nous pouvons étendre cette fonctionnalité pour permettre à certains utilisateurs de gérer plusieurs entreprises :

```php
// Dans le modèle User
public function entreprises()
{
    return $this->belongsToMany(Entreprise::class, 'user_entreprise')
        ->withPivot('role', 'is_default')
        ->withTimestamps();
}

public function isMultiEntreprise()
{
    return $this->entreprises()->count() > 1;
}

public function switchEntreprise($entrepriseId)
{
    if (!$this->entreprises()->where('entreprise_id', $entrepriseId)->exists()) {
        throw new \Exception("L'utilisateur n'a pas accès à cette entreprise");
    }
    
    session(['current_entreprise_id' => $entrepriseId]);
    return true;
}
```

### 2. Tableau de Bord Entreprise

Créer un tableau de bord spécifique pour chaque entreprise avec des KPIs pertinents :

```php
// Dans EntrepriseController
public function dashboard($id)
{
    $entreprise = Entreprise::findOrFail($id);
    
    $stats = [
        'nombre_employes' => $entreprise->employeurs()->count(),
        'presence_aujourd_hui' => $entreprise->presences()
            ->whereDate('created_at', today())->count(),
        'abonnement' => [
            'statut' => $entreprise->abonnementActif()->statut ?? 'aucun',
            'expiration' => $entreprise->abonnementActif()->date_fin ?? null,
            'jours_restants' => $entreprise->abonnementActif() 
                ? (int)now()->diffInDays($entreprise->abonnementActif()->date_fin, false) 
                : 0
        ],
        'factures_impayees' => $entreprise->facturations()
            ->where('statut_paiement', 'en_attente')->count()
    ];
    
    return view('entreprise.dashboard', compact('entreprise', 'stats'));
}
```

### 3. Gestion des Filiales

Améliorer la gestion des filiales pour les grandes entreprises :

```php
// Dans le modèle Entreprise
public function filiales()
{
    return $this->hasMany(Entreprise::class, 'entreprise_parent_id');
}

public function entrepriseParent()
{
    return $this->belongsTo(Entreprise::class, 'entreprise_parent_id');
}

public function isFiliale()
{
    return !is_null($this->entreprise_parent_id);
}

// Dans la migration
Schema::table('entreprises', function (Blueprint $table) {
    $table->uuid('entreprise_parent_id')->nullable();
    $table->foreign('entreprise_parent_id')
        ->references('id')
        ->on('entreprises')
        ->onDelete('set null');
});
```

### 4. Gestion des Paramètres par Entreprise

Centraliser la gestion des paramètres spécifiques à chaque entreprise :

```php
// Dans EntrepriseController
public function settings($id)
{
    $entreprise = Entreprise::findOrFail($id);
    
    $settings = [
        'general' => [
            'langue' => $entreprise->langue,
            'fuseau_horaire' => $entreprise->fuseau_horaire,
            'devise' => $entreprise->devise
        ],
        'presence' => json_decode($entreprise->parametres_presence, true) ?? [
            'geolocalisation_obligatoire' => false,
            'photo_obligatoire' => false,
            'validation_obligatoire' => true
        ],
        'notification' => json_decode($entreprise->parametres_notification, true) ?? [
            'email' => true,
            'sms' => false,
            'push' => true
        ]
    ];
    
    return view('entreprise.settings', compact('entreprise', 'settings'));
}
```

### 5. Intégration avec la Carte des Sites

Améliorer l'intégration entre la gestion des entreprises et la carte interactive des sites :

```php
// Dans EntrepriseController
public function mapView($id)
{
    $entreprise = Entreprise::findOrFail($id);
    $sites = $entreprise->sites()->with('adresse')->get();
    
    $geoJson = [
        'type' => 'FeatureCollection',
        'features' => $sites->map(function ($site) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [$site->longitude, $site->latitude]
                ],
                'properties' => [
                    'id' => $site->id,
                    'name' => $site->nom,
                    'address' => $site->adresse_complete,
                    'status' => $site->statut,
                    'geofencing' => $site->rayon_geofencing,
                    'employeeCount' => $site->employes_count
                ]
            ];
        })->toArray()
    ];
    
    return view('entreprise.map', compact('entreprise', 'geoJson'));
}
```

### 6. Amélioration du Workflow d'Inscription

Optimiser le processus d'inscription avec une meilleure validation et une expérience utilisateur améliorée :

```php
// Dans WorkflowController
public function validateCompanyAccount(Request $request)
{
    $validator = Validator::make($request->all(), [
        'company_name' => 'required|string|max:255',
        'industry' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'company_size' => 'required|integer|min:1',
        'contact_phone' => [
            'required',
            'string',
            'max:20',
            'regex:/^\+?[0-9]{8,15}$/'
        ],
        'contact_email' => [
            'required',
            'email:rfc,dns',
            'max:255'
        ],
    ], [
        'contact_phone.regex' => 'Le numéro de téléphone doit être au format international (ex: +22507123456)'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    return response()->json([
        'success' => true,
        'message' => 'Données d\'entreprise valides'
    ]);
}
```

### 7. Implémentation d'un Système de Vérification d'Entreprise

Ajouter un processus de vérification pour les nouvelles entreprises :

```php
// Dans EntrepriseService
public function verifyEntreprise(Entreprise $entreprise, array $documents)
{
    // Enregistrer les documents de vérification
    foreach ($documents as $type => $file) {
        $path = $file->store('entreprises/' . $entreprise->id . '/verification');
        
        VerificationDocument::create([
            'entreprise_id' => $entreprise->id,
            'type' => $type,
            'chemin_fichier' => $path,
            'statut' => 'en_attente'
        ]);
    }
    
    // Mettre à jour le statut de l'entreprise
    $entreprise->update([
        'statut_verification' => 'en_attente'
    ]);
    
    // Notifier les administrateurs
    $admins = User::role('SuperAdmin')->get();
    Notification::send($admins, new EntrepriseVerificationRequested($entreprise));
    
    return true;
}
```

### 8. Extension du Système d'Autorisation

Étendre le système d'autorisation pour prendre en charge des cas d'utilisation plus complexes :

```php
// Ajout à EntreprisePolicy
public function manageUsers(User $user, Entreprise $entreprise): bool
{
    if ($user->isSuperAdmin() || $user->isSupport()) {
        return true;
    }
    
    // Les administrateurs peuvent gérer les utilisateurs de leur entreprise
    if ($user->isAdmin() && $user->entreprise_id === $entreprise->id) {
        return true;
    }
    
    // Les gestionnaires RH peuvent aussi gérer les utilisateurs
    if ($user->hasRole('RH') && $user->entreprise_id === $entreprise->id) {
        return true;
    }
    
    return false;
}

public function viewReports(User $user, Entreprise $entreprise): bool
{
    if ($user->isSuperAdmin() || $user->isSupport()) {
        return true;
    }
    
    // Les administrateurs et gestionnaires peuvent voir les rapports
    if (($user->isAdmin() || $user->hasRole('Manager')) && 
        $user->entreprise_id === $entreprise->id) {
        return true;
    }
    
    return false;
}
```

## Conclusion

La gestion des entreprises dans GENIUS WORK est désormais renforcée par un système d'autorisation robuste basé sur des policies Laravel. Ces améliorations permettent un contrôle précis des accès selon les rôles des utilisateurs, tout en maintenant la flexibilité nécessaire pour les administrateurs système.

Les fonctionnalités proposées s'intègrent parfaitement avec les autres modules existants comme la gestion des sites, la carte interactive, et le système d'impersonation déjà en place.

L'implémentation de ces améliorations renforce la position de GENIUS WORK comme une solution complète de gestion d'entreprise, particulièrement adaptée aux organisations avec des structures complexes et des besoins de gestion multi-sites.
