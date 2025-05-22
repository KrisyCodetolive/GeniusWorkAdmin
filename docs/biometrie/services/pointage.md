# Service de Pointage Biométrique

Le service `PointageBiometriqueService` est responsable de la conversion des logs d'appareils biométriques en pointages (entrées/sorties) dans le système de présence de GENIUS WORK.

## Fonctionnalités principales

- Conversion des logs biométriques en pointages
- Validation et vérification des pointages
- Gestion des règles de pointage spécifiques
- Détection des anomalies de pointage
- Génération de rapports de présence

## Interface du service

```php
class PointageBiometriqueService
{
    public function __construct();
    
    public function creerPointageDepuisLog(LogAppareilBiometrique $log): ?Presence;
    public function creerPointage(
        AppareilBiometrique $appareil,
        User $user,
        string $type,
        Carbon $dateHeure,
        array $options = []
    ): Presence;
    
    public function validerPointage(Presence $pointage, ?User $validateur = null): bool;
    public function annulerPointage(Presence $pointage, string $raison, ?User $validateur = null): bool;
    
    public function detecterAnomaliesPointage(User $user, Carbon $debut, Carbon $fin): array;
    public function corrigerAnomaliePointage(array $anomalie, array $correction): bool;
    
    public function getPointagesUtilisateur(User $user, Carbon $debut, Carbon $fin): Collection;
    public function getPointagesSite(Site $site, Carbon $debut, Carbon $fin): Collection;
    public function getPointagesAppareil(AppareilBiometrique $appareil, Carbon $debut, Carbon $fin): Collection;
    
    public function genererRapportPresence(User $user, Carbon $debut, Carbon $fin): array;
    public function genererRapportPresenceDepartement(string $departementId, Carbon $debut, Carbon $fin): array;
}
```

## Méthodes détaillées

### creerPointageDepuisLog

Crée un pointage à partir d'un log d'appareil biométrique.

**Paramètres:**
- `$log` (LogAppareilBiometrique): Log à convertir

**Retourne:**
- `Presence|null`: Instance du pointage créé ou null en cas d'échec

### creerPointage

