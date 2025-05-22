<?php

namespace App\Filament\Resources\JourResource\Pages;

use App\Filament\Resources\JourResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJours extends ListRecords
{
    protected static string $resource = JourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
