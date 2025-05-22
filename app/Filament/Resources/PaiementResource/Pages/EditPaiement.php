<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditPaiement extends EditRecord
{
    protected static string $resource = PaiementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
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
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si le paiement passe de en_attente à complete, mettre à jour la date de validation et le validateur
        if ($this->record->statut === 'en_attente' && $data['statut'] === 'complete') {
            $data['date_validation'] = now();
            $data['validateur_id'] = auth()->id();
        }
        
        return $data;
    }
    
    protected function afterSave(): void
    {
        // Si le paiement est lié à une facturation et qu'il est complet, mettre à jour la facturation
        if ($this->record->facturation_id && $this->record->statut === 'complete') {
            $this->record->facturation->update([
                'statut_paiement' => 'paye',
                'reference_paiement' => $this->record->reference,
            ]);
            
            // Mettre à jour le statut de l'abonnement si nécessaire
            if ($this->record->abonnement && $this->record->abonnement->statut !== 'actif') {
                $this->record->abonnement->update([
                    'statut' => 'actif',
                ]);
            }
        }
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
    
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Paiement mis à jour')
            ->body('Le paiement a été mis à jour avec succès.');
    }
}