Crée un pointage manuellement.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$user` (User): Utilisateur concerné
- `$type` (string): Type de pointage (entree, sortie, pause_debut, pause_fin)
- `$dateHeure` (Carbon): Date et heure du pointage
- `$options` (array): Options supplémentaires

**Retourne:**
- `Presence`: Instance du pointage créé

### validerPointage

Valide un pointage.

**Paramètres:**
- `$pointage` (Presence): Pointage à valider
- `$validateur` (User|null): Utilisateur validant le pointage

**Retourne:**
- `bool`: Succès de la validation

### annulerPointage

Annule un pointage.

**Paramètres:**
- `$pointage` (Presence): Pointage à annuler
- `$raison` (string): Raison de l'annulation
- `$validateur` (User|null): Utilisateur annulant le pointage

**Retourne:**
- `bool`: Succès de l'annulation

### detecterAnomaliesPointage

Détecte les anomalies dans les pointages d'un utilisateur sur une période donnée.

**Paramètres:**
- `$user` (User): Utilisateur concerné
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `array`: Liste des anomalies détectées

```php
[
    [
        'type' => 'sortie_manquante',
        'date' => '2025-03-03',
        'entree' => [
            'id' => 'uuid-presence',
            'date_heure' => '2025-03-03 08:30:00'
        ],
        'sortie' => null,
        'severite' => 'error'
    ],
    [
        'type' => 'chevauchement',
        'date' => '2025-03-04',
        'pointages' => [
            [
                'id' => 'uuid-presence-1',
                'type' => 'entree',
                'date_heure' => '2025-03-04 08:30:00'
            ],
            [
                'id' => 'uuid-presence-2',
                'type' => 'entree',
                'date_heure' => '2025-03-04 09:15:00'
            ]
        ],
        'severite' => 'warning'
    ]
]
```

### corrigerAnomaliePointage

Corrige une anomalie de pointage.

**Paramètres:**
- `$anomalie` (array): Anomalie à corriger
- `$correction` (array): Données de correction

**Retourne:**
- `bool`: Succès de la correction

### getPointagesUtilisateur

Récupère les pointages d'un utilisateur sur une période donnée.

**Paramètres:**
- `$user` (User): Utilisateur concerné
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `Collection`: Collection de pointages

### getPointagesSite

Récupère les pointages d'un site sur une période donnée.

**Paramètres:**
- `$site` (Site): Site concerné
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `Collection`: Collection de pointages

### getPointagesAppareil

Récupère les pointages d'un appareil sur une période donnée.

**Paramètres:**
- `$appareil` (AppareilBiometrique): Appareil concerné
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `Collection`: Collection de pointages

### genererRapportPresence

Génère un rapport de présence pour un utilisateur sur une période donnée.

**Paramètres:**
- `$user` (User): Utilisateur concerné
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `array`: Rapport de présence

```php
[
    'user' => [
        'id' => 'uuid-user',
        'nom' => 'Dupont',
        'prenom' => 'Jean'
    ],
    'periode' => [
        'debut' => '2025-03-01',
        'fin' => '2025-03-31'
    ],
    'resume' => [
        'jours_travailles' => 22,
        'heures_travaillees' => 176.5,
        'heures_supplementaires' => 2.5,
        'retards' => 3,
        'absences' => 0
    ],
    'details' => [
        '2025-03-01' => [
            'date' => '2025-03-01',
            'jour_semaine' => 'Lundi',
            'pointages' => [
                [
                    'type' => 'entree',
                    'date_heure' => '2025-03-01 08:30:00',
                    'source' => 'biometrique',
                    'appareil' => 'Pointeuse Entrée'
                ],
                [
                    'type' => 'sortie',
                    'date_heure' => '2025-03-01 17:30:00',
                    'source' => 'biometrique',
                    'appareil' => 'Pointeuse Sortie'
                ]
            ],
            'duree_travail' => 9.0, // heures
            'retard' => false,
            'commentaire' => null
        ],
        // Autres jours...
    ]
]
```

### genererRapportPresenceDepartement

Génère un rapport de présence pour un département sur une période donnée.

**Paramètres:**
- `$departementId` (string): ID du département
- `$debut` (Carbon): Date de début
- `$fin` (Carbon): Date de fin

**Retourne:**
- `array`: Rapport de présence du département

## Conversion des logs en pointages

Le processus de conversion des logs en pointages suit les étapes suivantes:

1. **Identification de l'utilisateur**:
   - Recherche de l'utilisateur correspondant à l'identifiant biométrique
   - Vérification que l'utilisateur est actif et autorisé

2. **Détermination du type de pointage**:
   - Analyse des données du log pour déterminer s'il s'agit d'une entrée ou d'une sortie
   - Vérification de la séquence logique (entrée suivie de sortie)

3. **Création du pointage**:
   - Création d'une nouvelle instance de `Presence`
   - Remplissage des attributs (utilisateur, site, date, type, etc.)
   - Ajout des métadonnées (source, appareil, etc.)

4. **Validation et enregistrement**:
   - Validation des données du pointage
   - Enregistrement en base de données
   - Mise à jour du log pour le marquer comme traité

## Règles de pointage

Le service applique plusieurs règles lors de la création des pointages:

1. **Règle de séquence**: Un utilisateur ne peut pas avoir deux entrées consécutives sans sortie entre les deux
2. **Règle d'intervalle minimum**: Un délai minimum doit être respecté entre deux pointages du même type
3. **Règle de validation**: Certains pointages peuvent nécessiter une validation manuelle selon les paramètres
4. **Règle de site**: Un utilisateur doit être affecté au site pour pouvoir y pointer
5. **Règle d'horaire**: Les pointages en dehors des plages horaires autorisées peuvent être signalés

## Exemples d'utilisation

### Création d'un pointage à partir d'un log

```php
$pointageService = app(PointageBiometriqueService::class);
$log = LogAppareilBiometrique::find('uuid-log');

$pointage = $pointageService->creerPointageDepuisLog($log);

if ($pointage) {
    echo "Pointage créé: " . $pointage->id;
    echo "Type: " . $pointage->type;
    echo "Date: " . $pointage->date_heure;
} else {
    echo "Impossible de créer le pointage";
}
```

### Détection des anomalies

```php
$pointageService = app(PointageBiometriqueService::class);
$user = User::find('uuid-user');

// Détecter les anomalies du mois en cours
$debut = now()->startOfMonth();
$fin = now()->endOfMonth();

$anomalies = $pointageService->detecterAnomaliesPointage($user, $debut, $fin);

echo "Nombre d'anomalies: " . count($anomalies);

foreach ($anomalies as $anomalie) {
    echo "Type: " . $anomalie['type'];
    echo "Date: " . $anomalie['date'];
    echo "Sévérité: " . $anomalie['severite'];
}
```

### Génération d'un rapport de présence

```php
$pointageService = app(PointageBiometriqueService::class);
$user = User::find('uuid-user');

// Rapport du mois précédent
$debut = now()->subMonth()->startOfMonth();
$fin = now()->subMonth()->endOfMonth();

$rapport = $pointageService->genererRapportPresence($user, $debut, $fin);

echo "Jours travaillés: " . $rapport['resume']['jours_travailles'];
echo "Heures travaillées: " . $rapport['resume']['heures_travaillees'];
echo "Retards: " . $rapport['resume']['retards'];
```

## Intégration avec le modèle Presence

Le service utilise le modèle `Presence` pour représenter les pointages. Ce modèle remplace l'ancien modèle `Pointage` et offre des fonctionnalités avancées:

- Gestion des différents types de pointage (entrée, sortie, pause)
- Support de la géolocalisation
- Mécanismes de vérification (photo, signature, QR code, NFC)
- Système de validation
- Calcul des temps de présence

Pour plus de détails sur le modèle `Presence`, consultez la documentation du module Pointage.
