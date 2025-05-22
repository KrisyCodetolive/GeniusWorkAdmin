# Service de Maintenance des Appareils Biométriques

Le service `MaintenanceAppareilBiometriqueService` est responsable de la gestion des opérations de maintenance, de diagnostic et de mise à jour des appareils biométriques.

## Fonctionnalités principales

- Diagnostic des appareils biométriques
- Planification et suivi des opérations de maintenance
- Gestion des mises à jour du firmware
- Surveillance de l'état des appareils
- Génération de rapports de maintenance

## Interface du service

```php
class MaintenanceAppareilBiometriqueService
{
    public function __construct(
        AppareilBiometriqueService $appareilService,
        LogAppareilBiometriqueService $logService
    );
    
    public function diagnostiquerAppareil(AppareilBiometrique $appareil): array;
    public function verifierConnectivite(AppareilBiometrique $appareil): bool;
    public function verifierEspaceDIsque(AppareilBiometrique $appareil): array;
    public function verifierVersionFirmware(AppareilBiometrique $appareil): array;
    
    public function planifierMaintenance(
        AppareilBiometrique $appareil, 
        string $type, 
        Carbon $dateDebut, 
        ?Carbon $dateFin = null, 
        array $options = []
    ): MaintenanceAppareilBiometrique;
    
    public function executerMaintenance(MaintenanceAppareilBiometrique $maintenance): bool;
    public function annulerMaintenance(MaintenanceAppareilBiometrique $maintenance, string $raison): bool;
    
    public function mettreAJourFirmware(
        AppareilBiometrique $appareil, 
        string $versionCible, 
        bool $sauvegarderAvant = true
    ): bool;
    
    public function sauvegarderConfiguration(AppareilBiometrique $appareil, string $description = ''): string;
    public function restaurerConfiguration(AppareilBiometrique $appareil, string $sauvegardeId): bool;
    
    public function genererRapportMaintenance(AppareilBiometrique $appareil, Carbon $debut, Carbon $fin): array;
    public function genererRapportEtatParc(?string $siteId = null): array;
}
```

## Méthodes détaillées

### diagnostiquerAppareil

Effectue un diagnostic complet d'un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil à diagnostiquer

**Retourne:**
- `array`: Résultats du diagnostic

```php
[
    'statut_general' => 'ok', // ok, warning, error
    'connectivite' => [
        'statut' => 'ok',
        'latence' => 15, // ms
        'derniere_connexion' => '2025-03-04 08:30:00'
    ],
    'stockage' => [
        'statut' => 'warning',
        'espace_total' => 1024, // MB
        'espace_utilise' => 820, // MB
        'pourcentage_utilisation' => 80,
        'details' => [
            'logs' => 500, // MB
            'utilisateurs' => 300, // MB
            'firmware' => 20, // MB
        ]
    ],
    'firmware' => [
        'statut' => 'ok',
        'version_actuelle' => '3.2.1',
        'version_disponible' => '3.2.1',
        'mise_a_jour_necessaire' => false
    ],
    'capteurs' => [
        'statut' => 'ok',
        'empreinte' => [
            'statut' => 'ok',
            'qualite' => 95 // %
        ],
        'camera' => [
            'statut' => 'ok',
            'qualite' => 90 // %
        ]
    ],
    'memoire' => [
        'statut' => 'ok',
        'utilisation' => 45 // %
    ],
    'batterie' => [
        'statut' => 'ok',
        'niveau' => 80, // %
        'autonomie_estimee' => 120 // minutes
    ],
    'temperature' => [
        'statut' => 'ok',
        'valeur' => 35 // °C
    ]
]
```

### verifierConnectivite

Vérifie la connectivité d'un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil à vérifier

**Retourne:**
- `bool`: Statut de la connectivité

### verifierEspaceDIsque

Vérifie l'espace disque d'un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil à vérifier

**Retourne:**
- `array`: Informations sur l'espace disque

```php
[
    'statut' => 'warning', // ok, warning, error
    'espace_total' => 1024, // MB
    'espace_utilise' => 820, // MB
    'espace_libre' => 204, // MB
    'pourcentage_utilisation' => 80,
    'details' => [
        'logs' => 500, // MB
        'utilisateurs' => 300, // MB
        'firmware' => 20, // MB
    ]
]
```

### verifierVersionFirmware

Vérifie la version du firmware d'un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil à vérifier

**Retourne:**
- `array`: Informations sur le firmware

