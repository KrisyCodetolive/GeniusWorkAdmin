<?php

namespace App\Filament\Resources\ParametreDepenseResource\Pages;

use App\Filament\Resources\ParametreDepenseResource;
use App\Models\ParametreDepense;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListParametreDepenses extends ListRecords
{
    protected static string $resource = ParametreDepenseResource::class;

    public function mount(): void
    {
        // Un administrateur d'entreprise trouve toujours ses paramètres, avec les valeurs par défaut.
        if ($entrepriseId = auth()->user()->entreprise_id) {
            ParametreDepense::pour($entrepriseId);
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
