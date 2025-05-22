# Service de Synchronisation Automatique

Le service `SynchronisationAutomatiqueService` est responsable de la gestion des synchronisations automatiques entre le système GENIUS WORK et les appareils biométriques.

## Fonctionnalités principales

- Synchronisation automatique des logs d'appareils
- Synchronisation automatique des utilisateurs
- Synchronisation automatique de l'heure des appareils
- Gestion des intervalles de synchronisation
- Journalisation des opérations de synchronisation

## Interface du service

```php
class SynchronisationAutomatiqueService
{
    public function __construct(
        AppareilBiometriqueStatsService $statsService,
        LogAppareilBiometriqueService $logService
    );
    
    public function synchroniserTousLesAppareils(string $type = 'all', ?int $siteId = null): array;
    public function synchroniserAppareil(string $appareilId, string $type = 'all'): array;
    public function synchroniserAppareils(Collection $appareils, string $type = 'all'): array;
    protected function synchroniserLogsAppareil(AppareilBiometrique $appareil): array;
    protected function synchroniserUtilisateursAppareil(AppareilBiometrique $appareil): array;
    protected function synchroniserHeureAppareil(AppareilBiometrique $appareil): array;
}
```

## Méthodes détaillées

### synchroniserTousLesAppareils

Synchronise tous les appareils configurés pour la synchronisation automatique.

**Paramètres:**
- `$type` (string): Type de synchronisation ('logs', 'users', 'time', 'all')
- `$siteId` (int|null): ID du site pour filtrer les appareils

**Retourne:**
- `array`: Résultats de la synchronisation

### synchroniserAppareil

Synchronise un appareil spécifique.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$type` (string): Type de synchronisation ('logs', 'users', 'time', 'all')

**Retourne:**
- `array`: Résultats de la synchronisation

### synchroniserAppareils

Synchronise une collection d'appareils.

**Paramètres:**
- `$appareils` (Collection): Collection d'appareils à synchroniser
- `$type` (string): Type de synchronisation ('logs', 'users', 'time', 'all')

**Retourne:**
- `array`: Résultats de la synchronisation

### synchroniserLogsAppareil

Synchronise les logs d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultats de la synchronisation des logs

### synchroniserUtilisateursAppareil

Synchronise les utilisateurs d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultats de la synchronisation des utilisateurs

### synchroniserHeureAppareil

Synchronise l'heure d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultats de la synchronisation de l'heure

## Format des résultats

Le service retourne les résultats de synchronisation sous forme de tableau associatif:

```php
[
    'total' => 10,        // Nombre total d'appareils traités
    'success' => 8,       // Nombre d'appareils synchronisés avec succès
    'errors' => 2,        // Nombre d'appareils avec erreurs
    'details' => [        // Détails par appareil
        [
            'id' => 'uuid-1',
            'nom' => 'Appareil 1',
            'adresse_ip' => '192.168.1.100',
            'success' => true,
            'operations' => [
                'logs' => [
                    'success' => true,
                    'message' => '150 logs synchronisés',
                    'count' => 150
                ],
                'users' => [
                    'success' => true,
                    'message' => '25 utilisateurs synchronisés',
                    'count' => 25
                ],
                'time' => [
                    'success' => true,
                    'message' => 'Heure synchronisée'
                ]
            ]
        ],
        // Autres appareils...
    ]
]
```

## Exemples d'utilisation

### Synchronisation complète de tous les appareils

```php
$syncService = app(SynchronisationAutomatiqueService::class);
$results = $syncService->synchroniserTousLesAppareils('all');
```

### Synchronisation des logs d'un appareil spécifique

```php
$syncService = app(SynchronisationAutomatiqueService::class);
$results = $syncService->synchroniserAppareil('uuid-appareil', 'logs');
```

### Synchronisation des utilisateurs pour un site spécifique

```php
$syncService = app(SynchronisationAutomatiqueService::class);
$results = $syncService->synchroniserTousLesAppareils('users', 5);
```

## Intégration avec la planification

Le service est utilisé par les tâches planifiées dans `App\Console\Kernel`:

```php
// Dans App\Console\Kernel
protected function schedule(Schedule $schedule)
{
    // Synchronisation horaire des logs
    $schedule->command('biometrique:sync --type=logs')
             ->hourly();
             
    // Synchronisation quotidienne des utilisateurs
    $schedule->command('biometrique:sync --type=users')
             ->dailyAt('01:00');
             
    // Synchronisation quotidienne de l'heure
    $schedule->command('biometrique:sync --type=time')
             ->dailyAt('00:30');
             
    // Synchronisation complète hebdomadaire
    $schedule->command('biometrique:sync')
             ->weekly()->sundays()->at('02:00');
}
```
