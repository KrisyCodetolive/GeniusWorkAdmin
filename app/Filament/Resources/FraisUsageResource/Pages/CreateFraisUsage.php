<?php

namespace App\Filament\Resources\FraisUsageResource\Pages;

use App\Filament\Resources\FraisUsageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFraisUsage extends CreateRecord
{
    protected static string $resource = FraisUsageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculer le montant total si ce n'est pas déjà fait
        if (empty($data['montant_total']) && !empty($data['prix_unitaire']) && !empty($data['quantite'])) {
            $data['montant_total'] = $data['prix_unitaire'] * $data['quantite'];
        }
        
        return $data;
    }
}