```php
[
    'statut' => 'ok', // ok, warning, error
    'version_actuelle' => '3.2.1',
    'version_disponible' => '3.2.1',
    'mise_a_jour_necessaire' => false,
    'notes_version' => 'Correction de bugs et amélioration des performances'
]
```

### planifierMaintenance

Planifie une opération de maintenance pour un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$type` (string): Type de maintenance (preventive, corrective, mise_a_jour)
- `$dateDebut` (Carbon): Date de début de la maintenance
- `$dateFin` (Carbon|null): Date de fin prévue (optionnel)
- `$options` (array): Options supplémentaires

**Retourne:**
- `MaintenanceAppareilBiometrique`: Instance de la maintenance planifiée

### executerMaintenance

Exécute une opération de maintenance planifiée.

**Paramètres:**
- `$maintenance` (MaintenanceAppareilBiometrique): Maintenance à exécuter

**Retourne:**
- `bool`: Succès de l'exécution

### annulerMaintenance

Annule une opération de maintenance planifiée.

**Paramètres:**
- `$maintenance` (MaintenanceAppareilBiometrique): Maintenance à annuler
- `$raison` (string): Raison de l'annulation

**Retourne:**
- `bool`: Succès de l'annulation

### mettreAJourFirmware

Met à jour le firmware d'un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil à mettre à jour
- `$versionCible` (string): Version cible du firmware
- `$sauvegarderAvant` (bool): Indique s'il faut sauvegarder la configuration avant la mise à jour

**Retourne:**
- `bool`: Succès de la mise à jour

### sauvegarderConfiguration

Sauvegarde la configuration d'un appareil biométrique.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$description` (string): Description de la sauvegarde

**Retourne:**
- `string`: Identifiant de la sauvegarde

### restaurerConfiguration

Restaure la configuration d'un appareil biométrique à partir d'une sauvegarde.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$sauvegardeId` (string): Identifiant de la sauvegarde

**Retourne:**
- `bool`: Succès de la restauration

### genererRapportMaintenance

Génère un rapport de maintenance pour un appareil sur une période donnée.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `array`: Rapport de maintenance

```php
[
    'appareil' => [
        'id' => 'uuid-appareil',
        'nom' => 'Pointeuse Entrée',
        'modele' => 'ZKTeco F18',
        'site' => 'Siège Social'
    ],
    'periode' => [
        'debut' => '2025-01-01',
        'fin' => '2025-03-31'
    ],
    'resume' => [
        'maintenances_preventives' => 2,
        'maintenances_correctives' => 1,
        'mises_a_jour' => 1,
        'temps_indisponibilite' => 180, // minutes
        'taux_disponibilite' => 99.5 // %
    ],
    'maintenances' => [
        [
            'id' => 'uuid-maintenance-1',
            'type' => 'preventive',
            'date_debut' => '2025-01-15 09:00:00',
            'date_fin' => '2025-01-15 11:00:00',
            'duree' => 120, // minutes
            'statut' => 'terminee',
            'technicien' => 'Jean Dupont',
            'actions' => [
                'Nettoyage des capteurs',
                'Vérification des connexions',
                'Test des fonctionnalités'
            ]
        ],
        // Autres maintenances...
    ],
    'incidents' => [
        [
            'id' => 'uuid-incident-1',
            'date' => '2025-02-10 14:30:00',
            'description' => 'Erreur de connexion réseau',
            'impact' => 'moyen',
            'duree' => 60, // minutes
            'resolution' => 'Redémarrage du routeur et reconfiguration'
        ],
        // Autres incidents...
    ]
]
```

### genererRapportEtatParc

Génère un rapport sur l'état du parc d'appareils biométriques.

**Paramètres:**
- `$siteId` (string|null): ID du site (optionnel)

**Retourne:**
- `array`: Rapport d'état du parc

```php
[
    'resume' => [
        'total_appareils' => 15,
        'appareils_actifs' => 14,
        'appareils_inactifs' => 1,
        'taux_disponibilite' => 98.5, // %
        'age_moyen' => 18 // mois
    ],
    'par_site' => [
        [
            'site' => 'Siège Social',
            'total_appareils' => 8,
            'appareils_actifs' => 8,
            'taux_disponibilite' => 99.8, // %
        ],
        // Autres sites...
    ],
    'par_modele' => [
        [
            'modele' => 'ZKTeco F18',
            'total_appareils' => 10,
            'appareils_actifs' => 9,
            'taux_disponibilite' => 98.2, // %
        ],
        // Autres modèles...
    ],
    'appareils_problematiques' => [
        [
            'id' => 'uuid-appareil-1',
            'nom' => 'Pointeuse Atelier',
            'site' => 'Usine Nord',
            'probleme' => 'Connectivité intermittente',
            'depuis' => '2025-03-01',
            'statut' => 'En attente de maintenance'
        ],
        // Autres appareils problématiques...
    ]
]
```

## Modèle de données pour la maintenance

Le service utilise le modèle `MaintenanceAppareilBiometrique` pour représenter les opérations de maintenance:

```php
class MaintenanceAppareilBiometrique extends Model
{
    use HasUuids, SoftDeletes;
    
