<?php

namespace App\Filament\Resources\TypeCongeResource\Pages;

use App\Filament\Resources\TypeCongeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTypeConge extends EditRecord
{
    protected static string $resource = TypeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
