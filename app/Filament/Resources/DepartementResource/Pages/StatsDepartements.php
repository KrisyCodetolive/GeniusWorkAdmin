<?php

namespace App\Filament\Resources\DepartementResource\Pages;

use App\Filament\Resources\DepartementResource;
use App\Filament\Resources\DepartementResource\Widgets\DepartementStatsWidget;
use App\Filament\Resources\DepartementResource\Widgets\DepartementHierarchieWidget;
use App\Filament\Resources\DepartementResource\Widgets\DepartementFilialeWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StatsDepartements extends ListRecords
{
    protected static string $resource = DepartementResource::class;

    protected static ?string $title = 'Statistiques des départements';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => DepartementResource::getUrl()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DepartementStatsWidget::class,
            DepartementHierarchieWidget::class,
            DepartementFilialeWidget::class,
        ];
    }

    protected function isTableDisabled(): bool
    {
        return true;
    }

    protected function getTableQuery(): ?Builder
    {
        $query = parent::getTableQuery();

        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        $user = Auth::user();
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        return $query;
    }
}
