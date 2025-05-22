# Contrôleurs du Module Biométrie

Les contrôleurs du module Biométrie gèrent les interactions entre l'interface utilisateur et les services métier.

## Vue d'ensemble des contrôleurs

| Contrôleur | Responsabilité |
|------------|----------------|
| [AppareilBiometriqueController](#appareilbiometriquecontroller) | Gestion des appareils biométriques |
| [DashboardBiometriqueController](#dashboardbiometriquecontroller) | Tableau de bord et statistiques |
| [LogAppareilBiometriqueController](#logappareilbiometriquecontroller) | Gestion des logs d'appareils |
| [PointageBiometriqueController](#pointagebiometriquecontroller) | Gestion des pointages biométriques |
| [UserBiometriqueController](#userbiometriquecontroller) | Gestion des utilisateurs sur les appareils |

## AppareilBiometriqueController

Contrôleur principal pour la gestion des appareils biométriques.

### Dépendances

```php
protected $appareilService;
protected $statsService;
protected $syncService;

public function __construct(
    AppareilBiometriqueService $appareilService,
    AppareilBiometriqueStatsService $statsService,
    SynchronisationAutomatiqueService $syncService
) {
    $this->appareilService = $appareilService;
    $this->statsService = $statsService;
    $this->syncService = $syncService;
    $this->middleware('auth');
    $this->middleware('permission:gerer_appareils_biometriques');
}
```

### Routes principales

| Méthode | URI | Action | Description |
|---------|-----|--------|-------------|
| GET | /biometrique/appareils | index | Liste des appareils |
| GET | /biometrique/appareils/create | create | Formulaire de création |
| POST | /biometrique/appareils | store | Enregistrement d'un appareil |
| GET | /biometrique/appareils/{id} | show | Détails d'un appareil |
| GET | /biometrique/appareils/{id}/edit | edit | Formulaire d'édition |
| PUT/PATCH | /biometrique/appareils/{id} | update | Mise à jour d'un appareil |
| DELETE | /biometrique/appareils/{id} | destroy | Suppression d'un appareil |
| GET | /biometrique/appareils/{id}/test | testConnection | Test de connexion |
| POST | /biometrique/appareils/{id}/sync-users | syncUsers | Synchronisation des utilisateurs |
| POST | /biometrique/appareils/{id}/sync-logs | syncLogs | Synchronisation des logs |
| POST | /biometrique/appareils/{id}/reboot | reboot | Redémarrage de l'appareil |
| POST | /biometrique/appareils/{id}/sync-time | syncTime | Synchronisation de l'heure |
| GET | /biometrique/appareils/{id}/sync-config | showSyncConfig | Configuration de synchronisation |
| POST | /biometrique/appareils/{id}/sync-config | updateSyncConfig | Mise à jour de la configuration |
| POST | /biometrique/appareils/{id}/run-sync | runSync | Exécution d'une synchronisation |

### Méthodes principales

#### index

Affiche la liste des appareils biométriques avec filtres et pagination.

```php
public function index(Request $request)
{
    $query = AppareilBiometrique::query();
    
    // Filtres
    if ($request->has('site_id') && $request->site_id) {
        $query->where('site_id', $request->site_id);
    }
    
    // Autres filtres...
    
    $appareils = $query->with('site')->paginate(15);
    
    // Récupérer les statistiques pour chaque appareil
    $appareilsWithStats = $appareils->map(function ($appareil) {
        $stats = $this->statsService->getAppareilStats($appareil->id);
        $appareil->stats = $stats;
        return $appareil;
    });
    
    return view('biometrique.appareils.index', [
        'appareils' => $appareils,
        // Autres données pour la vue...
    ]);
}
```

#### show

Affiche les détails d'un appareil biométrique.

```php
public function show($id)
{
    $appareil = AppareilBiometrique::with(['site', 'entreprise'])->findOrFail($id);
    $stats = $this->statsService->getAppareilStats($id);
    
    return view('biometrique.appareils.show', [
        'appareil' => $appareil,
        'stats' => $stats
    ]);
}
```

#### store

Enregistre un nouvel appareil biométrique.

```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'entreprise_id' => 'required|exists:entreprises,id',
        'site_id' => 'required|exists:sites,id',
        'nom' => 'required|string|max:255',
        'modele' => 'required|string|max:100',
        'fabricant' => 'required|string|max:100',
        'adresse_ip' => 'required|ip',
        'port' => 'required|integer|min:1|max:65535',
        'protocole' => 'required|string|max:10',
        // Autres validations...
    ]);
    
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }
    
    $appareil = $this->appareilService->creerAppareil($request->all());
    
    return redirect()->route('biometrique.appareils.show', $appareil->id)
        ->with('success', 'Appareil biométrique créé avec succès');
}
```

#### syncUsers

Synchronise les utilisateurs avec un appareil.

```php
public function syncUsers($id)
{
    $appareil = AppareilBiometrique::findOrFail($id);
    $result = $this->statsService->synchroniserUtilisateurs($appareil);
    
    return response()->json($result);
}
```

#### showSyncConfig

Affiche la page de configuration de la synchronisation automatique.

```php
public function showSyncConfig($id)
{
    $appareil = AppareilBiometrique::findOrFail($id);
    
    return view('biometrique.appareils.sync-config', [
        'appareil' => $appareil
    ]);
}
```

#### updateSyncConfig

Met à jour la configuration de synchronisation automatique.

```php
public function updateSyncConfig(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'sync_auto_enabled' => 'boolean',
        'sync_logs_interval' => 'required_if:sync_auto_enabled,1|integer|min:5|max:10080',
        'sync_users_interval' => 'required_if:sync_auto_enabled,1|integer|min:5|max:10080',
        'sync_time_interval' => 'required_if:sync_auto_enabled,1|integer|min:5|max:10080',
        'sync_options' => 'nullable|json'
    ]);
    
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }
    
    $appareil = AppareilBiometrique::findOrFail($id);
    
    // Mettre à jour les paramètres de synchronisation
    $appareil->sync_auto_enabled = $request->has('sync_auto_enabled') ? (bool)$request->sync_auto_enabled : false;
    $appareil->sync_logs_interval = $request->sync_logs_interval ?? 60;
    $appareil->sync_users_interval = $request->sync_users_interval ?? 1440;
    $appareil->sync_time_interval = $request->sync_time_interval ?? 1440;
    $appareil->sync_options = $request->sync_options ?? null;
    $appareil->save();
    
    return redirect()->route('biometrique.appareils.show', $id)
        ->with('success', 'Configuration de synchronisation mise à jour avec succès');
}
```

## DashboardBiometriqueController

Contrôleur pour le tableau de bord biométrique.

### Méthodes principales

#### index

Affiche le tableau de bord avec statistiques globales.

```php
public function index()
{
    $stats = [
        'total_appareils' => AppareilBiometrique::count(),
        'appareils_actifs' => AppareilBiometrique::actif()->count(),
        'appareils_inactifs' => AppareilBiometrique::inactif()->count(),
        'appareils_erreur' => AppareilBiometrique::enErreur()->count(),
        'total_logs' => LogAppareilBiometrique::count(),
        'logs_non_traites' => LogAppareilBiometrique::nonTraite()->count(),
        'pointages_aujourd_hui' => Presence::where('source', 'biometrique')
            ->whereDate('date_heure', today())
            ->count()
    ];
    
    $appareilsParSite = DB::table('appareil_biometriques')
        ->join('sites', 'appareil_biometriques.site_id', '=', 'sites.id')
        ->select('sites.nom', DB::raw('count(*) as total'))
        ->groupBy('sites.nom')
        ->get();
    
    $logsParJour = LogAppareilBiometrique::selectRaw('DATE(created_at) as date, COUNT(*) as total')
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->limit(30)
        ->get();
    
    return view('biometrique.dashboard.index', [
        'stats' => $stats,
        'appareilsParSite' => $appareilsParSite,
        'logsParJour' => $logsParJour
    ]);
}
```

## LogAppareilBiometriqueController

Contrôleur pour la gestion des logs des appareils biométriques.

### Méthodes principales

#### index

Affiche la liste des logs avec filtres et pagination.

```php
public function index(Request $request)
{
    $query = LogAppareilBiometrique::query();
    
    // Filtres
    if ($request->has('appareil_id') && $request->appareil_id) {
        $query->where('appareil_biometrique_id', $request->appareil_id);
    }
    
    if ($request->has('type') && $request->type) {
        $query->where('type', $request->type);
    }
    
    if ($request->has('statut') && $request->statut) {
        $query->where('statut', $request->statut);
    }
    
    if ($request->has('date_debut') && $request->date_debut) {
        $query->whereDate('date_evenement', '>=', $request->date_debut);
    }
    
    if ($request->has('date_fin') && $request->date_fin) {
        $query->whereDate('date_evenement', '<=', $request->date_fin);
    }
    
    // Tri
    $sortField = $request->get('sort', 'date_evenement');
    $sortDirection = $request->get('direction', 'desc');
    $query->orderBy($sortField, $sortDirection);
    
    $logs = $query->with(['appareil', 'user'])->paginate(50);
    
    return view('biometrique.logs.index', [
        'logs' => $logs,
        'appareils' => AppareilBiometrique::pluck('nom', 'id')
    ]);
}
```

## PointageBiometriqueController

Contrôleur pour la gestion des pointages biométriques.

### Méthodes principales

#### index

Affiche la liste des pointages biométriques.

```php
public function index(Request $request)
{
    $query = Presence::where('source', 'biometrique');
    
    // Filtres
    if ($request->has('user_id') && $request->user_id) {
        $query->where('user_id', $request->user_id);
    }
    
    if ($request->has('site_id') && $request->site_id) {
        $query->where('site_id', $request->site_id);
    }
    
    if ($request->has('date_debut') && $request->date_debut) {
        $query->whereDate('date_heure', '>=', $request->date_debut);
    }
    
    if ($request->has('date_fin') && $request->date_fin) {
        $query->whereDate('date_heure', '<=', $request->date_fin);
    }
    
    $pointages = $query->with(['user', 'site', 'appareilBiometrique'])
        ->orderBy('date_heure', 'desc')
        ->paginate(50);
    
    return view('biometrique.pointages.index', [
        'pointages' => $pointages,
        'sites' => Site::pluck('nom', 'id')
    ]);
}
```

## UserBiometriqueController

Contrôleur pour la gestion des utilisateurs sur les appareils biométriques.

### Méthodes principales

#### index

Affiche la liste des utilisateurs enregistrés sur les appareils.

```php
public function index(Request $request)
{
    $appareilId = $request->get('appareil_id');
    
    if ($appareilId) {
        $appareil = AppareilBiometrique::findOrFail($appareilId);
        $users = $appareil->utilisateursEnregistres()
            ->withPivot(['identifiant_biometrique', 'type_donnee', 'date_enregistrement', 'statut'])
            ->paginate(50);
    } else {
        $users = User::whereHas('appareilsBiometriques')->paginate(50);
    }
    
    return view('biometrique.users.index', [
        'users' => $users,
        'appareils' => AppareilBiometrique::pluck('nom', 'id'),
        'appareilId' => $appareilId
    ]);
}
```

#### enroll

Enregistre un utilisateur sur un appareil.

```php
public function enroll(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'appareil_id' => 'required|exists:appareil_biometriques,id',
        'type_donnee' => 'required|in:empreinte,visage,carte,code',
        'identifiant_biometrique' => 'nullable|string|max:50'
    ]);
    
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }
    
    $appareil = AppareilBiometrique::findOrFail($request->appareil_id);
    $user = User::findOrFail($request->user_id);
    
    $result = $this->appareilService->ajouterUtilisateur($appareil, $user, [
        'type_donnee' => $request->type_donnee,
        'identifiant_biometrique' => $request->identifiant_biometrique
    ]);
    
    if ($result['success']) {
        return redirect()->route('biometrique.users.index', ['appareil_id' => $request->appareil_id])
            ->with('success', 'Utilisateur enregistré avec succès');
    } else {
        return redirect()->back()
            ->with('error', 'Erreur lors de l\'enregistrement: ' . $result['message'])
            ->withInput();
    }
}
```
