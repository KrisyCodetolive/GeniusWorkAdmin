<?php

namespace App\Filament\Resources\DemandeDepenseResource\Pages;

use App\Filament\Resources\DemandeDepenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDemandeDepense extends EditRecord
{
    protected static string $resource = DemandeDepenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
