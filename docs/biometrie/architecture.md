# Architecture du Module Biométrie

## Structure générale

Le module Biométrie est organisé selon une architecture en couches, suivant les principes de séparation des responsabilités et d'injection de dépendances.

```
app/
├── Models/
│   ├── AppareilBiometrique.php
│   └── LogAppareilBiometrique.php
├── Services/
│   └── Biometrique/
│       ├── AppareilBiometriqueService.php
│       ├── AppareilBiometriqueStatsService.php
│       ├── LogAppareilBiometriqueService.php
│       ├── PointageBiometriqueService.php
│       ├── SynchronisationAutomatiqueService.php
│       └── Protocols/
│           ├── AbstractBiometriqueProtocol.php
│           ├── ProtocolFactory.php
│           ├── Adapters/
│           │   ├── HttpAdapter.php
│           │   └── SocketAdapter.php
│           ├── Interfaces/
│           │   ├── BiometriqueProtocolInterface.php
│           │   └── ConnectionAdapterInterface.php
│           └── Implementations/
│               ├── AnvizProtocol.php
│               ├── GenericHttpProtocol.php
│               ├── HikVisionProtocol.php
│               └── ZKTecoProtocol.php
├── Http/
│   └── Controllers/
│       └── Biometrique/
│           ├── AppareilBiometriqueController.php
│           ├── DashboardBiometriqueController.php
│           ├── LogAppareilBiometriqueController.php
│           ├── PointageBiometriqueController.php
│           └── UserBiometriqueController.php
├── Console/
│   └── Commands/
│       └── SynchroniserAppareilsBiometriques.php
└── resources/
    └── views/
        └── biometrique/
            ├── appareils/
            ├── dashboard/
            └── components/
```

## Flux de données

1. **Interface utilisateur** → Les utilisateurs interagissent avec les vues pour gérer les appareils
2. **Contrôleurs** → Traitent les requêtes et délèguent aux services
3. **Services** → Contiennent la logique métier et interagissent avec les modèles
4. **Protocoles** → Gèrent la communication avec les appareils physiques
5. **Modèles** → Représentent les données en base et leurs relations

## Principes architecturaux

### Injection de dépendances

Tous les services et contrôleurs utilisent l'injection de dépendances pour faciliter les tests et réduire le couplage.

### Abstraction des protocoles

Le système utilise une abstraction des protocoles de communication pour supporter différents fabricants d'appareils biométriques:

- Interface commune (`BiometriqueProtocolInterface`)
- Implémentations spécifiques par fabricant
- Factory pour instancier le protocole approprié

### Séparation des responsabilités

- **Contrôleurs**: Gestion des requêtes HTTP
- **Services**: Logique métier
- **Modèles**: Accès aux données
- **Protocoles**: Communication avec les appareils

## Intégration avec d'autres modules

- **Module de Pointage**: Utilisation des données biométriques pour le suivi de présence
- **Module Utilisateurs**: Gestion des utilisateurs enregistrés sur les appareils
- **Module Sites**: Association des appareils aux sites physiques

## Sécurité

- Authentification requise pour toutes les opérations
- Permissions granulaires via le système de rôles
- Validation des entrées utilisateur
- Journalisation des opérations sensibles
