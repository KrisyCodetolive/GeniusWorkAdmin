# RetardAbsenceService

## Description

Le `RetardAbsenceService` est un service central dans l'application GENIUS WORK qui gère la détection et le traitement des retards, absences et sorties manquantes pour les employeurs. Il s'intègre avec d'autres services comme `WebPointageService` et `NotificationService` pour fournir une solution complète de suivi de présence.

## Fonctionnalités

- Détection automatique des retards
- Détection automatique des absences
- Détection automatique des sorties manquantes
- Envoi de notifications pour chaque type d'événement
- Vérification des jours de travail et des congés
- Génération de statistiques sur les événements détectés

## Dépendances

Le service dépend des éléments suivants :

- **WebPointageService** : Pour récupérer les plages horaires et déterminer les horaires de travail
- **NotificationService** : Pour envoyer des notifications aux employeurs
- **ConfigurationPresenceService** : Pour vérifier si les notifications sont activées

## Modèles utilisés

- **Employeur** : Entité principale ciblée par le service
- **Presence** : Enregistrements de pointage
- **Notification** : Pour stocker et gérer les notifications envoyées
- **PlageHoraire** : Pour déterminer les horaires de travail

## Méthodes principales

### verifierRetards

```php
public function verifierRetards(?Carbon $date = null): array
```

Vérifie et traite les retards pour tous les employeurs actifs.

