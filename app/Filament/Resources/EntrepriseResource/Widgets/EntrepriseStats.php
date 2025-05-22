<?php

namespace App\Filament\Resources\EntrepriseResource\Widgets;

use App\Models\Entreprise;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EntrepriseStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalEntreprises = Entreprise::count();
        $activeEntreprises = Entreprise::whereHas('abonnements', function ($query) {
            $query->where('statut', 'actif');
        })->count();
        $inactiveEntreprises = $totalEntreprises - $activeEntreprises;

        return [
            Stat::make('Total des entreprises', $totalEntreprises)
                ->description('Nombre total d\'entreprises')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('Entreprises actives', $activeEntreprises)
                ->description('Entreprises avec un abonnement actif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Entreprises inactives', $inactiveEntreprises)
                ->description('Entreprises sans abonnement actif')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
