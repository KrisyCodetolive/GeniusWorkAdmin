# Documentation : Système d'emails de bienvenue GENIUS WORK

## Table des matières

1. [Introduction](#introduction)
2. [Architecture du système](#architecture-du-système)
3. [Configuration](#configuration)
4. [Fonctionnement](#fonctionnement)
5. [Personnalisation des emails](#personnalisation-des-emails)
6. [Journalisation et suivi](#journalisation-et-suivi)
7. [Dépannage](#dépannage)
8. [Maintenance](#maintenance)
9. [Évolutions futures](#évolutions-futures)

## Introduction

Le système d'emails de bienvenue de GENIUS WORK est conçu pour envoyer automatiquement des emails personnalisés aux utilisateurs et aux entreprises après la création de leur compte et la validation de leur paiement. Cette fonctionnalité s'intègre dans le workflow d'inscription et permet de maintenir le contact avec les nouveaux utilisateurs.

### Objectifs

- Confirmer la création réussie du compte
- Fournir des informations sur l'abonnement souscrit
- Guider l'utilisateur vers les prochaines étapes
- Renforcer l'image professionnelle de GENIUS WORK

## Architecture du système

Le système d'emails de bienvenue suit les principes SOLID et utilise une architecture orientée services pour une meilleure maintenabilité et extensibilité.

### Structure des dossiers

```
app/
├── Http/
│   └── Controllers/
│       └── WorkflowController.php  # Contrôleur qui déclenche l'envoi d'emails
├── Mail/
│   ├── UserWelcomeEmail.php        # Classe Mailable pour l'email utilisateur
│   └── CompanyWelcomeEmail.php     # Classe Mailable pour l'email entreprise
├── Services/
│   └── Mail/
│       ├── EmailServiceInterface.php  # Interface du service d'email
│       ├── SmtpEmailService.php       # Implémentation SMTP du service
│       └── EmailLogger.php            # Service de journalisation des emails
└── Providers/
    └── EmailServiceProvider.php     # Fournisseur de services pour l'injection de dépendances
```

### Composants principaux

1. **Contrôleur (WorkflowController)** : Déclenche l'envoi des emails après un paiement réussi
2. **Classes Mailable** : Définissent le contenu et la structure des emails
3. **Service d'email** : Gère l'envoi des emails via SMTP
4. **Logger d'emails** : Enregistre les informations sur les emails envoyés
5. **Fournisseur de services** : Configure l'injection de dépendances

## Configuration

### Configuration SMTP

La configuration SMTP est définie dans le fichier `config/mail_service.php` :

```php
return [
    'smtp' => [
        'host' => env('MAIL_HOST', 'mail.genius.ci'),
        'port' => env('MAIL_PORT', 465),
        'username' => env('MAIL_USERNAME', 'work@genius.ci'),
        'password' => env('MAIL_PASSWORD', 'work@genius.ci'),
        'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'work@genius.ci'),
        'from_name' => env('MAIL_FROM_NAME', config('app.name')),
        'default_bcc' => [
            'it@genius.ci',
            'work@genius.ci'
        ],
    ],
    'logging' => [
        'channel' => 'emails',
        'retention_days' => 30,
    ],
];
```

### Variables d'environnement

Les paramètres SMTP peuvent être configurés dans le fichier `.env` :

```
MAIL_MAILER=smtp
MAIL_SCHEME=ssl
MAIL_HOST=mail.genius.ci
MAIL_PORT=465
MAIL_USERNAME=work@genius.ci
MAIL_PASSWORD=your_password_here
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="work@genius.ci"
MAIL_FROM_NAME="${APP_NAME}"
```

### Configuration de la journalisation

La journalisation des emails est configurée dans `config/logging.php` avec un canal dédié :

```php
'emails' => [
    'driver' => 'daily',
    'path' => storage_path('logs/emails.log'),
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
4. Le service d'email est injecté via le conteneur IoC
5. La méthode `sendWelcomeEmails` est appelée pour envoyer les emails
6. Le service d'email envoie les emails à l'utilisateur et à l'entreprise
7. Les BCC configurés (it@genius.ci et work@genius.ci) reçoivent une copie des emails
8. Les résultats sont journalisés dans le canal dédié aux emails

### Code du contrôleur

```php
public function showSuccess($paiementReference)
{
    // Récupérer le paiement
    $paiement = \App\Models\Paiement::where('reference', $paiementReference)->firstOrFail();
    
    // Vérifier que le paiement est bien validé
    if (!$paiement->estComplete()) {
        return redirect()->route('paiements.statut', $paiement->reference)
            ->with('error', 'Le paiement n\'est pas encore validé.');
    }
    
    // Récupérer les données associées
    $facturation = $paiement->facturation;
    $abonnement = $paiement->abonnement ?? $facturation->abonnement;
    $entreprise = $paiement->entreprise ?? $abonnement->entreprise;
    
    // Récupérer l'utilisateur (administrateur de l'entreprise)
    $user = auth()->user();
    
    // Envoyer les emails de bienvenue via le service d'email
    $emailService = app(\App\Services\Mail\EmailServiceInterface::class);
    $this->sendWelcomeEmails($emailService, $user, $entreprise, $abonnement);
    
    return view('workflow.success', [
        'paiement' => $paiement,
        'facturation' => $facturation,
        'abonnement' => $abonnement,
        'entreprise' => $entreprise
    ]);
}

private function sendWelcomeEmails($emailService, $user, $entreprise, $abonnement)
{
    try {
        // Envoyer l'email de bienvenue à l'utilisateur
        $userEmailSent = $emailService->sendUserWelcome($user, $entreprise, $abonnement);
        
        // Envoyer l'email de bienvenue à l'entreprise
        $companyEmailSent = $emailService->sendCompanyWelcome($user, $entreprise, $abonnement);
        
        // Les logs sont déjà gérés par le service EmailLogger
    } catch (\Exception $e) {
        // Créer une instance du logger d'emails pour enregistrer l'erreur
        $emailLogger = new \App\Services\Mail\EmailLogger();
        $emailLogger->emailError($e, [
            'user_id' => $user->id,
            'entreprise_id' => $entreprise->id,
            'abonnement_id' => $abonnement->id,
            'context' => 'workflow_success'
        ]);
    }
}
```

## Personnalisation des emails

### Email de bienvenue utilisateur

L'email de bienvenue utilisateur est défini dans `resources/views/emails/user-welcome.blade.php`. Il contient :

- Un message de bienvenue personnalisé avec le nom de l'utilisateur
- Les détails du compte utilisateur
- Les informations sur l'entreprise
- Les détails de l'abonnement
- Un bouton pour accéder au tableau de bord

### Email de bienvenue entreprise

L'email de bienvenue entreprise est défini dans `resources/views/emails/company-welcome.blade.php`. Il contient :

- Un message de bienvenue avec le nom de l'entreprise
- Les détails de l'entreprise
- Les informations sur l'abonnement
- Une présentation des fonctionnalités de GENIUS WORK
- Les coordonnées du support

### Modification des templates

Pour modifier les templates d'emails :

1. Ouvrir le fichier Blade correspondant dans `resources/views/emails/`
2. Modifier le contenu HTML/Markdown selon les besoins
3. Utiliser la syntaxe Blade pour intégrer les variables dynamiques
4. Tester l'email en utilisant la fonctionnalité de prévisualisation de Laravel

## Journalisation et suivi

### Fichiers de logs

Les logs des emails sont stockés dans un fichier dédié :
- Chemin : `storage/logs/emails.log`
- Rotation : quotidienne
- Rétention : 30 jours

### Types de logs

1. **Logs d'information** :
   - Envoi réussi d'un email utilisateur
   - Envoi réussi d'un email entreprise
   - Ajout de destinataires BCC

2. **Logs d'erreur** :
   - Échec de connexion SMTP
   - Erreur lors de l'envoi d'un email
   - Exceptions non gérées

### Format des logs

```
[2025-05-11 08:15:23] emails.INFO: Email de bienvenue envoyé à l'utilisateur {"user_id":123,"email":"user@example.com","timestamp":"2025-05-11 08:15:23","type":"user_welcome"}
```

## Dépannage

### Problèmes courants

1. **Échec de connexion SMTP** :
   - Vérifier les paramètres SMTP dans le fichier `.env`
   - Confirmer que le serveur SMTP est accessible
   - Vérifier les identifiants de connexion

2. **Emails non reçus** :
   - Vérifier les logs dans `storage/logs/emails.log`
   - Contrôler les filtres anti-spam du destinataire
   - Vérifier que l'adresse email est correcte

3. **Erreurs de template** :
   - Vérifier la syntaxe Blade dans les fichiers de template
   - S'assurer que toutes les variables utilisées sont définies

### Résolution des problèmes

1. **Consulter les logs** :
   ```bash
   tail -f storage/logs/emails.log
   ```

2. **Tester l'envoi manuel** :
   ```php
   // Dans Tinker
   $user = App\Models\User::find(1);
   $entreprise = App\Models\Entreprise::find(1);
   $abonnement = App\Models\Abonnement::find(1);
   $email = new App\Mail\UserWelcomeEmail($user, $entreprise, $abonnement);
   Mail::to($user->email)->send($email);
   ```

3. **Vérifier la configuration SMTP** :
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

## Maintenance

### Tâches régulières

1. **Vérification des logs** :
   - Surveiller régulièrement les logs d'emails pour détecter les erreurs
   - Nettoyer les anciens logs si nécessaire

2. **Mise à jour des templates** :
   - Actualiser les templates d'emails en fonction des évolutions de GENIUS WORK
   - Vérifier la compatibilité avec les différents clients de messagerie

3. **Tests d'envoi** :
   - Tester périodiquement l'envoi d'emails pour s'assurer du bon fonctionnement
   - Vérifier la réception des emails sur différentes plateformes

### Mise à jour de la configuration

Pour mettre à jour la configuration SMTP :

1. Modifier les variables d'environnement dans le fichier `.env`
2. Ou mettre à jour les valeurs par défaut dans `config/mail_service.php`
3. Vider le cache de configuration :
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

## Évolutions futures

### Améliorations possibles

1. **Emails multilingues** :
   - Ajouter la prise en charge de plusieurs langues pour les emails
   - Détecter automatiquement la langue préférée de l'utilisateur

2. **Personnalisation avancée** :
   - Permettre aux administrateurs de personnaliser les templates via l'interface
   - Ajouter des variables dynamiques supplémentaires

3. **Statistiques d'ouverture** :
   - Intégrer un système de suivi d'ouverture des emails
   - Générer des rapports sur l'efficacité des emails

4. **File d'attente** :
   - Utiliser le système de file d'attente de Laravel pour l'envoi asynchrone
   - Améliorer les performances lors de l'envoi de nombreux emails

---

## Annexes

### Diagramme de séquence

```
┌─────────┐          ┌───────────────┐          ┌─────────────┐          ┌──────┐
│Utilisateur│          │WorkflowController│          │EmailService │          │SMTP  │
└─────┬─────┘          └───────┬───────┘          └──────┬──────┘          └──┬───┘
      │                        │                         │                    │
      │ Complète paiement      │                         │                    │
      │───────────────────────>│                         │                    │
      │                        │                         │                    │
      │                        │ Récupère données        │                    │
      │                        │─────────────────┐       │                    │
      │                        │                 │       │                    │
      │                        │<────────────────┘       │                    │
      │                        │                         │                    │
      │                        │ sendWelcomeEmails()     │                    │
      │                        │────────────────────────>│                    │
      │                        │                         │                    │
      │                        │                         │ sendUserWelcome()  │
      │                        │                         │────────────────────>
      │                        │                         │                    │
      │                        │                         │ sendCompanyWelcome()
      │                        │                         │────────────────────>
      │                        │                         │                    │
      │                        │                         │ Logs résultats     │
      │                        │                         │─────────┐          │
      │                        │                         │         │          │
      │                        │                         │<────────┘          │
      │                        │                         │                    │
      │                        │<────────────────────────│                    │
      │                        │                         │                    │
      │ Affiche page succès    │                         │                    │
      │<───────────────────────│                         │                    │
      │                        │                         │                    │
```

### Références

- [Documentation Laravel Mail](https://laravel.com/docs/10.x/mail)
- [Documentation Laravel Logging](https://laravel.com/docs/10.x/logging)
- [Documentation SMTP](https://fr.wikipedia.org/wiki/Simple_Mail_Transfer_Protocol)

---

Document créé le 11 mai 2025 | Dernière mise à jour : 11 mai 2025
