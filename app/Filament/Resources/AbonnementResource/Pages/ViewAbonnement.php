<?php

namespace App\Filament\Resources\AbonnementResource\Pages;

use App\Filament\Resources\AbonnementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Services\AbonnementService;
use App\Models\PlanAbonnement;
use Filament\Notifications\Notification;

class ViewAbonnement extends ViewRecord
{
    protected static string $resource = AbonnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin()),
            Actions\Action::make('renouveler')
                ->label('Renouveler')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->isSuperAdmin())
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
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('changer_statut')
                ->label(fn () => $this->record->statut === 'actif' ? 'Désactiver' : 'Activer')
                ->icon(fn () => $this->record->statut === 'actif' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->statut === 'actif' ? 'danger' : 'success')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->isSuperAdmin())
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
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
        ];
    }
}
