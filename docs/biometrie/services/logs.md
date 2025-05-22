# Service de Gestion des Logs d'Appareils Biométriques

Le service `LogAppareilBiometriqueService` est responsable de la gestion des logs générés par les appareils biométriques, leur traitement et leur conversion en pointages.

## Fonctionnalités principales

- Création et gestion des logs d'appareils biométriques
- Traitement des logs pour générer des pointages
- Filtrage et recherche de logs
- Analyse des logs pour détecter des anomalies
- Archivage et purge des logs anciens

## Interface du service

```php
class LogAppareilBiometriqueService
{
    public function __construct(
        PointageBiometriqueService $pointageService
    );
    
    public function creerLog(
        AppareilBiometrique $appareil, 
        ?User $user, 
        string $type, 
        array $donnees, 
        string $statut = 'info'
    ): LogAppareilBiometrique;
    
    public function creerLogsDepuisDonnees(
        AppareilBiometrique $appareil, 
        array $logsData
    ): array;
    
    public function traiterLog(LogAppareilBiometrique $log): bool;
    public function traiterLogsNonTraites(?AppareilBiometrique $appareil = null): array;
    
    public function rechercherLogs(array $criteres): Collection;
    public function getLogsParAppareil(string $appareilId, ?Carbon $depuis = null): Collection;
    public function getLogsParUtilisateur(string $userId, ?Carbon $depuis = null): Collection;
    
    public function analyserAnomalie(LogAppareilBiometrique $log): ?array;
    public function detecterAnomalies(?AppareilBiometrique $appareil = null): Collection;
    
    public function archiverLogs(Carbon $avantDate): int;
    public function purgerLogs(Carbon $avantDate): int;
}
```

## Méthodes détaillées

### creerLog

Crée un nouveau log d'appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$user` (User|null): Utilisateur concerné (optionnel)
- `$type` (string): Type de log (connexion, pointage, erreur, etc.)
- `$donnees` (array): Données détaillées du log
- `$statut` (string): Statut du log (success, error, warning, info)

**Retourne:**
- `LogAppareilBiometrique`: Instance du log créé

### creerLogsDepuisDonnees

Crée plusieurs logs à partir de données brutes provenant d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$logsData` (array): Données brutes des logs

**Retourne:**
- `array`: Résultats de la création des logs

```php
[
    'total' => 150,
    'success' => 148,
    'errors' => 2,
    'details' => [
        'nouveaux' => 120,
        'existants' => 28,
        'erreurs' => [
            ['message' => 'Format invalide', 'data' => [...]]
        ]
    ]
]
```

### traiterLog

Traite un log pour générer un pointage si nécessaire.

**Paramètres:**
- `$log` (LogAppareilBiometrique): Log à traiter

**Retourne:**
- `bool`: Succès du traitement

### traiterLogsNonTraites

Traite tous les logs non traités d'un appareil ou de tous les appareils.

**Paramètres:**
- `$appareil` (AppareilBiometrique|null): Appareil concerné (optionnel)

**Retourne:**
- `array`: Résultats du traitement

```php
[
    'total' => 50,
    'traites' => 48,
    'erreurs' => 2,
    'pointages_crees' => 35,
    'details' => [
        'entrees' => 20,
        'sorties' => 15,
        'erreurs' => [
            ['log_id' => 'uuid1', 'message' => 'Utilisateur inconnu'],
            ['log_id' => 'uuid2', 'message' => 'Données incomplètes']
        ]
    ]
]
```

### rechercherLogs

Recherche des logs selon des critères spécifiques.

**Paramètres:**
- `$criteres` (array): Critères de recherche

**Retourne:**
- `Collection`: Collection de logs correspondant aux critères

### getLogsParAppareil

Récupère les logs d'un appareil spécifique.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$depuis` (Carbon|null): Date de début (optionnel)

**Retourne:**
- `Collection`: Collection de logs

### getLogsParUtilisateur

Récupère les logs d'un utilisateur spécifique.

**Paramètres:**
- `$userId` (string): ID de l'utilisateur
- `$depuis` (Carbon|null): Date de début (optionnel)

