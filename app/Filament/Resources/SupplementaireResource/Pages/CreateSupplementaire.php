<?php

namespace App\Filament\Resources\SupplementaireResource\Pages;

use App\Filament\Resources\SupplementaireResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplementaire extends CreateRecord
{
    protected static string $resource = SupplementaireResource::class;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
