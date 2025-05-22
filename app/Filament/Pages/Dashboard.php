<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\SaasStatsWidget;
use App\Filament\Widgets\EntrepriseStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        $user = Auth::user();
        
        // Pour les SuperAdmin et Support, afficher le widget SaasStatsWidget en premier
        if ($user && ($user->isSuperAdmin() || $user->isSupport())) {
            return [
                SaasStatsWidget::class,
                EntrepriseStatsWidget::class,
                StatsOverview::class,
            ];
        }
        
        // Pour les autres utilisateurs, afficher uniquement EntrepriseStatsWidget et StatsOverview
        return [
            EntrepriseStatsWidget::class,
            StatsOverview::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}
