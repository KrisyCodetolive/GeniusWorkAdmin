<?php

namespace App\Filament\Resources\EquipeResource\Pages;

use App\Filament\Resources\EquipeResource;
use App\Filament\Actions\GenerateEquipesExemplesAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipes extends ListRecords
{
    protected static string $resource = EquipeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            GenerateEquipesExemplesAction::make(),
        ];
    }
}
