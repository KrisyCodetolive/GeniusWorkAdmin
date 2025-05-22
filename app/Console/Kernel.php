<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Vérification des présences sans pointage de sortie (tôt le matin)
        $schedule->command('presence:verifier-sans-sortie')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/presence-sans-sortie.log'));
            
        // Générer les factures tous les jours à 1h du matin
        $schedule->command('factures:generer')
                ->dailyAt('01:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/factures.log'));
                
        // Générer les factures mensuelles automatiquement le 1er du mois à 2h du matin
        $schedule->command('facturation:generer-factures --notifier')
                ->monthlyOn(1, '02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/facturation-auto.log'));
                
        // Facturer les frais d'usage le 5 du mois à 3h du matin
        $schedule->command('facturation:frais-usage --periode=mois --notifier')
                ->monthlyOn(5, '03:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/frais-usage.log'));
                
        // Vérifier les présences sans pointage de sortie pour le jour précédent (4h du matin)
        $schedule->command('presence:verifier-sans-sortie')
                ->dailyAt('04:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/presence-sans-sortie-matin.log'));
                
        // Vérifier les présences sans sortie en fin de journée
        $schedule->command('presence:verifier-sans-sortie')
                ->dailyAt('23:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verifier-presences-sans-sortie-soir.log'));
                
        // Vérification spécifique des sorties manquantes (nouvelle commande)
        $schedule->command('presence:verifier-sorties-manquantes')
                ->dailyAt('19:00')
                ->weekdays()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verification-sorties-manquantes.log'));
                
        // Vérification des retards et envoi de notifications
        $schedule->command('presence:verifier retards')
                ->everyFifteenMinutes()
                ->between('7:00', '20:00')
                ->weekdays()
                ->withoutOverlapping(30)
                ->appendOutputTo(storage_path('logs/verification-retards.log'));
                
        // Vérification des absences et envoi de notifications
        $schedule->command('presence:verifier absences')
                ->hourly()
                ->between('8:00', '18:00')
                ->weekdays()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verification-absences.log'));
                
        // Vérification des sorties manquantes et envoi de notifications
        $schedule->command('presence:verifier sorties')
                ->hourly()
                ->between('17:00', '23:00')
                ->weekdays()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verification-sorties.log'));
                
        // Vérification complète quotidienne de toutes les présences
        $schedule->command('presence:verifier all')
                ->dailyAt('23:45')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/verification-presences-complete.log'));
                
        // Génération automatique du rapport de présence quotidien
        $schedule->command('rapport:generer-presence-quotidien')
                ->dailyAt('00:15')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/rapport-presence-quotidien.log'));
                
        // Synchronisation des logs des appareils biométriques toutes les heures
        $schedule->command('biometrique:sync --type=logs')
                ->hourly()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/biometrique-sync-logs.log'));
                
        // Synchronisation des utilisateurs des appareils biométriques tous les jours à 5h du matin
        $schedule->command('biometrique:sync --type=users')
                ->dailyAt('05:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/biometrique-sync-users.log'));
                
        // Synchronisation de l'heure des appareils biométriques tous les jours à 5h30 du matin
        $schedule->command('biometrique:sync --type=time')
                ->dailyAt('05:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/biometrique-sync-time.log'));
                
        // Synchronisation complète des appareils biométriques tous les dimanches à 2h du matin
        $schedule->command('biometrique:sync --type=all')
                ->weekly()
                ->sundays()
                ->at('02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/biometrique-sync-complete.log'));
                
        // Envoyer des rappels pour les paiements en attente tous les jours à 9h du matin
        $schedule->command('paiements:send-reminders')
                ->dailyAt('09:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/paiement-reminders.log'));
                
        // Envoyer des notifications d'expiration d'abonnement tous les jours à 10h du matin
        $schedule->command('abonnements:send-expiration-notices')
                ->dailyAt('10:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/abonnement-expiration-notices.log'));
                
        // Mettre à jour le statut des abonnements expirés tous les jours à 00:30
        $schedule->command('abonnements:update-expired')
                ->dailyAt('00:30')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/abonnement-update-expired.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
