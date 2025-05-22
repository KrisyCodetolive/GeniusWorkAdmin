# Services du Module Biométrie

Le module Biométrie utilise une architecture orientée services pour encapsuler la logique métier et faciliter la maintenance et les tests.

## Vue d'ensemble des services

| Service | Responsabilité |
|---------|----------------|
| [SynchronisationAutomatiqueService](./services/synchronisation-automatique.md) | Gestion de la synchronisation automatique des appareils |
| [AppareilBiometriqueService](./services/appareil-biometrique.md) | Opérations CRUD et communication avec les appareils |
| [AppareilBiometriqueStatsService](./services/statistiques.md) | Collecte et analyse des statistiques des appareils |
| [LogAppareilBiometriqueService](./services/logs.md) | Gestion des logs des appareils |
| [PointageBiometriqueService](./services/pointage.md) | Conversion des logs en pointages |
| [Protocols](./services/protocoles.md) | Communication avec différents types d'appareils |
| [MaintenanceService](./services/maintenance.md) | Gestion de la maintenance des appareils biométriques |

## Principes communs

Tous les services du module Biométrie suivent ces principes communs:

1. **Injection de dépendances**: Les services reçoivent leurs dépendances via le constructeur
2. **Journalisation**: Utilisation du système de journalisation de Laravel
3. **Gestion des erreurs**: Utilisation d'exceptions pour signaler les erreurs
4. **Transactions**: Utilisation de transactions pour les opérations critiques
5. **Validation**: Validation des données avant traitement

## Diagramme d'interactions

```
┌─────────────────────┐      ┌─────────────────────┐
│ AppareilBiometrique │◄────►│ AppareilBiometrique │
│      Service        │      │    StatsService     │
└─────────┬───────────┘      └─────────┬───────────┘
          │                            │
          │                            │
          ▼                            ▼
┌─────────────────────┐      ┌─────────────────────┐
│  Protocol Factory   │      │ Synchronisation     │
└─────────┬───────────┘      │ AutomatiqueService  │
          │                  └─────────┬───────────┘
          │                            │
          ▼                            │
┌─────────────────────┐                │
│ Specific Protocol   │                │
│  Implementation     │                │
└─────────┬───────────┘                │
          │                            │
          ▼                            ▼
┌─────────────────────┐      ┌─────────────────────┐
│ Connection Adapter  │      │ LogAppareilBiometri │
└─────────────────────┘      │      queService     │
                             └─────────┬───────────┘
                                       │
                                       ▼
                             ┌─────────────────────┐
                             │ PointageBiometrique │
                             │      Service        │
                             └─────────────────────┘
                             ┌─────────────────────┐
                             │ MaintenanceService  │
                             │      (nouveau)      │
                             └─────────────────────┘
```

## Utilisation des services

Les services sont injectés dans les contrôleurs et autres services via l'injection de dépendances de Laravel:

```php
class AppareilBiometriqueController extends Controller
{
    protected $appareilService;
    protected $statsService;
    protected $maintenanceService;
    
    public function __construct(
        AppareilBiometriqueService $appareilService,
        AppareilBiometriqueStatsService $statsService,
        MaintenanceService $maintenanceService
    ) {
        $this->appareilService = $appareilService;
        $this->statsService = $statsService;
        $this->maintenanceService = $maintenanceService;
    }
    
    // Méthodes du contrôleur...
}
```

## Documentation détaillée

Pour plus de détails sur chaque service, consultez les pages dédiées:

- [Service de synchronisation automatique](./services/synchronisation-automatique.md)
- [Service de gestion des appareils](./services/appareil-biometrique.md)
- [Service de statistiques](./services/statistiques.md)
- [Service de gestion des logs](./services/logs.md)
- [Service de pointage biométrique](./services/pointage.md)
- [Service de maintenance](./services/maintenance.md)
- [Protocoles et adaptateurs](./services/protocoles.md)
