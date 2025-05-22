<?php

namespace App\Filament\Resources\MethodePointageResource\Pages;

use App\Filament\Resources\MethodePointageResource;
use App\Models\MethodePointage;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListMethodePointages extends ListRecords
{
    protected static string $resource = MethodePointageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
