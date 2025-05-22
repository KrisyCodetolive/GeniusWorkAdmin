<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class UpdateExpiredAbonnementsAction
{
    /**
     * Crée une action Filament pour mettre à jour les abonnements expirés
     *
     * @return \Filament\Actions\Action
     */
    public static function make(): Action
    {
        return Action::make('update_expired_abonnements')
            ->label('Mettre à jour les abonnements expirés')
            ->icon('heroicon-o-exclamation-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Mettre à jour les abonnements expirés')
            ->modalDescription('Cette action va marquer comme expirés tous les abonnements dont la date de fin est dépassée. Voulez-vous continuer?')
            ->modalSubmitActionLabel('Mettre à jour')
            ->action(function () {
                try {
                    // Journaliser le début de l'opération
                    Log::info('Démarrage de la mise à jour des abonnements expirés depuis l\'interface Filament');
                    
                    // Exécuter la commande Artisan
                    $output = [];
                    $exitCode = Artisan::call('abonnements:update-expired', [], $output);
                    
                    // Récupérer la sortie de la commande
                    $commandOutput = Artisan::output();
                    
                    // Journaliser le résultat
                    Log::info('Résultat de la commande de mise à jour des abonnements expirés', [
                        'exit_code' => $exitCode,
                        'output' => $commandOutput
                    ]);
                    
                    // Extraire le nombre d'abonnements mis à jour
                    preg_match('/(\d+) abonnements ont été marqués comme expirés/', $commandOutput, $matches);
                    $count = $matches[1] ?? 0;
                    
                    // Afficher une notification de succès
                    Notification::make()
                        ->title('Abonnements mis à jour')
                        ->body($count > 0 
                            ? "{$count} abonnement(s) ont été marqués comme expirés." 
                            : "Aucun abonnement n'a été marqué comme expiré.")
                        ->color($count > 0 ? 'warning' : 'success')
                        ->send();
                        
                } catch (\Exception $e) {
                    // Journaliser l'erreur
                    Log::error('Erreur lors de la mise à jour des abonnements expirés', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Afficher une notification d'erreur
                    Notification::make()
                        ->title('Erreur lors de la mise à jour')
                        ->body('Une erreur est survenue: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
