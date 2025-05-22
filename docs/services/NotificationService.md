# Documentation du Service de Notification (NotificationService)

## Introduction

Le `NotificationService` est un composant central du système de gestion des présences dans l'application GENIUS WORK. Il est responsable de l'envoi des notifications aux employeurs concernant différents événements liés à la présence, comme les retards, les absences, les congés et les sorties manquantes.

Ce service a été mis à jour pour utiliser le modèle `Employeur` au lieu du modèle `User`, conformément aux récentes modifications apportées au système.

## Dépendances

Le service dépend des éléments suivants :

- **ConfigurationPresenceService** : Pour vérifier si les notifications sont activées pour une entreprise donnée et pour récupérer les paramètres de configuration.
- **Laravel Mail** : Pour l'envoi des notifications par email.
- **Twilio/Vonage** : Pour l'envoi des notifications par SMS (si configuré).
- **Modèles** : Employeur, Entreprise, Notification, ConfigurationPresence.

## Fonctionnalités principales

### 1. Types de notifications

Le service prend en charge plusieurs types de notifications :

- **Retard** : Lorsqu'un employeur arrive en retard par rapport à sa plage horaire prévue.
- **Absence** : Lorsqu'un employeur est absent pendant une plage horaire prévue.
- **Sortie manquante** : Lorsqu'un employeur a pointé son entrée mais n'a pas pointé sa sortie.
- **Congé** : Notifications liées aux demandes de congé (approbation, refus, etc.).
- **Notifications personnalisées** : Possibilité d'envoyer des notifications personnalisées avec des données spécifiques.

### 2. Canaux de notification

Le service peut envoyer des notifications via plusieurs canaux :

- **Application** : Stockage des notifications dans la base de données pour affichage dans l'application.
- **Email** : Envoi de notifications par email si l'adresse email de l'employeur est disponible.
- **SMS** : Envoi de notifications par SMS si le numéro de téléphone de l'employeur est disponible et si le service SMS est configuré.

### 3. Gestion des notifications

Le service offre des fonctionnalités pour :

- Créer et enregistrer des notifications dans la base de données.
- Mettre à jour le statut des notifications (envoyée, lue, etc.).
- Récupérer l'historique des notifications pour un employeur ou une entreprise.
- Générer des statistiques sur les notifications.

## Méthodes principales

### Méthodes de notification spécifiques

#### `notifierRetard(Employeur $employeur, array $data = [])`

Envoie une notification de retard à un employeur. Vérifie d'abord si les notifications de retard sont activées pour l'entreprise de l'employeur.

#### `notifierAbsence(Employeur $employeur, array $data = [])`

Envoie une notification d'absence à un employeur. Vérifie d'abord si les notifications d'absence sont activées pour l'entreprise de l'employeur.

#### `notifierConge(Employeur $employeur, array $congeData, array $data = [])`

Envoie une notification de congé à un employeur. Vérifie d'abord si les notifications de congé sont activées pour l'entreprise de l'employeur.

#### `notifierSortieManquante($employeur, $entreprise, $plageHoraire, $presence)`

Envoie une notification de sortie manquante à un employeur. Vérifie d'abord si les notifications de sorties manquantes sont activées pour l'entreprise.

### Méthodes génériques d'envoi

#### `envoyerNotification(Employeur $employeur, string $type, array $data = [])`

Méthode générique pour envoyer une notification à un employeur. Cette méthode :
1. Récupère le message personnalisé pour le type de notification.
2. Enregistre la notification dans la base de données.
3. Envoie la notification par email si l'adresse email est disponible.
4. Envoie la notification par SMS si le numéro de téléphone est disponible.

#### `envoyerNotificationEmail(Employeur $employeur, string $type, string $message, array $data = [])`

Envoie un email à l'employeur avec le message de notification. Utilise Laravel Mail avec une vue Blade.

#### `envoyerSMSEmployeur(Employeur $employeur, string $message)`

Envoie un SMS à l'employeur avec le message de notification. Peut utiliser Twilio ou Vonage selon la configuration.

### Méthodes de gestion des notifications

#### `creerNotification($employeurId, $entrepriseId, $type, $message, $data = [], $canal = 'app', $priorite = 'normale')`

Crée une nouvelle notification dans la base de données avec les informations spécifiées.

#### `marquerCommeLue($notificationId)`

Marque une notification comme lue dans la base de données.

#### `getHistoriqueNotifications($employeurId, $limit = 50, $offset = 0)`

Récupère l'historique des notifications pour un employeur spécifique avec pagination.

#### `getHistoriqueNotificationsEntreprise($entrepriseId, $limit = 100, $offset = 0)`

Récupère l'historique des notifications pour tous les employeurs d'une entreprise avec pagination.

#### `getStatistiquesNotifications($entrepriseId, $dateDebut = null, $dateFin = null)`

Génère des statistiques sur les notifications pour une entreprise sur une période donnée.

### Méthodes utilitaires

#### `getMessagePersonnalise($entrepriseId, $type, $variables = [])`

Récupère et personnalise un message de notification en fonction du type et des variables fournies. Utilise les modèles de message configurés pour l'entreprise ou des messages par défaut.

#### `getMessageParDefaut($type)`

Récupère un message par défaut pour un type de notification spécifique.

## Configuration

Le service utilise la configuration de présence de l'entreprise pour déterminer :

1. Si les notifications sont activées pour chaque type (retard, absence, sortie manquante, etc.).
2. Les canaux à utiliser pour l'envoi des notifications (email, SMS, application).
3. Les modèles de message personnalisés pour chaque type de notification.

## Intégration avec d'autres services

Le `NotificationService` est utilisé par :

- **RetardAbsenceService** : Pour notifier les employeurs des retards, absences et sorties manquantes détectés.
- **Commands** : Les commandes comme `EnvoyerNotificationsPresence` et `VerifierSortiesManquantes` utilisent ce service pour envoyer des notifications basées sur les vérifications de présence.

## Exemple d'utilisation

```php
// Exemple d'utilisation dans une commande ou un contrôleur
$notificationService = app(NotificationService::class);

// Notifier un retard
$notificationService->notifierRetard($employeur, [
    'minutes' => 15,
    'date' => now()->format('d/m/Y'),
]);

// Notifier une absence
$notificationService->notifierAbsence($employeur, [
    'date' => now()->format('d/m/Y'),
    'motif' => 'Non justifiée',
]);

// Notifier une sortie manquante
$notificationService->notifierSortieManquante(
    $employeur,
    $entreprise,
    $plageHoraire,
    $presence
);
```

## Sécurité et validation

Le service implémente plusieurs mesures de sécurité et de validation :

1. Validation des adresses email avant l'envoi des notifications par email.
2. Nettoyage des numéros de téléphone avant l'envoi des SMS.
3. Journalisation des erreurs et des avertissements pour les tentatives d'envoi échouées.
4. Vérification de la configuration avant l'envoi des notifications pour éviter les envois non désirés.

## Journalisation

Le service utilise le système de journalisation de Laravel pour enregistrer :

1. Les notifications créées et envoyées.
2. Les erreurs lors de l'envoi des notifications.
3. Les avertissements pour les adresses email ou numéros de téléphone invalides.

## Conclusion

Le `NotificationService` est un composant essentiel du système de gestion des présences, permettant de tenir les employeurs informés des événements importants liés à leur présence. Sa conception modulaire et flexible permet une intégration facile avec d'autres services et une personnalisation des notifications selon les besoins de chaque entreprise.
