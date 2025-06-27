<?php

namespace App\Filament\Resources\EquipeResource\Pages;

use App\Filament\Resources\EquipeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEquipe extends CreateRecord
{
    protected static string $resource = EquipeResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