    protected $fillable = [
        'appareil_biometrique_id',
        'type', // preventive, corrective, mise_a_jour
        'date_debut',
        'date_fin_prevue',
        'date_fin_reelle',
        'statut', // planifiee, en_cours, terminee, annulee
        'description',
        'technicien_id',
        'actions',
        'resultats',
        'commentaire'
    ];
    
    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin_prevue' => 'datetime',
        'date_fin_reelle' => 'datetime',
        'actions' => 'array',
        'resultats' => 'array'
    ];
    
    // Relations
    public function appareil() { ... }
    public function technicien() { ... }
    public function logs() { ... }
}
```

## Exemples d'utilisation

### Diagnostic d'un appareil

```php
$maintenanceService = app(MaintenanceAppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

$diagnostic = $maintenanceService->diagnostiquerAppareil($appareil);

echo "Statut général: " . $diagnostic['statut_general'];

if ($diagnostic['statut_general'] !== 'ok') {
    foreach ($diagnostic as $categorie => $details) {
        if (is_array($details) && isset($details['statut']) && $details['statut'] !== 'ok') {
            echo "Problème détecté dans la catégorie: " . $categorie;
            echo "Statut: " . $details['statut'];
        }
    }
}
```

### Planification d'une maintenance

```php
$maintenanceService = app(MaintenanceAppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

$maintenance = $maintenanceService->planifierMaintenance(
    $appareil,
    'preventive',
    now()->addDays(3)->setHour(9)->setMinute(0),
    now()->addDays(3)->setHour(11)->setMinute(0),
    [
        'description' => 'Maintenance préventive trimestrielle',
        'actions' => [
            'Nettoyage des capteurs',
            'Vérification des connexions',
            'Test des fonctionnalités'
        ],
        'technicien_id' => 'uuid-technicien'
    ]
);

echo "Maintenance planifiée: " . $maintenance->id;
echo "Date: " . $maintenance->date_debut->format('d/m/Y H:i');
```

### Mise à jour du firmware

```php
$maintenanceService = app(MaintenanceAppareilBiometriqueService::class);
$appareil = AppareilBiometrique::find('uuid-appareil');

// Vérifier la version du firmware
$infoFirmware = $maintenanceService->verifierVersionFirmware($appareil);

if ($infoFirmware['mise_a_jour_necessaire']) {
    echo "Mise à jour disponible: " . $infoFirmware['version_disponible'];
    
    // Effectuer la mise à jour
    $resultat = $maintenanceService->mettreAJourFirmware(
        $appareil,
        $infoFirmware['version_disponible'],
        true // Sauvegarder avant la mise à jour
    );
    
    if ($resultat) {
        echo "Mise à jour réussie";
    } else {
        echo "Échec de la mise à jour";
    }
} else {
    echo "Le firmware est à jour";
}
```

### Génération d'un rapport d'état du parc

```php
$maintenanceService = app(MaintenanceAppareilBiometriqueService::class);

// Rapport pour tous les sites
$rapport = $maintenanceService->genererRapportEtatParc();

echo "Total des appareils: " . $rapport['resume']['total_appareils'];
echo "Taux de disponibilité: " . $rapport['resume']['taux_disponibilite'] . "%";

// Afficher les appareils problématiques
if (count($rapport['appareils_problematiques']) > 0) {
    echo "Appareils nécessitant une attention:";
    
    foreach ($rapport['appareils_problematiques'] as $appareil) {
        echo "- " . $appareil['nom'] . " (" . $appareil['site'] . "): " . $appareil['probleme'];
    }
}
```

## Intégration avec d'autres services

Le service de maintenance est utilisé par:

1. **AppareilBiometriqueService**: Pour effectuer des diagnostics lors des opérations sur les appareils
2. **SynchronisationAutomatiqueService**: Pour vérifier l'état des appareils avant la synchronisation
3. **DashboardBiometriqueController**: Pour afficher l'état du parc dans le tableau de bord
4. **MaintenanceAppareilBiometriqueController**: Pour l'interface de gestion des maintenances
