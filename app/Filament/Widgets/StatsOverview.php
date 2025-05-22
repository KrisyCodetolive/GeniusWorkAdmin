<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Employés Présents', '0')
                ->description('Aujourd\'hui')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            
            Stat::make('Employés en Congé', '0')
                ->description('Cette semaine')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
            
            Stat::make('Retards', '0')
                ->description('Cette semaine')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
