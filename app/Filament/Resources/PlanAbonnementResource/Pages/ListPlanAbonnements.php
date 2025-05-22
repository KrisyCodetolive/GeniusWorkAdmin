<?php

namespace App\Filament\Resources\PlanAbonnementResource\Pages;

use App\Filament\Resources\PlanAbonnementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPlanAbonnements extends ListRecords
{
    protected static string $resource = PlanAbonnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Nouveau Plan'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'tous' => Tab::make('Tous les plans')
                ->icon('heroicon-o-rectangle-stack'),
            'actifs' => Tab::make('Plans actifs')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'actif')),
            'inactifs' => Tab::make('Plans inactifs')
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('statut', 'inactif')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PlanAbonnementResource\Widgets\PlanAbonnementStats::class,
        ];
    }
}
