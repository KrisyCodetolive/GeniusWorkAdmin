<?php

namespace App\Filament\Resources\EntrepriseResource\Pages;

use App\Filament\Resources\EntrepriseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEnterprises extends ListRecords
{
    protected static string $resource = EntrepriseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EntrepriseResource\Widgets\EntrepriseStats::class,
           // EntrepriseResource\Widgets\EntreprisesChart::class,
        ];
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-building-office-2';
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Aucune entreprise trouvée';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Vous pouvez créer une entreprise en cliquant sur le bouton ci-dessous.';
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Créer une entreprise')
                ->icon('heroicon-o-plus'),
        ];
    }
}
