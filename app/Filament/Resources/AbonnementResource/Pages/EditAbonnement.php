<?php

namespace App\Filament\Resources\AbonnementResource\Pages;

use App\Filament\Resources\AbonnementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\AbonnementService;
use App\Models\PlanAbonnement;
use Filament\Notifications\Notification;

class EditAbonnement extends EditRecord
{
    protected static string $resource = AbonnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin()),
            Actions\Action::make('renouveler')
                ->label('Renouveler')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Select::make('type_periode')
                        ->options([
                            'mensuel' => 'Mensuel',
                            'annuel' => 'Annuel',
                        ])
                        ->required()
                        ->default('mensuel'),
                ])
                ->action(function (array $data) {
                    $abonnementService = app(AbonnementService::class);
                    $abonnementService->renouvelerAbonnement($this->record, [
                        'type_periode' => $data['type_periode'],
                        'date_debut' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('Abonnement renouvelé')
                        ->body('L\'abonnement a été renouvelé avec succès.')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
            Actions\Action::make('changer_statut')
                ->label(fn () => $this->record->statut === 'actif' ? 'Désactiver' : 'Activer')
                ->icon(fn () => $this->record->statut === 'actif' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->statut === 'actif' ? 'danger' : 'success')
                ->requiresConfirmation()
                ->action(function () {
                    $abonnementService = app(AbonnementService::class);
                    $activer = $this->record->statut !== 'actif';
                    $abonnementService->changerStatutAbonnement($this->record, $activer);
                    
                    Notification::make()
                        ->title($activer ? 'Abonnement activé' : 'Abonnement désactivé')
                        ->body($activer 
                            ? 'L\'abonnement a été activé avec succès.' 
                            : 'L\'abonnement a été désactivé avec succès.')
                        ->color($activer ? 'success' : 'warning')
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
            Actions\Action::make('changer_plan')
                ->label('Changer de plan')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\Select::make('plan_abonnement_id')
                        ->label('Nouveau plan')
                        ->options(PlanAbonnement::all()->pluck('nom', 'id'))
                        ->required(),
                    \Filament\Forms\Components\Select::make('type_periode')
                        ->options([
                            'mensuel' => 'Mensuel',
                            'annuel' => 'Annuel',
                        ])
                        ->required()
                        ->default('mensuel'),
                ])
                ->action(function (array $data) {
                    $abonnementService = app(AbonnementService::class);
                    $nouveauPlan = PlanAbonnement::find($data['plan_abonnement_id']);
                    $abonnementService->changerPlanAbonnement($this->record, $nouveauPlan, [
                        'type_periode' => $data['type_periode'],
                    ]);
                    
                    Notification::make()
                        ->title('Plan d\'abonnement modifié')
                        ->body('Le plan d\'abonnement a été modifié avec succès.')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Abonnement mis à jour')
            ->body('L\'abonnement a été mis à jour avec succès.');
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si l'utilisateur n'est pas un super admin, empêcher la modification de l'entreprise
        if (!auth()->user()->isSuperAdmin() && isset($data['entreprise_id'])) {
            $data['entreprise_id'] = $this->record->entreprise_id;
        }
        
        return $data;
    }
}
