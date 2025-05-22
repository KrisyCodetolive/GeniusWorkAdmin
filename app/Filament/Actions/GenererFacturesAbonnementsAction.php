<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GenererFacturesAbonnementsAction
{
    /**
     * Crée une action Filament pour générer les factures d'abonnements
     *
     * @return \Filament\Actions\Action
     */
    public static function make(): Action
    {
        return Action::make('generer_factures_abonnements')
            ->label('Générer les factures')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->form([
                Forms\Components\Toggle::make('expired')
                    ->label('Inclure les abonnements expirés')
                    ->helperText('Générer des factures pour les abonnements déjà expirés')
                    ->default(false),
            ])
            ->requiresConfirmation()
            ->modalHeading('Générer les factures d\'abonnements')
            ->modalDescription('Cette action va générer des factures pour les abonnements à renouvellement automatique. Vous pouvez choisir d\'inclure les abonnements expirés.')
            ->modalSubmitActionLabel('Générer les factures')
            ->action(function (array $data) {
                try {
                    // Journaliser le début de l'opération
                    Log::info('Démarrage de la génération des factures d\'abonnements depuis l\'interface Filament', [
                        'expired_mode' => $data['expired'] ?? false
                    ]);
                    
                    // Préparer les options pour la commande Artisan
                    $options = [];
                    if ($data['expired'] ?? false) {
                        $options['--expired'] = true;
                    }
                    
                    // Exécuter la commande Artisan
                    $exitCode = Artisan::call('factures:generer', $options);
                    
                    // Récupérer la sortie de la commande
                    $commandOutput = Artisan::output();
                    
                    // Journaliser le résultat
                    Log::info('Résultat de la commande de génération des factures', [
                        'exit_code' => $exitCode,
                        'output' => $commandOutput
                    ]);
                    
                    // Extraire le nombre de factures générées
                    preg_match('/(\d+) factures générées/', $commandOutput, $matches);
                    $count = $matches[1] ?? 0;
                    
                    // Afficher une notification de succès
                    Notification::make()
                        ->title('Factures générées')
                        ->body($count > 0 
                            ? "{$count} facture(s) ont été générées avec succès." 
                            : "Aucune facture n'a été générée.")
                        ->color($count > 0 ? 'success' : 'info')
                        ->send();
                        
                } catch (\Exception $e) {
                    // Journaliser l'erreur
                    Log::error('Erreur lors de la génération des factures d\'abonnements', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Afficher une notification d'erreur
                    Notification::make()
                        ->title('Erreur lors de la génération des factures')
                        ->body('Une erreur est survenue: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
