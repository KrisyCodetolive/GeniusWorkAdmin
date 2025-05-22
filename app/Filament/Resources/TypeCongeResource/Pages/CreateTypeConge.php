<?php

namespace App\Filament\Resources\TypeCongeResource\Pages;

use App\Filament\Resources\TypeCongeResource;
use App\Traits\HasEntrepriseScope;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTypeConge extends CreateRecord
{
    use HasEntrepriseScope;
    
    protected static string $resource = TypeCongeResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return static::mutateFormDataWithEntreprise($data);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
