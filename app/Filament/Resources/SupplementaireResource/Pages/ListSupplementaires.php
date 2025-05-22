<?php

namespace App\Filament\Resources\SupplementaireResource\Pages;

use App\Filament\Resources\SupplementaireResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSupplementaires extends ListRecords
{
    protected static string $resource = SupplementaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
