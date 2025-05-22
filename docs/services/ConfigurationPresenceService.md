# Documentation du Service de Configuration de Présence (ConfigurationPresenceService)

## Introduction

Le `ConfigurationPresenceService` est un composant essentiel du système de gestion des présences dans l'application GENIUS WORK. Il gère les configurations et paramètres liés aux présences, absences, retards et notifications pour chaque entreprise. Ce service permet de personnaliser le comportement du système de pointage selon les besoins spécifiques de chaque entreprise.

## Dépendances

Le service dépend des éléments suivants :

- **Modèles** : ConfigurationPresence, Entreprise, Presence, User, Politique
- **Carbon** : Pour la gestion des dates et heures
- **Laravel Log** : Pour la journalisation des événements

## Fonctionnalités principales

### 1. Gestion des configurations

Le service permet de :

- Récupérer la configuration de présence d'une entreprise
- Créer une configuration par défaut si elle n'existe pas
- Mettre à jour les paramètres de configuration
- Récupérer des paramètres spécifiques (heures supplémentaires, pauses, etc.)

### 2. Gestion des notifications

Le service vérifie si les notifications sont activées pour :

- Les retards
- Les absences
- Les sorties manquantes
- Les congés

Il permet également de récupérer les messages personnalisés pour chaque type de notification et de vérifier les canaux de communication activés (email, SMS).

### 3. Gestion des tolérances

Le service fournit des méthodes pour récupérer les tolérances configurées pour :

- Les retards
- Les absences
- Les sorties manquantes

### 4. Gestion des politiques d'entreprise

Le service permet de :

- Récupérer la politique d'une entreprise
- Créer une politique par défaut si elle n'existe pas

### 5. Automatisation

Le service inclut des fonctionnalités d'automatisation comme :

- La vérification et l'annulation des présences sans pointage de sortie après un délai configurable

## Méthodes principales

### Méthodes de configuration

#### `getConfiguration($entreprise)`

Récupère la configuration de présence pour une entreprise. Si aucune configuration n'existe, en crée une par défaut.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise

**Retourne :**
- Instance de ConfigurationPresence

#### `creerConfigurationParDefaut($entrepriseId)`

Crée une configuration de présence par défaut pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Instance de ConfigurationPresence nouvellement créée

#### `updateConfiguration($entreprise, array $data)`

Met à jour la configuration de présence pour une entreprise.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise
- `$data` : Tableau des données à mettre à jour

**Retourne :**
- Instance de ConfigurationPresence mise à jour

### Méthodes d'accès aux paramètres

#### `heuresSupplementairesActives($entreprise)`

Vérifie si les heures supplémentaires sont activées pour une entreprise.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise

**Retourne :**
- Booléen indiquant si les heures supplémentaires sont activées

#### `pausesActives($entreprise)`

Vérifie si les pauses sont activées pour une entreprise.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise

**Retourne :**
- Booléen indiquant si les pauses sont activées

#### `getNombrePointagesParJour($entreprise)`

Obtient le nombre de pointages par jour configuré pour une entreprise.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise

**Retourne :**
- Entier représentant le nombre de pointages par jour

#### `annulerPresenceSansSortie($entreprise)`

Vérifie si l'annulation des présences sans pointage de sortie est activée.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise

**Retourne :**
- Booléen indiquant si l'annulation est activée

#### `getDelaiAnnulationHeures($entreprise)`

Obtient le délai d'annulation des présences sans pointage de sortie en heures.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise

**Retourne :**
- Entier représentant le délai en heures

### Méthodes de gestion des notifications

#### `notificationsActives($entreprise, $type = null)`

Vérifie si les notifications sont activées pour une entreprise et éventuellement pour un type spécifique.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise
- `$type` : Type de notification (absence, retard, conge) ou null pour toutes

**Retourne :**
- Booléen indiquant si les notifications sont activées

#### `getMessageNotification($entreprise, $type)`

Obtient le message personnalisé pour un type de notification.

**Paramètres :**
- `$entreprise` : Instance d'Entreprise ou ID d'entreprise
- `$type` : Type de notification (absence, retard, conge)

**Retourne :**
- Chaîne de caractères contenant le message ou null

#### `envoyerNotification(User $user, $type, array $data = [])`

Envoie une notification selon le type spécifié.

**Paramètres :**
- `$user` : Instance de User
- `$type` : Type de notification (absence, retard, conge)
- `$data` : Données supplémentaires pour la notification

**Retourne :**
- Booléen indiquant si l'envoi a réussi

### Méthodes spécifiques aux notifications

#### `notificationsActivees($entrepriseId)`

Vérifie si les notifications sont globalement activées pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Booléen indiquant si les notifications sont activées

#### `notificationsRetardActivees($entrepriseId)`

Vérifie si les notifications de retard sont activées pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Booléen indiquant si les notifications de retard sont activées

#### `notificationsAbsenceActivees($entrepriseId)`

