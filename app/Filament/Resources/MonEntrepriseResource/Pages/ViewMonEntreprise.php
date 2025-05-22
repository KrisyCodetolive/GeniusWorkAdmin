<?php

namespace App\Filament\Resources\MonEntrepriseResource\Pages;

use App\Filament\Resources\MonEntrepriseResource;
use App\Filament\Widgets\EntrepriseStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ViewMonEntreprise extends ViewRecord
{
    protected static string $resource = MonEntrepriseResource::class;

    protected function getHeaderActions(): array
    {
        $user = Auth::user();
        $actions = [
            Actions\EditAction::make(),
        ];
        
        // Ajouter l'action de backup si l'utilisateur est admin, superadmin ou support
        if ($user && ($user->isAdmin() || $user->isSuperAdmin() || $user->isSupport())) {
            $actions[] = Actions\Action::make('backup')
                ->label('Sauvegarder les données')
                ->icon('heroicon-o-archive-box')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Sauvegarder les données de l\'entreprise')
                ->modalDescription('Cette action va générer un fichier SQL contenant toutes les données de l\'entreprise. Ce processus peut prendre quelques minutes selon la quantité de données.')
                ->modalSubmitActionLabel('Confirmer la sauvegarde')
                ->action(function () {
                    $entrepriseId = $this->record->id;
                    
                    try {
                        // Exécuter la commande de backup
                        $backupResult = $this->executeBackupCommand($entrepriseId);
                        
                        if ($backupResult['success']) {
                            // Stocker temporairement le contenu dans la session
                            session([
                                'backup_content' => $backupResult['content'],
                                'backup_filename' => $backupResult['file_name'],
                                'backup_size' => $backupResult['size']
                            ]);
                            
                            Notification::make()
                                ->title('Sauvegarde terminée avec succès')
                                ->body('Le téléchargement va démarrer automatiquement.')
                                ->success()
                                ->send();
                            
                            // Rediriger vers une route de téléchargement
                            return redirect()->route('entreprise.backup.download');
                        } else {
                            $errorMessage = isset($backupResult['error']) ? $backupResult['error'] : 'Veuillez consulter les logs pour plus d\'informations.';
                            
                            Notification::make()
                                ->title('Erreur lors de la sauvegarde')
                                ->body($errorMessage)
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Log::error('Exception lors du processus de backup', [
                            'entreprise_id' => $entrepriseId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        Notification::make()
                            ->title('Erreur lors de la sauvegarde')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }
        
        return $actions;
    }

    

    public function mount(int|string $record): void
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a accès à cette entreprise
        if ($user && !$user->hasRole(['SuperAdmin', 'Support']) && $user->entreprise_id != $record) {
            $this->redirect(MonEntrepriseResource::getUrl('view', ['record' => $user->entreprise_id]));
            return;
        }
        
        parent::mount($record);
    }
    
    /**
     * Exécute la commande de backup pour l'entreprise spécifiée et retourne le contenu directement
     *
     * @param int|string $entrepriseId
     * @return array Tableau contenant le statut de l'opération et les informations sur le contenu SQL
     */
    protected function executeBackupCommand(int|string $entrepriseId): array
    {
        try {
            // Log de début d'exécution
            Log::info('Début de l\'exécution de la commande de backup', [
                'entreprise_id' => $entrepriseId,
                'user_id' => auth()->id(),
                'timestamp' => now()->toDateTimeString()
            ]);
            
            // Réinitialiser toute instance précédente du résultat
            if (app()->has('backup.result')) {
                app()->forgetInstance('backup.result');
                Log::debug('Instance précédente de backup.result supprimée');
            }
            
            // Exécuter la commande avec les options pour générer en mémoire et retourner les données
            Log::debug('Appel de la commande Artisan', [
                'commande' => 'entreprise:backup',
                'params' => [
                    'entreprise_id' => $entrepriseId,
                    '--memory-only' => true,
                    '--api' => true,
                ]
            ]);
            
            $exitCode = Artisan::call('entreprise:backup', [
                'entreprise_id' => $entrepriseId,
                '--memory-only' => true,
                '--api' => true,
            ]);
            
            Log::debug('Résultat de la commande Artisan', [
                'exit_code' => $exitCode
            ]);
            
            // Récupérer le résultat depuis le conteneur d'application
            Log::debug('Vérification de la présence du résultat dans le conteneur', [
                'has_result' => app()->has('backup.result')
            ]);
            
            if (app()->has('backup.result')) {
                $result = app()->make('backup.result');
                
                Log::debug('Résultat récupéré depuis le conteneur', [
                    'has_success_key' => isset($result['success']),
                    'success' => $result['success'] ?? false,
                    'has_content' => isset($result['content']),
                    'content_length' => isset($result['content']) ? strlen($result['content']) : 0,
                    'filename' => $result['filename'] ?? null,
                    'size' => $result['size'] ?? 0
                ]);
                
                if (isset($result['success']) && $result['success']) {
                    $returnData = [
                        'success' => true,
                        'content' => $result['content'],
                        'file_name' => $result['filename'] ?? $result['file_name'] ?? ('backup_' . now()->format('Y-m-d_His') . '.sql'),
                        'size' => $result['size'],
                        'file_size' => round($result['size'] / 1024 / 1024, 2) . ' MB'
                    ];
                    
                    Log::info('Backup réussi, prêt pour le téléchargement', [
                        'file_name' => $result['filename'],
                        'file_size_mb' => round($result['size'] / 1024 / 1024, 2)
                    ]);
                    
                    return $returnData;
                }
            }
            
            // Si on arrive ici, c'est qu'il y a eu un problème
            Log::error('Erreur lors de la génération du backup: résultat non trouvé ou incomplet', [
                'entreprise_id' => $entrepriseId,
                'has_result' => app()->has('backup.result'),
            ]);
            
            return ['success' => false, 'error' => 'Erreur lors de la génération du backup'];
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'exécution de la commande de backup', [
                'entreprise_id' => $entrepriseId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}   
