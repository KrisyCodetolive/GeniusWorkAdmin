<?php

namespace App\Filament\Resources\SoldeCongeResource\Pages;

use App\Filament\Resources\SoldeCongeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoldeConge extends EditRecord
{
    protected static string $resource = SoldeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