Vérifie si les notifications d'absence sont activées pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Booléen indiquant si les notifications d'absence sont activées

#### `notificationsSortieManquanteActivees($entrepriseId)`

Vérifie si les notifications de sortie manquante sont activées pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Booléen indiquant si les notifications de sortie manquante sont activées

### Méthodes de gestion des tolérances

#### `getToleranceRetard($entrepriseId)`

Récupère la tolérance de retard en minutes pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Entier représentant la tolérance en minutes

#### `getToleranceAbsence($entrepriseId)`

Récupère la tolérance d'absence en minutes pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Entier représentant la tolérance en minutes

#### `getToleranceSortieManquante($entrepriseId)`

Récupère la tolérance de sortie manquante en minutes pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Entier représentant la tolérance en minutes

### Méthodes de gestion des canaux de notification

#### `notificationsEmailActivees($entrepriseId)`

Vérifie si les notifications par email sont activées pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Booléen indiquant si les notifications par email sont activées

#### `notificationsSmsActivees($entrepriseId)`

Vérifie si les notifications par SMS sont activées pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Booléen indiquant si les notifications par SMS sont activées

### Méthodes de gestion des messages

#### `getMessageRetard($entrepriseId)`

Récupère le message de retard personnalisé pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Chaîne de caractères contenant le message ou null

#### `getMessageAbsence($entrepriseId)`

Récupère le message d'absence personnalisé pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Chaîne de caractères contenant le message ou null

#### `getMessageSortieManquante($entrepriseId)`

Récupère le message de sortie manquante personnalisé pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Chaîne de caractères contenant le message ou null

### Méthodes d'automatisation

#### `verifierEtAnnulerPresencesSansSortie()`

Vérifie et annule les présences sans pointage de sortie selon les configurations des entreprises.

**Retourne :**
- Entier représentant le nombre de présences annulées

### Méthodes de gestion des politiques

#### `getConfigurationForEntreprise($entrepriseId)`

Récupère la configuration de présence pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Instance de ConfigurationPresence ou null

#### `getPolitiqueForEntreprise($entrepriseId)`

Récupère la politique d'entreprise. Si aucune politique n'existe, en crée une par défaut.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Instance de Politique

#### `creerPolitiqueParDefaut($entrepriseId)`

Crée une politique par défaut pour une entreprise.

**Paramètres :**
- `$entrepriseId` : ID de l'entreprise

**Retourne :**
- Instance de Politique nouvellement créée

## Configuration

Le service utilise le modèle `ConfigurationPresence` pour stocker les paramètres de configuration pour chaque entreprise. Les paramètres incluent :

- Activation des heures supplémentaires
- Nombre de pointages par jour
- Activation des pauses
- Annulation des présences sans sortie
- Délai d'annulation des heures
- Activation des notifications (globales et par type)
- Messages personnalisés pour chaque type de notification

## Intégration avec d'autres services

Le `ConfigurationPresenceService` est utilisé par :

- **NotificationService** : Pour vérifier si les notifications sont activées et récupérer les messages personnalisés
- **RetardAbsenceService** : Pour récupérer les tolérances de retard, d'absence et de sortie manquante
- **PointageService** : Pour vérifier les configurations liées aux pointages (nombre par jour, pauses, etc.)

## Exemple d'utilisation

```php
// Exemple d'utilisation dans un contrôleur ou un service
$configService = app(ConfigurationPresenceService::class);

// Récupérer la configuration d'une entreprise
$config = $configService->getConfiguration($entrepriseId);

// Vérifier si les notifications de retard sont activées
if ($configService->notificationsRetardActivees($entrepriseId)) {
    // Envoyer une notification de retard
    $notificationService->notifierRetard($employeur, [
        'minutes' => 15,
        'date' => now()->format('d/m/Y'),
    ]);
}

// Récupérer la tolérance de retard
$toleranceRetard = $configService->getToleranceRetard($entrepriseId);

// Mettre à jour la configuration
$configService->updateConfiguration($entrepriseId, [
    'notifier_retards' => true,
    'tolerance_retard_minutes' => 10,
    'message_retard' => 'Attention, vous êtes en retard de {minutes} minutes.'
]);
```

## Sécurité et validation

Le service implémente plusieurs mesures de sécurité et de validation :

1. Vérification de l'existence des configurations avant utilisation
2. Création automatique de configurations par défaut pour éviter les erreurs
3. Journalisation des actions importantes comme l'annulation automatique des présences

## Journalisation

Le service utilise le système de journalisation de Laravel pour enregistrer :

1. Les présences annulées automatiquement
2. Les notifications envoyées
3. Les erreurs lors de l'envoi des notifications

## Conclusion

Le `ConfigurationPresenceService` est un composant central du système de gestion des présences, permettant de personnaliser le comportement du système selon les besoins de chaque entreprise. Sa conception modulaire facilite l'intégration avec d'autres services et offre une grande flexibilité dans la configuration des règles de présence et de notification.
