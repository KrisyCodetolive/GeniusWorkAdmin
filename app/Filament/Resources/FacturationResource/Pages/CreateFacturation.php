<?php

namespace App\Filament\Resources\FacturationResource\Pages;

use App\Filament\Resources\FacturationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateFacturation extends CreateRecord
{
    protected static string $resource = FacturationResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculer automatiquement les montants TVA et TTC
        $data['montant_tva'] = $data['montant_ht'] * ($data['taux_tva'] / 100);
        $data['montant_ttc'] = $data['montant_ht'] + $data['montant_tva'];
        
        // Si l'utilisateur n'est pas un super admin, forcer l'entreprise_id
        if (!auth()->user()->isSuperAdmin()) {
            $data['entreprise_id'] = auth()->user()->entreprise_id;
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Facturation créée')
            ->body('La facturation a été créée avec succès.');
    }
}
