<?php

namespace App\Filament\Resources\AbonnementResource\Pages;

use App\Filament\Resources\AbonnementResource;
use App\Filament\Actions\UpdateExpiredAbonnementsAction;
use App\Filament\Actions\GenererFacturesAbonnementsAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AbonnementService;
use Filament\Notifications\Notification;

class ListAbonnements extends ListRecords
{
    protected static string $resource = AbonnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin()),
            Actions\Action::make('verifier_expirations')
                ->label('Vérifier les expirations')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->action(function () {
                    $abonnementService = app(AbonnementService::class);
                    $abonnements = $abonnementService->getAbonnementsExpirantBientot(7);
                    
                    if ($abonnements->count() > 0) {
                        Notification::make()
                            ->title($abonnements->count() . ' abonnement(s) expirent bientôt')
                            ->body('Des abonnements expirent dans les 7 prochains jours.')
                            ->warning()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Aucun abonnement n\'expire bientôt')
                            ->success()
                            ->send();
                    }
                }),
            // Ajouter l'action de mise à jour des abonnements expirés
            UpdateExpiredAbonnementsAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
                
            // Ajouter l'action de génération des factures d'abonnements
            GenererFacturesAbonnementsAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin() || auth()->user()->isSupport()),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        
        // Trier par date d'expiration (les plus proches d'abord)
        return $query->orderBy('date_fin', 'asc');
    }

    
}
