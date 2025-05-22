<?php

namespace App\Filament\Resources\PlanAbonnementResource\Widgets;

use App\Models\PlanAbonnement;
use App\Models\Abonnement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlanAbonnementStats extends BaseWidget
{
    protected function getStats(): array
    {
        $plansActifs = PlanAbonnement::where('plan_abonnements.statut', 'actif')->count();
        $totalAbonnements = Abonnement::count();
        $revenuMensuel = Abonnement::whereHas('planAbonnement', function ($query) {
            $query->where('periode_facturation', 'mensuel');
        })->sum('montant');

        return [
            Stat::make('Plans Actifs', $plansActifs)
                ->description('Nombre total de plans disponibles')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->chart([7, 3, 4, 5, 6, $plansActifs])
                ->color('success'),

            Stat::make('Abonnements Actifs', $totalAbonnements)
                ->description('Nombre total d\'abonnements')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([2, 4, 6, 8, 10, $totalAbonnements])
                ->color('info'),

            Stat::make('Revenu Mensuel', number_format($revenuMensuel, 2) . ' XOF')
                ->description('Revenu mensuel récurrent')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->chart([1000, 2000, 3000, 4000, 5000, $revenuMensuel])
                ->color('warning'),
        ];
    }
}
