<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use Filament\Resources\Pages\Page;
use Filament\Actions;
use App\Models\Departement;
use Illuminate\Support\Facades\Blade;

class ArborescenceDepartement extends Page
{
    protected static string $resource = DepartementResource::class;

    protected static string $view = 'filament.resources.departement-resource.pages.arborescence-departement';

    public ?Departement $record = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    public function getArborescence()
    {
        return $this->record->getArborescence();
    }

    public function getStatistiques()
    {
        return $this->record->getStatistiques();
    }
}
