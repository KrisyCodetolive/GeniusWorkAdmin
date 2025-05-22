<?php

namespace App\Filament\Pages;

use App\Filament\Resources\DepartementResource\Widgets\DepartementStatsWidget;
use App\Filament\Resources\DepartementResource\Widgets\DepartementHierarchieWidget;
use App\Filament\Resources\DepartementResource\Widgets\DepartementFilialeWidget;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentIcon;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Auth;

class DepartementStatsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Statistiques des départements';

    protected static ?string $title = 'Statistiques des départements';

    protected static ?string $slug = 'departements-stats';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.departement-stats-page';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => route('filament.admin.resources.departements.index')),
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
}
