<?php

namespace App\Filament\Resources\PaiementResource\Pages;

use App\Filament\Resources\PaiementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreatePaiement extends CreateRecord
{
    protected static string $resource = PaiementResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ajouter l'ID de l'initiateur
        $data['initiateur_id'] = auth()->id();
        
        // Si l'utilisateur n'est pas un super admin, forcer l'entreprise_id
        if (!auth()->user()->isSuperAdmin()) {
            $data['entreprise_id'] = auth()->user()->entreprise_id;
        }
        
        // Si le paiement est effectué via une passerelle automatique (non manuel), le marquer comme complet
        if ($data['passerelle'] !== 'manuel') {
            $data['statut'] = 'complete';
            $data['date_validation'] = now();
            $data['validateur_id'] = auth()->id();
        }
        
        return $data;
    }
    
    protected function afterCreate(): void
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
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Paiement créé')
            ->body('Le paiement a été créé avec succès.');
    }
}
