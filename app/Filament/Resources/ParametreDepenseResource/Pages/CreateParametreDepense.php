<?php

namespace App\Filament\Resources\ParametreDepenseResource\Pages;

use App\Filament\Resources\ParametreDepenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParametreDepense extends CreateRecord
{
    protected static string $resource = ParametreDepenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ParametreDepenseResource::forcerEntreprise($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
