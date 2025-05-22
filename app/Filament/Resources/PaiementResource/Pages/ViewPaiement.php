<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewPaiement extends ViewRecord
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->statut !== 'complete' || auth()->user()->isSuperAdmin()),
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()->isSuperAdmin()),
            Actions\Action::make('valider')
                ->label('Valider le paiement')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->necessiteValidation() && auth()->user()->isSuperAdmin())
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Commentaire de validation')
                        ->maxLength(1000),
                ])
                ->action(function (array $data) {
                    $this->record->valider(auth()->id(), $data['commentaire'] ?? null);
                    
                    // Mettre à jour la facturation associée si elle existe
                    if ($this->record->facturation) {
                        $this->record->facturation->update([
                            'statut_paiement' => 'paye',
                            'reference_paiement' => $this->record->reference,
                        ]);
                    }
                    
                    // Mettre à jour le statut de l'abonnement si nécessaire
                    if ($this->record->abonnement && $this->record->abonnement->statut !== 'actif') {
                        $this->record->abonnement->update([
                            'statut' => 'actif',
                        ]);
                    }
                    
                    Notification::make()
                        ->title('Paiement validé')
                        ->body('Le paiement a été validé avec succès.')
                        ->success()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('rejeter')
                ->label('Rejeter le paiement')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->necessiteValidation() && auth()->user()->isSuperAdmin())
                ->form([
                    \Filament\Forms\Components\Textarea::make('commentaire')
                        ->label('Motif du rejet')
                        ->required()
                        ->maxLength(1000),
                ])
                ->action(function (array $data) {
                    $this->record->rejeter(auth()->id(), $data['commentaire']);
                    
                    Notification::make()
                        ->title('Paiement rejeté')
                        ->body('Le paiement a été rejeté.')
                        ->danger()
                        ->send();
                        
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
                }),
           
        ];
    }
}
