# Documentation : Système de SMS de bienvenue GENIUS WORK

## Table des matières

1. [Introduction](#introduction)
2. [Architecture du système](#architecture-du-système)
3. [Configuration](#configuration)
4. [Fonctionnement](#fonctionnement)
5. [Personnalisation des messages](#personnalisation-des-messages)
6. [Journalisation et suivi](#journalisation-et-suivi)
7. [Dépannage](#dépannage)
8. [Maintenance](#maintenance)
9. [Évolutions futures](#évolutions-futures)

## Introduction

Le système de SMS de bienvenue de GENIUS WORK est conçu pour envoyer automatiquement des messages SMS personnalisés aux utilisateurs et aux entreprises après la création de leur compte et la validation de leur paiement. Cette fonctionnalité complète le système d'emails de bienvenue existant et permet de maintenir un contact immédiat avec les nouveaux utilisateurs via un canal de communication mobile.

### Objectifs

- Confirmer la création réussie du compte par un canal alternatif
- Fournir un accès rapide aux informations essentielles
- Renforcer l'engagement des utilisateurs dès le début
- Offrir une expérience utilisateur multicanale

## Architecture du système

Le système de SMS de bienvenue suit les principes SOLID et utilise une architecture orientée services pour une meilleure maintenabilité et extensibilité, similaire au système d'emails de bienvenue.

### Structure des dossiers

```
app/
├── Http/
│   └── Controllers/
│       └── WorkflowController.php  # Contrôleur qui déclenche l'envoi de SMS
├── Models/
│   └── SMSLog.php                  # Modèle pour la journalisation des SMS
├── Services/
│   └── SMS/
│       ├── SMSServiceInterface.php # Interface du service de SMS
│       ├── WelcomeSMSService.php   # Implémentation du service de SMS de bienvenue
│       ├── OrangeSMSService.php    # Service d'envoi de SMS via Orange
│       └── SMSLogService.php       # Service de journalisation des SMS
└── Providers/
    └── SMSServiceProvider.php      # Fournisseur de services pour l'injection de dépendances
```

### Composants principaux

1. **Contrôleur (WorkflowController)** : Déclenche l'envoi des SMS après un paiement réussi
2. **Interface du service (SMSServiceInterface)** : Définit les méthodes pour l'envoi de SMS de bienvenue
3. **Service de SMS de bienvenue (WelcomeSMSService)** : Implémente la logique d'envoi des SMS de bienvenue
4. **Service d'envoi de SMS (OrangeSMSService)** : Gère l'envoi des SMS via l'API Orange
5. **Service de journalisation (SMSLogService)** : Enregistre les informations sur les SMS envoyés
6. **Modèle de log (SMSLog)** : Stocke les données des SMS dans la base de données
7. **Fournisseur de services (SMSServiceProvider)** : Configure l'injection de dépendances

## Configuration

### Configuration Orange SMS

La configuration du service Orange SMS est définie dans le fichier `config/sms.php` :

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut
    |--------------------------------------------------------------------------
    |
    | Définit le service SMS par défaut à utiliser pour les notifications système
    | Options disponibles : 'smslab', 'orange'
    |
    */
    'default_provider' => env('SMS_DEFAULT_PROVIDER', 'orange'),

    /*
    |--------------------------------------------------------------------------
    | Configuration Orange SMS
    |--------------------------------------------------------------------------
    */
    'orange' => [
        'client_id' => env('ORANGE_SMS_CLIENT_ID'),
        'client_secret' => env('ORANGE_SMS_CLIENT_SECRET'),
        'dev_phone_number' => env('ORANGE_SMS_DEV_PHONE_NUMBER'),
    ],
];
```

### Variables d'environnement

Les paramètres Orange SMS peuvent être configurés dans le fichier `.env` :

```
SMS_DEFAULT_PROVIDER=orange
ORANGE_SMS_CLIENT_ID=your_client_id_here
ORANGE_SMS_CLIENT_SECRET=your_client_secret_here
ORANGE_SMS_DEV_PHONE_NUMBER=+22500000000
```

### Configuration de la journalisation

La journalisation des SMS utilise le système de journalisation standard de Laravel, configuré dans `config/logging.php`. Un canal dédié peut être ajouté pour les SMS :

```php
'sms' => [
    'driver' => 'daily',
    'path' => storage_path('logs/sms.log'),
    'level' => 'info',
    'days' => 30,
    'permission' => 0664,
],
```

## Fonctionnement

### Flux d'exécution

1. L'utilisateur complète le processus d'inscription et de paiement
2. Le contrôleur `WorkflowController` reçoit la confirmation du paiement
3. La méthode `showSuccess` récupère les données nécessaires (utilisateur, entreprise, abonnement)
4. Le service de SMS est injecté via le conteneur IoC
5. La méthode `sendWelcomeSMS` est appelée pour envoyer les SMS
6. Le service de SMS envoie les messages à l'utilisateur et à l'entreprise
7. Les résultats sont journalisés dans le système de logs

### Code du contrôleur

La méthode `sendWelcomeSMS` du `WorkflowController` gère l'envoi des SMS de bienvenue :

```php
/**
 * Envoyer les SMS de bienvenue à l'utilisateur et à l'entreprise
 *
 * @param \App\Services\SMS\SMSServiceInterface $smsService
 * @param \App\Models\User|null $user
 * @param \App\Models\Entreprise|null $entreprise
 * @param \App\Models\Abonnement|null $abonnement
 * @return array Statuts d'envoi des SMS [user => bool, company => bool]
 */
private function sendWelcomeSMS($smsService, $user, $entreprise, $abonnement)
{
    $smsStatus = [
        'user' => false,
        'company' => false
    ];
    
    // Vérifier que tous les paramètres nécessaires sont présents
    if (!$user || !$entreprise || !$abonnement) {
        \Illuminate\Support\Facades\Log::warning('Impossible d\'envoyer les SMS de bienvenue : paramètres manquants', [
            'user_present' => (bool)$user,
            'entreprise_present' => (bool)$entreprise,
            'abonnement_present' => (bool)$abonnement
        ]);
        return $smsStatus;
    }
    
    try {
        // Vérifier si l'utilisateur a un numéro de téléphone
        if ($user->phone_number) {
            // Envoyer le SMS de bienvenue à l'utilisateur
            $userSmsResult = $smsService->sendUserWelcomeSMS($user, $entreprise, $abonnement);
            $smsStatus['user'] = $userSmsResult['success'] ?? false;
            
            // Journaliser le résultat de l'envoi du SMS utilisateur
            \Illuminate\Support\Facades\Log::info('SMS de bienvenue utilisateur', [
                'user_id' => $user->id,
                'phone' => $user->phone_number,
                'statut' => $smsStatus['user'] ? 'envoyé' : 'échec',
                'details' => $userSmsResult
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('SMS de bienvenue utilisateur non envoyé : numéro de téléphone manquant', [
                'user_id' => $user->id
            ]);
        }
        
        // Vérifier si l'entreprise a un numéro de téléphone
        if ($entreprise->phone_number) {
            // Envoyer le SMS de bienvenue à l'entreprise
            $companySmsResult = $smsService->sendCompanyWelcomeSMS($user, $entreprise, $abonnement);
            $smsStatus['company'] = $companySmsResult['success'] ?? false;
            
            // Journaliser le résultat de l'envoi du SMS entreprise
            \Illuminate\Support\Facades\Log::info('SMS de bienvenue entreprise', [
                'entreprise_id' => $entreprise->id,
                'phone' => $entreprise->phone_number,
                'statut' => $smsStatus['company'] ? 'envoyé' : 'échec',
                'details' => $companySmsResult
            ]);
        } else {
            \Illuminate\Support\Facades\Log::info('SMS de bienvenue entreprise non envoyé : numéro de téléphone manquant', [
                'entreprise_id' => $entreprise->id
            ]);
        }
        
    } catch (\Exception $e) {
        // Journaliser l'erreur
        \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des SMS de bienvenue', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    return $smsStatus;
}
```

## Personnalisation des messages

### Message de bienvenue utilisateur

Le message de bienvenue utilisateur est formaté dans la méthode `formatUserWelcomeMessage` du service `WelcomeSMSService`. Il contient :

```php
protected function formatUserWelcomeMessage($user, $entreprise, $abonnement)
{
    $message = "Bienvenue {$user->name} sur GENIUS WORK! ";
    $message .= "Votre compte a été créé avec succès. ";
    $message .= "Vous êtes maintenant membre de {$entreprise->name}. ";
    $message .= "Connectez-vous sur app.genius.ci pour commencer.";
    
    return $message;
}
```

### Message de bienvenue entreprise

Le message de bienvenue entreprise est formaté dans la méthode `formatCompanyWelcomeMessage` du service `WelcomeSMSService`. Il contient :

```php
protected function formatCompanyWelcomeMessage($user, $entreprise, $abonnement)
{
    $message = "Bienvenue {$entreprise->name} sur GENIUS WORK! ";
    $message .= "Votre entreprise a été enregistrée avec succès. ";
    $message .= "Abonnement: {$abonnement->plan->name}. ";
    $message .= "Contactez-nous au +225 0700000000 pour toute assistance.";
    
    return $message;
}
```

### Modification des messages

Pour modifier les messages SMS :

1. Ouvrir le fichier `app/Services/SMS/WelcomeSMSService.php`
2. Modifier les méthodes `formatUserWelcomeMessage` et `formatCompanyWelcomeMessage`
3. Respecter la limite de caractères des SMS (160 caractères pour un SMS standard)
4. Tester l'envoi des SMS pour vérifier le format

## Journalisation et suivi

### Fichiers de logs

Les logs des SMS sont stockés dans les fichiers de logs standard de Laravel :
- Chemin par défaut : `storage/logs/laravel.log`
- Si un canal dédié est configuré : `storage/logs/sms.log`
- Rotation : selon la configuration Laravel
- Rétention : selon la configuration Laravel

### Types de logs

1. **Logs d'information** :
   - Envoi réussi d'un SMS utilisateur
   - Envoi réussi d'un SMS entreprise
   - SMS non envoyé en raison d'un numéro manquant

2. **Logs d'erreur** :
   - Échec de connexion à l'API Orange
   - Erreur lors de l'envoi d'un SMS
   - Exceptions non gérées

### Format des logs

```
[2025-05-11 08:15:23] local.INFO: SMS de bienvenue utilisateur {"user_id":123,"phone":"+22501234567","statut":"envoyé","details":{"success":true}}
```

### Base de données

Les SMS sont également enregistrés dans la table `sms_logs` de la base de données, avec les informations suivantes :
- Numéro de téléphone destinataire
- Contenu du message
- Statut d'envoi (envoyé, échec, en attente)
- Date d'envoi
- Nombre de tentatives
- Message d'erreur (le cas échéant)

## Dépannage

### Problèmes courants

1. **Échec de connexion à l'API Orange** :
   - Vérifier les identifiants client_id et client_secret dans le fichier `.env`
   - Confirmer que l'API Orange est accessible
   - Vérifier que le compte Orange SMS est actif

2. **SMS non reçus** :
   - Vérifier les logs dans `storage/logs/laravel.log` ou `storage/logs/sms.log`
   - Contrôler le format du numéro de téléphone (format international +XXX)
   - Vérifier que le numéro de téléphone est correct et actif

3. **Erreurs de formatage** :
   - Vérifier que le message ne dépasse pas la limite de caractères
   - S'assurer que les caractères spéciaux sont correctement encodés

### Résolution des problèmes

1. **Consulter les logs** :
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Vérifier les entrées dans la base de données** :
   ```php
   // Dans Tinker
   App\Models\SMSLog::where('status', 'failed')->latest()->get();
   ```

3. **Tester l'envoi manuel** :
   ```php
   // Dans Tinker
   $smsService = app(\App\Services\SMS\OrangeSMSService::class);
   $smsLogService = app(\App\Services\SMS\SMSLogService::class);
   $smsService->sendSMS('+22501234567', 'Message de test');
   ```

## Maintenance

### Tâches régulières

1. **Vérification des logs** :
   - Surveiller régulièrement les logs de SMS pour détecter les erreurs
   - Analyser les taux de réussite et d'échec

2. **Mise à jour des messages** :
   - Actualiser les messages SMS en fonction des évolutions de GENIUS WORK
   - Optimiser le contenu pour respecter la limite de caractères

3. **Tests d'envoi** :
   - Tester périodiquement l'envoi de SMS pour s'assurer du bon fonctionnement
   - Vérifier la réception des SMS sur différents opérateurs

### Mise à jour de la configuration

Pour mettre à jour la configuration Orange SMS :

1. Modifier les variables d'environnement dans le fichier `.env`
2. Ou mettre à jour les valeurs par défaut dans `config/sms.php`
3. Vider le cache de configuration :
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

## Évolutions futures

### Améliorations possibles

1. **SMS multilingues** :
   - Ajouter la prise en charge de plusieurs langues pour les SMS
   - Détecter automatiquement la langue préférée de l'utilisateur

2. **Personnalisation avancée** :
   - Permettre aux administrateurs de personnaliser les templates via l'interface
   - Ajouter des variables dynamiques supplémentaires

3. **Statistiques d'envoi** :
   - Intégrer un tableau de bord pour suivre les statistiques d'envoi de SMS
   - Générer des rapports sur l'efficacité des SMS

4. **File d'attente** :
   - Utiliser le système de file d'attente de Laravel pour l'envoi asynchrone
   - Implémenter un système de réessai automatique en cas d'échec

---

## Annexes

### Diagramme de séquence

```
┌───────┌───────────────┌───────────┌────────
│Utilisateur│          │WorkflowController│          │SMSService   │          │Orange API│
└─────┐────└──────┐─────└──────┐────└────┐────
      │                        │                         │                      │
      │ Complète paiement      │                         │                      │
      │────────────────────────>│                         │                      │
      │                        │                         │                      │
      │                        │ Récupère données        │                      │
      │                        │─────────────────────│       │                      │
      │                        │                 │       │                      │
      │                        │<─────────────────────┐       │                      │
      │                        │                         │                      │
      │                        │ sendWelcomeSMS()        │                      │
      │                        │──────────────────────────>│                      │
      │                        │                         │                      │
      │                        │                         │ sendUserWelcomeSMS() │
      │                        │                         │───────────────────────>│
      │                        │                         │                      │
      │                        │                         │ sendCompanyWelcomeSMS│
      │                        │                         │───────────────────────>│
      │                        │                         │                      │
      │                        │                         │ Logs résultats       │
      │                        │                         │─────────│            │
      │                        │                         │         │            │
      │                        │                         │<────────┐            │
      │                        │                         │                      │
      │                        │<──────────────────────────│                      │
      │                        │                         │                      │
```

### Références

- [Documentation API Orange SMS](https://developer.orange.com/apis/sms-ci/getting-started)
- [Documentation Laravel Logging](https://laravel.com/docs/10.x/logging)
- [Documentation Laravel Queues](https://laravel.com/docs/10.x/queues)

---

Document créé le 11 mai 2025 | Dernière mise à jour : 11 mai 2025
