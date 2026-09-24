<?php

namespace App\Filament\Resources\CategorieDepenseResource\Pages;

use App\Filament\Resources\CategorieDepenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategorieDepense extends CreateRecord
{
    protected static string $resource = CategorieDepenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CategorieDepenseResource::forcerEntreprise($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
