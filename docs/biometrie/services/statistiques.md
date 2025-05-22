# Service de Statistiques des Appareils Biométriques

Le service `AppareilBiometriqueStatsService` est responsable de la collecte et de l'analyse des statistiques des appareils biométriques.

## Fonctionnalités principales

- Collecte des statistiques d'utilisation des appareils
- Analyse des logs et des pointages
- Synchronisation des données avec les appareils
- Génération de rapports statistiques
- Suivi des performances des appareils

## Interface du service

```php
class AppareilBiometriqueStatsService
{
    public function __construct(
        AppareilBiometriqueService $appareilService,
        LogAppareilBiometriqueService $logService
    );
    
    public function getAppareilStats(string $appareilId): array;
    public function getUtilisationStats(string $appareilId, ?Carbon $debut = null, ?Carbon $fin = null): array;
    public function getLogsStats(string $appareilId, ?Carbon $debut = null, ?Carbon $fin = null): array;
    public function getPointagesStats(string $appareilId, ?Carbon $debut = null, ?Carbon $fin = null): array;
    public function getPerformanceStats(string $appareilId, ?Carbon $debut = null, ?Carbon $fin = null): array;
    
    public function synchroniserLogs(AppareilBiometrique $appareil): array;
    public function synchroniserUtilisateurs(AppareilBiometrique $appareil): array;
    public function synchroniserHeure(AppareilBiometrique $appareil): array;
    
    public function generateRapportUtilisation(string $appareilId, ?Carbon $debut = null, ?Carbon $fin = null): array;
    public function generateRapportGlobal(?Carbon $debut = null, ?Carbon $fin = null): array;
}
```

## Méthodes détaillées

### getAppareilStats

Récupère les statistiques générales d'un appareil.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil

**Retourne:**
- `array`: Statistiques de l'appareil

```php
[
    'total_logs' => 1250,
    'logs_non_traites' => 15,
    'total_utilisateurs' => 45,
    'total_pointages' => 980,
    'derniere_connexion' => '2025-03-03 14:25:30',
    'statut_connexion' => 'connecté',
    'performance' => [
        'temps_reponse_moyen' => 0.8, // secondes
        'taux_erreur' => 0.5, // pourcentage
        'uptime' => 99.8 // pourcentage
    ],
    'utilisation_capacite' => [
        'empreintes' => 25, // pourcentage
        'visages' => 15, // pourcentage
        'cartes' => 30, // pourcentage
        'logs' => 45 // pourcentage
    ]
]
```

### getUtilisationStats

Récupère les statistiques d'utilisation d'un appareil sur une période donnée.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$debut` (Carbon|null): Date de début de la période
- `$fin` (Carbon|null): Date de fin de la période

**Retourne:**
- `array`: Statistiques d'utilisation

### getLogsStats

Récupère les statistiques des logs d'un appareil sur une période donnée.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$debut` (Carbon|null): Date de début de la période
- `$fin` (Carbon|null): Date de fin de la période

**Retourne:**
- `array`: Statistiques des logs

### getPointagesStats

Récupère les statistiques des pointages d'un appareil sur une période donnée.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$debut` (Carbon|null): Date de début de la période
- `$fin` (Carbon|null): Date de fin de la période

**Retourne:**
- `array`: Statistiques des pointages

### getPerformanceStats

Récupère les statistiques de performance d'un appareil sur une période donnée.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$debut` (Carbon|null): Date de début de la période
- `$fin` (Carbon|null): Date de fin de la période

**Retourne:**
- `array`: Statistiques de performance

### synchroniserLogs

Synchronise les logs d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultats de la synchronisation des logs

```php
[
    'success' => true,
    'message' => '150 logs synchronisés',
    'count' => 150,
    'details' => [
        'nouveaux' => 120,
        'existants' => 30,
        'erreurs' => 0
    ]
]
```

### synchroniserUtilisateurs

Synchronise les utilisateurs d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultats de la synchronisation des utilisateurs

```php
[
    'success' => true,
    'message' => '25 utilisateurs synchronisés',
    'count' => 25,
    'details' => [
        'ajoutes' => 5,
        'mis_a_jour' => 20,
        'supprimes' => 0,
        'erreurs' => 0
    ]
]
```

### synchroniserHeure

Synchronise l'heure d'un appareil.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Instance de l'appareil

**Retourne:**
- `array`: Résultats de la synchronisation de l'heure

```php
[
    'success' => true,
    'message' => 'Heure synchronisée',
    'details' => [
        'heure_precedente' => '2025-03-03 14:25:30',
        'heure_actuelle' => '2025-03-03 14:30:00',
        'decalage' => 270 // secondes
    ]
]
```

### generateRapportUtilisation

Génère un rapport d'utilisation pour un appareil sur une période donnée.

**Paramètres:**
- `$appareilId` (string): ID de l'appareil
- `$debut` (Carbon|null): Date de début de la période
- `$fin` (Carbon|null): Date de fin de la période

**Retourne:**
- `array`: Rapport d'utilisation

### generateRapportGlobal

Génère un rapport global pour tous les appareils sur une période donnée.

**Paramètres:**
- `$debut` (Carbon|null): Date de début de la période
- `$fin` (Carbon|null): Date de fin de la période

**Retourne:**
- `array`: Rapport global

## Exemples d'utilisation

### Récupération des statistiques d'un appareil

```php
$statsService = app(AppareilBiometriqueStatsService::class);
$stats = $statsService->getAppareilStats('uuid-appareil');

echo "Total des logs: " . $stats['total_logs'];
echo "Utilisateurs enregistrés: " . $stats['total_utilisateurs'];
```

### Synchronisation des logs

```php
$statsService = app(AppareilBiometriqueStatsService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

$result = $statsService->synchroniserLogs($appareil);

if ($result['success']) {
    echo $result['count'] . " logs synchronisés";
} else {
    echo "Erreur: " . $result['message'];
}
```

### Génération d'un rapport d'utilisation

```php
$statsService = app(AppareilBiometriqueStatsService::class);

// Rapport pour le mois dernier
$debut = now()->startOfMonth()->subMonth();
$fin = now()->startOfMonth()->subSecond();

$rapport = $statsService->generateRapportUtilisation('uuid-appareil', $debut, $fin);

// Traitement du rapport...
```

## Intégration avec d'autres services

Le service de statistiques est utilisé par:

1. **AppareilBiometriqueController**: Pour afficher les statistiques dans l'interface utilisateur
2. **DashboardBiometriqueController**: Pour générer les tableaux de bord
3. **SynchronisationAutomatiqueService**: Pour les opérations de synchronisation
4. **Commande SynchroniserAppareilsBiometriques**: Pour les synchronisations via la ligne de commande
