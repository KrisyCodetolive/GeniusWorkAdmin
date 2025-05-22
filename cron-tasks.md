# Configuration des Tâches Cron pour GENIUS WORK

Ce document détaille les tâches cron à configurer pour le bon fonctionnement de l'application GENIUS WORK. Ces tâches automatisées permettent de gérer les notifications, la facturation, les vérifications de présence et autres processus récurrents.

## Liste des Commandes Artisan

| Commande | Description | Fréquence recommandée | Exemple Crontab |
|---------|-------------|----------------------|-----------------|
| `notifications:presence` | Envoie les notifications de présence (retards, absences, sorties manquantes) | Toutes les heures en journée | `0 8-18 * * 1-5 cd /chemin/vers/gwork && php artisan notifications:presence` |
| `facturation:frais-usage` | Facture automatiquement les frais d'usage non facturés | Une fois par mois | `0 1 1 * * cd /chemin/vers/gwork && php artisan facturation:frais-usage` |
| `facturation:generer-factures` | Génère automatiquement les factures mensuelles pour tous les abonnements actifs | Une fois par mois | `0 2 1 * * cd /chemin/vers/gwork && php artisan facturation:generer-factures` |
| `factures:generer` | Génère les factures pour les abonnements à renouvellement automatique | Une fois par jour | `0 3 * * * cd /chemin/vers/gwork && php artisan factures:generer` |
| `presence:verifier` | Vérifie les retards, absences et sorties manquantes des employeurs | Toutes les heures en journée | `0 9-18 * * 1-5 cd /chemin/vers/gwork && php artisan presence:verifier` |
| `presence:verifier-sans-sortie` | Vérifie et annule les présences sans sortie selon la politique de l'entreprise | En fin de journée | `0 19 * * 1-5 cd /chemin/vers/gwork && php artisan presence:verifier-sans-sortie` |
| `presence:verifier-sorties-manquantes` | Vérifie les sorties manquantes des employeurs et envoie des notifications | En fin de journée | `30 19 * * 1-5 cd /chemin/vers/gwork && php artisan presence:verifier-sorties-manquantes` |
| `biometrique:sync` | Synchronise les appareils biométriques (logs, utilisateurs, heure) | Toutes les heures | `0 * * * * cd /chemin/vers/gwork && php artisan biometrique:sync` |
| `abonnements:send-expiration-notices` | Envoie des notifications d'expiration pour les abonnements | Une fois par jour | `0 8 * * * cd /chemin/vers/gwork && php artisan abonnements:send-expiration-notices` |
| `paiements:send-reminders` | Envoie des rappels pour les paiements en attente | Une fois par jour | `0 9 * * * cd /chemin/vers/gwork && php artisan paiements:send-reminders` |

## Configuration sur un Serveur Linux

Pour configurer ces tâches cron sur un serveur Linux, suivez ces étapes :

1. Connectez-vous au serveur via SSH
2. Ouvrez le fichier crontab avec la commande : `crontab -e`
3. Ajoutez les lignes correspondant aux tâches ci-dessus, en adaptant le chemin vers votre installation de GENIUS WORK
4. Enregistrez et fermez le fichier

Exemple de configuration complète :

```bash
# Notifications de présence (toutes les heures en journée, jours ouvrables)
0 8-18 * * 1-5 cd /chemin/vers/gwork && php artisan notifications:presence

# Facturation et abonnements (mensuelle/quotidienne)
0 1 1 * * cd /chemin/vers/gwork && php artisan facturation:frais-usage
0 2 1 * * cd /chemin/vers/gwork && php artisan facturation:generer-factures
0 3 * * * cd /chemin/vers/gwork && php artisan factures:generer

# Rappels et notifications (quotidien)
0 8 * * * cd /chemin/vers/gwork && php artisan abonnements:send-expiration-notices
0 9 * * * cd /chemin/vers/gwork && php artisan paiements:send-reminders

# Synchronisation des appareils biométriques (toutes les heures)
0 * * * * cd /chemin/vers/gwork && php artisan biometrique:sync

# Vérification des présences (jours ouvrables)
0 9-18 * * 1-5 cd /chemin/vers/gwork && php artisan presence:verifier
0 19 * * 1-5 cd /chemin/vers/gwork && php artisan presence:verifier-sans-sortie
30 19 * * 1-5 cd /chemin/vers/gwork && php artisan presence:verifier-sorties-manquantes
```

## Configuration sur un Serveur Windows

Pour Windows, vous pouvez utiliser le Planificateur de tâches :

1. Ouvrez le Planificateur de tâches
2. Créez une nouvelle tâche pour chaque commande
3. Configurez le déclencheur selon la fréquence recommandée
4. Dans l'action, utilisez `cmd.exe` avec les arguments : `/c cd /d D:\chemin\vers\gwork && php artisan [commande]`

## Journalisation

Toutes les commandes enregistrent leurs activités dans les logs Laravel. Vous pouvez consulter ces logs dans le fichier `storage/logs/laravel.log`.

Pour une meilleure gestion des logs, il est recommandé de configurer la rotation des logs dans le fichier `config/logging.php`.

## Dépannage

Si une tâche cron ne s'exécute pas correctement :

1. Vérifiez les permissions du dossier et des fichiers
2. Assurez-vous que PHP est accessible dans le PATH du système
3. Consultez les logs pour identifier les erreurs
4. Testez la commande manuellement pour vérifier son fonctionnement

## Surveillance

Il est recommandé de mettre en place une surveillance des tâches cron pour être alerté en cas d'échec. Vous pouvez utiliser des outils comme Monit, Supervisor ou des services de surveillance externes.
