<?php

namespace App\Filament\Resources\CodePromoResource\Pages;

use App\Filament\Resources\CodePromoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCodePromo extends CreateRecord
{
    protected static string $resource = CodePromoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Initialiser le nombre d'utilisations à 0
        $data['nombre_utilisations'] = 0;
        
        return $data;
    }
}