**Paramètres :**
- `$date` (Carbon|null) : Date à vérifier (aujourd'hui par défaut)

**Retourne :**
- `array` : Statistiques des retards traités
  - `total_employeurs` : Nombre total d'employeurs vérifiés
  - `retards_detectes` : Nombre de retards détectés
  - `notifications_envoyees` : Nombre de notifications envoyées
  - `erreurs` : Nombre d'erreurs rencontrées

**Processus :**
1. Récupère tous les employeurs actifs
2. Pour chaque employeur :
   - Vérifie si les notifications de retard sont activées
   - Vérifie si l'employeur doit travailler ce jour-là
   - Vérifie si l'employeur est en congé
   - Récupère les pointages de l'employeur pour la date spécifiée
   - Détecte les pointages avec retard
   - Envoie une notification si nécessaire

### verifierAbsences

```php
public function verifierAbsences(?Carbon $date = null): array
```

Vérifie et traite les absences pour tous les employeurs actifs.

**Paramètres :**
- `$date` (Carbon|null) : Date à vérifier (aujourd'hui par défaut)

**Retourne :**
- `array` : Statistiques des absences traitées
  - `total_employeurs` : Nombre total d'employeurs vérifiés
  - `absences_detectees` : Nombre d'absences détectées
  - `notifications_envoyees` : Nombre de notifications envoyées
  - `erreurs` : Nombre d'erreurs rencontrées

**Processus :**
1. Récupère tous les employeurs actifs
2. Pour chaque employeur :
   - Vérifie si les notifications d'absence sont activées
   - Vérifie si l'employeur doit travailler ce jour-là
   - Vérifie si l'employeur est en congé
   - Vérifie si l'employeur a pointé pour la date spécifiée
   - Si non, vérifie si l'heure actuelle dépasse l'heure limite d'absence
   - Envoie une notification si nécessaire

### verifierSortiesManquantes

```php
public function verifierSortiesManquantes(?Carbon $date = null): array
```

Vérifie et traite les sorties manquantes pour tous les employeurs actifs.

**Paramètres :**
- `$date` (Carbon|null) : Date à vérifier (aujourd'hui par défaut)

**Retourne :**
- `array` : Statistiques des sorties manquantes traitées
  - `total_employeurs` : Nombre total d'employeurs vérifiés
  - `sorties_manquantes` : Nombre de sorties manquantes détectées
  - `notifications_envoyees` : Nombre de notifications envoyées
  - `erreurs` : Nombre d'erreurs rencontrées

**Processus :**
1. Récupère tous les employeurs actifs
2. Pour chaque employeur :
   - Vérifie si les notifications de sortie manquante sont activées
   - Vérifie si l'employeur doit travailler ce jour-là
   - Vérifie si l'employeur est en congé
   - Récupère les pointages de l'employeur pour la date spécifiée
   - Vérifie si le dernier pointage est une entrée ou une fin de pause
   - Vérifie si l'heure actuelle dépasse l'heure de fin de travail + marge
   - Envoie une notification si nécessaire

### executerToutesVerifications

```php
public function executerToutesVerifications(?Carbon $date = null): array
```

Exécute toutes les vérifications (retards, absences, sorties manquantes).

**Paramètres :**
- `$date` (Carbon|null) : Date à vérifier (aujourd'hui par défaut)

**Retourne :**
- `array` : Statistiques combinées
  - `date` : Date de vérification
  - `retards` : Statistiques des retards
  - `absences` : Statistiques des absences
  - `sorties_manquantes` : Statistiques des sorties manquantes
  - `total_notifications` : Nombre total de notifications envoyées
  - `total_erreurs` : Nombre total d'erreurs rencontrées

**Processus :**
1. Exécute `verifierRetards`
2. Exécute `verifierAbsences`
3. Exécute `verifierSortiesManquantes`
4. Combine et retourne les statistiques

## Méthodes utilitaires

### doitTravaillerAujourdhui

```php
protected function doitTravaillerAujourdhui(Employeur $employeur, Carbon $date): bool
```

Vérifie si un employeur doit travailler à une date donnée.

### estEnConge

```php
protected function estEnConge(Employeur $employeur, Carbon $date): bool
```

Vérifie si un employeur est en congé à une date donnée.

### getHeureLimiteAbsence

```php
protected function getHeureLimiteAbsence(Employeur $employeur, Carbon $date): Carbon
```

Obtient l'heure limite pour considérer une absence.

### getHeureFinTravail

```php
protected function getHeureFinTravail(Employeur $employeur, Carbon $date): Carbon
```

Obtient l'heure de fin de travail pour un employeur à une date donnée.

## Exemples d'utilisation

### Vérification des retards pour aujourd'hui

```php
$retardAbsenceService = app(RetardAbsenceService::class);
$statsRetards = $retardAbsenceService->verifierRetards();

// Afficher les statistiques
echo "Employeurs vérifiés : " . $statsRetards['total_employeurs'] . "\n";
echo "Retards détectés : " . $statsRetards['retards_detectes'] . "\n";
echo "Notifications envoyées : " . $statsRetards['notifications_envoyees'] . "\n";
```

### Vérification des absences pour une date spécifique

```php
$date = Carbon::parse('2025-03-15');
$retardAbsenceService = app(RetardAbsenceService::class);
$statsAbsences = $retardAbsenceService->verifierAbsences($date);

// Afficher les statistiques
echo "Employeurs vérifiés : " . $statsAbsences['total_employeurs'] . "\n";
echo "Absences détectées : " . $statsAbsences['absences_detectees'] . "\n";
echo "Notifications envoyées : " . $statsAbsences['notifications_envoyees'] . "\n";
```

### Exécution de toutes les vérifications

```php
$retardAbsenceService = app(RetardAbsenceService::class);
$stats = $retardAbsenceService->executerToutesVerifications();

// Afficher les statistiques globales
echo "Date : " . $stats['date'] . "\n";
echo "Total notifications : " . $stats['total_notifications'] . "\n";
echo "Total erreurs : " . $stats['total_erreurs'] . "\n";
```

## Configuration

Le service utilise les configurations de notification définies dans `ConfigurationPresenceService`. Les types de notifications suivants peuvent être activés ou désactivés par entreprise :

- `retard` : Notifications de retard
- `absence` : Notifications d'absence
- `sortie_manquante` : Notifications de sortie manquante

## Bonnes pratiques

1. **Planification des vérifications** : Utilisez des tâches planifiées (Laravel Scheduler) pour exécuter les vérifications à des moments appropriés :
   - Retards : en milieu de matinée
   - Absences : en fin de matinée
   - Sorties manquantes : en soirée

2. **Gestion des erreurs** : Le service inclut une gestion robuste des erreurs, mais surveillez les logs pour détecter des problèmes récurrents.

3. **Personnalisation des notifications** : Utilisez le `NotificationService` pour personnaliser les messages envoyés aux employeurs.

## Intégration avec d'autres services

Le `RetardAbsenceService` s'intègre parfaitement avec :

- **WebPointageService** : Pour la logique de pointage et la récupération des plages horaires
- **NotificationService** : Pour l'envoi de notifications
- **ConfigurationPresenceService** : Pour la gestion des configurations de notification

## Notes importantes

- Les notifications ne sont envoyées qu'une seule fois par jour pour chaque type d'événement et chaque employeur.
- Les vérifications tiennent compte des jours de travail et des congés approuvés.
- Les heures limites pour les absences et sorties manquantes sont calculées en fonction des plages horaires définies.
