<?php

namespace App\Filament\Resources\SoldeCongeResource\Pages;

use App\Filament\Resources\SoldeCongeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoldeConges extends ListRecords
{
    protected static string $resource = SoldeCongeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
