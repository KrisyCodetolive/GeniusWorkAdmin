<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\EmployeurStatsWidget;
use App\Filament\Widgets\EmployeurDepartementWidget;
use App\Filament\Widgets\EmployeurTendanceWidget;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentIcon;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Auth;

class EmployeurStatsPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Statistiques des employés';

    protected static ?string $title = 'Statistiques des employés';

    protected static ?string $slug = 'employeurs-stats';

    protected static bool $shouldRegisterNavigation = false;


    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.employeur-stats-page';

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
                ->url(fn () => route('filament.admin.resources.employeurs.index')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeurStatsWidget::class,
            EmployeurDepartementWidget::class,
            EmployeurTendanceWidget::class,
        ];
    }
}
