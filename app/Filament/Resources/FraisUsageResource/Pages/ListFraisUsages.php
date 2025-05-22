<?php

namespace App\Filament\Resources\FraisUsageResource\Pages;

use App\Filament\Resources\FraisUsageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFraisUsages extends ListRecords
{
    protected static string $resource = FraisUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
