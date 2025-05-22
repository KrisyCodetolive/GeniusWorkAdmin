<?php

namespace App\Filament\Resources\CodePromoResource\Pages;

use App\Filament\Resources\CodePromoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCodePromos extends ListRecords
{
    protected static string $resource = CodePromoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