**Retourne:**
- `Collection`: Collection de logs

### analyserAnomalie

Analyse un log pour détecter des anomalies.

**Paramètres:**
- `$log` (LogAppareilBiometrique): Log à analyser

**Retourne:**
- `array|null`: Détails de l'anomalie si détectée, null sinon

```php
[
    'type' => 'sequence_invalide',
    'description' => 'Sortie sans entrée préalable',
    'severite' => 'warning',
    'details' => [
        'derniere_entree' => '2025-03-03 08:15:00',
        'sortie_actuelle' => '2025-03-04 09:30:00'
    ]
]
```

### detecterAnomalies

Détecte les anomalies dans les logs d'un appareil ou de tous les appareils.

**Paramètres:**
- `$appareil` (AppareilBiometrique|null): Appareil concerné (optionnel)

**Retourne:**
- `Collection`: Collection d'anomalies détectées

### archiverLogs

Archive les logs antérieurs à une date donnée.

**Paramètres:**
- `$avantDate` (Carbon): Date limite

**Retourne:**
- `int`: Nombre de logs archivés

### purgerLogs

Supprime définitivement les logs antérieurs à une date donnée.

**Paramètres:**
- `$avantDate` (Carbon): Date limite

**Retourne:**
- `int`: Nombre de logs supprimés

## Format des logs

Les logs d'appareils biométriques sont structurés comme suit:

```php
[
    'id' => 'uuid',
    'appareil_biometrique_id' => 'uuid-appareil',
    'user_id' => 'uuid-user', // Optionnel
    'type' => 'pointage', // connexion, pointage, erreur, etc.
    'donnees' => [
        // Pour un pointage
        'identifiant_biometrique' => '12345',
        'date_evenement' => '2025-03-04 08:30:15',
        'type_pointage' => 'entree', // entree, sortie
        'methode_auth' => 'empreinte', // empreinte, visage, carte, code
        'verification_status' => 'success',
        'raw_data' => '...' // Données brutes de l'appareil
    ],
    'statut' => 'success', // success, error, warning, info
    'date_evenement' => '2025-03-04 08:30:15',
    'traite' => false,
    'date_traitement' => null
]
```

## Exemples d'utilisation

### Création d'un log

```php
$logService = app(LogAppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');
$user = User::find('uuid-user');

$log = $logService->creerLog(
    $appareil,
    $user,
    'pointage',
    [
        'identifiant_biometrique' => '12345',
        'date_evenement' => now(),
        'type_pointage' => 'entree',
        'methode_auth' => 'empreinte',
        'verification_status' => 'success'
    ],
    'success'
);
```

### Traitement des logs non traités

```php
$logService = app(LogAppareilBiometriqueService::class);

// Traiter tous les logs non traités
$result = $logService->traiterLogsNonTraites();

echo "Logs traités: " . $result['traites'] . "/" . $result['total'];
echo "Pointages créés: " . $result['pointages_crees'];
```

### Recherche de logs

```php
$logService = app(LogAppareilBiometriqueService::class);

$logs = $logService->rechercherLogs([
    'appareil_id' => 'uuid-appareil',
    'type' => 'pointage',
    'date_debut' => now()->startOfDay(),
    'date_fin' => now()->endOfDay(),
    'statut' => 'success'
]);

foreach ($logs as $log) {
    echo "Log ID: " . $log->id;
    echo "Date: " . $log->date_evenement;
    echo "Type: " . $log->type;
}
```

### Archivage des logs anciens

```php
$logService = app(LogAppareilBiometriqueService::class);

// Archiver les logs de plus de 3 mois
$count = $logService->archiverLogs(now()->subMonths(3));

echo "Logs archivés: " . $count;
```

## Intégration avec d'autres services

Le service de logs est utilisé par:

1. **AppareilBiometriqueService**: Pour journaliser les opérations sur les appareils
2. **SynchronisationAutomatiqueService**: Pour créer des logs lors des synchronisations
3. **PointageBiometriqueService**: Pour convertir les logs en pointages
4. **LogAppareilBiometriqueController**: Pour l'interface de gestion des logs
