# Documentation des Commandes de Gestion des Présences

Cette documentation détaille les commandes console disponibles pour la gestion des présences, retards, absences et sorties manquantes dans l'application GENIUS WORK.

## Table des matières

1. [VerifierPresences](#verifierpresences)
2. [VerifierPresencesSansSortie](#verifierpresencesanssortie)
3. [VerifierSortiesManquantes](#verifiersortiesmanquantes)
4. [EnvoyerNotificationsPresence](#envoyernotificationspresence)

---

## VerifierPresences

### Description

Cette commande vérifie les retards et absences des employeurs selon la politique de l'entreprise et envoie des notifications si nécessaire.

### Signature

```
presence:verifier {--date= : Date à vérifier (format Y-m-d)} {--entreprise= : ID de l'entreprise spécifique} {--type= : Type de vérification (retard, absence, sortie_manquante, all)}
```

### Options

- `--date` : Date à vérifier au format Y-m-d (par défaut : aujourd'hui)
- `--entreprise` : ID de l'entreprise spécifique à vérifier (par défaut : toutes les entreprises)
- `--type` : Type de vérification à effectuer (par défaut : all)
  - `retard` : Vérifie uniquement les retards
  - `absence` : Vérifie uniquement les absences
  - `sortie_manquante` : Vérifie uniquement les sorties manquantes
  - `all` : Vérifie tous les types

### Exemples d'utilisation

```bash
# Vérifier toutes les présences pour aujourd'hui
php artisan presence:verifier

# Vérifier uniquement les retards pour une date spécifique
php artisan presence:verifier --date=2023-12-15 --type=retard

# Vérifier les absences pour une entreprise spécifique
php artisan presence:verifier --entreprise=5 --type=absence
```

### Fonctionnement

La commande utilise le service `RetardAbsenceService` pour effectuer les vérifications suivantes :

1. Récupère les employeurs actifs de l'entreprise spécifiée (ou toutes les entreprises)
2. Pour chaque employeur, vérifie :
   - Si les notifications sont activées pour l'entreprise
   - Si l'employeur doit travailler à la date spécifiée
   - Si l'employeur est en retard, absent ou a une sortie manquante
3. Envoie des notifications aux employeurs concernés via le `NotificationService`
4. Affiche des statistiques sur les vérifications effectuées

---

## VerifierPresencesSansSortie

### Description

Cette commande vérifie et annule les présences sans sortie enregistrée selon la politique de l'entreprise.

### Signature

```
presence:verifier-sans-sortie {--entreprise= : ID de l'entreprise spécifique}
```

### Options

- `--entreprise` : ID de l'entreprise spécifique à vérifier (par défaut : toutes les entreprises)

### Exemples d'utilisation

```bash
# Vérifier les présences sans sortie pour toutes les entreprises
php artisan presence:verifier-sans-sortie

# Vérifier les présences sans sortie pour une entreprise spécifique
php artisan presence:verifier-sans-sortie --entreprise=5
```

### Fonctionnement

La commande effectue les opérations suivantes :

1. Récupère les entreprises à vérifier (toutes ou une spécifique)
2. Pour chaque entreprise, vérifie si l'annulation des présences sans sortie est activée dans la politique ou la configuration
3. Récupère les présences de la veille qui n'ont pas d'heure de sortie enregistrée
4. Pour chaque présence sans sortie :
   - Annule la présence en mettant à jour son statut
   - Ajoute un commentaire indiquant la raison de l'annulation
   - Envoie une notification à l'employeur concerné si configuré

---

## VerifierSortiesManquantes

### Description

Cette commande vérifie les sorties manquantes des employeurs et envoie des notifications si nécessaire.

### Signature

```
presence:verifier-sorties-manquantes {--date= : Date à vérifier (format Y-m-d)} {--entreprise= : ID de l'entreprise spécifique}
```

### Options

- `--date` : Date à vérifier au format Y-m-d (par défaut : aujourd'hui)
- `--entreprise` : ID de l'entreprise spécifique à vérifier (par défaut : toutes les entreprises)

### Exemples d'utilisation

```bash
# Vérifier les sorties manquantes pour aujourd'hui
php artisan presence:verifier-sorties-manquantes

# Vérifier les sorties manquantes pour une date spécifique
php artisan presence:verifier-sorties-manquantes --date=2023-12-15

# Vérifier les sorties manquantes pour une entreprise spécifique
php artisan presence:verifier-sorties-manquantes --entreprise=5
```

### Fonctionnement

La commande utilise le service `RetardAbsenceService` pour effectuer les vérifications suivantes :

1. Récupère les employeurs actifs de l'entreprise spécifiée (ou toutes les entreprises)
2. Pour chaque employeur, vérifie :
   - Si les notifications de sortie manquante sont activées pour l'entreprise
   - Si l'employeur a pointé en entrée mais pas en sortie
   - Si l'heure actuelle dépasse l'heure de fin de travail prévue plus une marge de tolérance
3. Envoie des notifications aux employeurs concernés via le `NotificationService`
4. Affiche des statistiques sur les vérifications effectuées

---

## EnvoyerNotificationsPresence

### Description

Cette commande envoie des notifications de présence (retards, absences, sorties manquantes) aux employeurs selon la configuration de l'entreprise.

### Signature

```
notifications:presence {type? : Type de notification (retard, absence, sortie_manquante)} {--entreprise= : ID de l'entreprise spécifique}
```

### Arguments

- `type` : Type de notification à envoyer (par défaut : tous les types)
  - `retard` : Envoie uniquement les notifications de retard
  - `absence` : Envoie uniquement les notifications d'absence
  - `sortie_manquante` : Envoie uniquement les notifications de sortie manquante

### Options

- `--entreprise` : ID de l'entreprise spécifique à traiter (par défaut : toutes les entreprises)

### Exemples d'utilisation

```bash
# Envoyer toutes les notifications de présence
php artisan notifications:presence

# Envoyer uniquement les notifications de retard
php artisan notifications:presence retard

# Envoyer les notifications d'absence pour une entreprise spécifique
php artisan notifications:presence absence --entreprise=5
```

### Fonctionnement

La commande effectue les opérations suivantes :

1. Récupère les entreprises à traiter (toutes ou une spécifique)
2. Pour chaque entreprise, vérifie si les notifications sont activées
3. Selon le type de notification spécifié (ou tous les types) :
   - Pour les retards : vérifie si les employeurs ont pointé en retard par rapport à leur plage horaire
   - Pour les absences : vérifie si les employeurs n'ont pas pointé alors qu'ils devraient être présents
   - Pour les sorties manquantes : vérifie si les employeurs ont pointé en entrée mais pas en sortie
4. Envoie les notifications appropriées aux employeurs concernés via le `NotificationService`

---

## Planification des commandes

Pour une utilisation optimale, il est recommandé de planifier ces commandes dans le fichier `app/Console/Kernel.php` comme suit :

```php
protected function schedule(Schedule $schedule)
{
    // Vérifier les retards et absences toutes les heures pendant la journée de travail
    $schedule->command('presence:verifier')
             ->hourly()
             ->between('8:00', '19:00')
             ->weekdays();
    
    // Vérifier les sorties manquantes en fin de journée
    $schedule->command('presence:verifier-sorties-manquantes')
             ->dailyAt('19:30')
             ->weekdays();
    
    // Annuler les présences sans sortie chaque matin pour la veille
    $schedule->command('presence:verifier-sans-sortie')
             ->dailyAt('6:00')
             ->weekdays();
}
```

## Dépendances

Ces commandes dépendent des services suivants :

- `RetardAbsenceService` : Gère la logique de vérification des retards, absences et sorties manquantes
- `NotificationService` : Gère l'envoi des notifications aux employeurs
- `ConfigurationPresenceService` : Gère la configuration des présences pour chaque entreprise

## Modèles utilisés

- `Employeur` : Représente un employeur dans le système
- `Entreprise` : Représente une entreprise dans le système
- `Presence` : Représente un pointage (entrée, sortie, pause) d'un employeur
- `PlageHoraire` : Représente une plage horaire de travail pour un employeur
- `Notification` : Représente une notification envoyée à un employeur
