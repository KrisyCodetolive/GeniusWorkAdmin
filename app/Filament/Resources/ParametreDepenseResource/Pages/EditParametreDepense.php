<?php

namespace App\Filament\Resources\ParametreDepenseResource\Pages;

use App\Filament\Resources\ParametreDepenseResource;
use Filament\Resources\Pages\EditRecord;

class EditParametreDepense extends EditRecord
{
    protected static string $resource = ParametreDepenseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
