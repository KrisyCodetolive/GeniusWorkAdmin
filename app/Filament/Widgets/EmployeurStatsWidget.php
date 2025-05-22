<?php

namespace App\Filament\Widgets;

use App\Models\Employeur;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeurStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Employeur::query();
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        $totalEmployes = $query->count();
        
        // Statistiques par statut
        $actifs = (clone $query)->where('statut', 'actif')->count();
        $inactifs = (clone $query)->where('statut', 'inactif')->count();
        $enConge = (clone $query)->where('statut', 'conge')->count();
        
        // Statistiques par type de contrat
        $cdi = (clone $query)->where('type_contrat', 'cdi')->count();
        $cdd = (clone $query)->where('type_contrat', 'cdd')->count();
        $stages = (clone $query)->where('type_contrat', 'stage')->count();
        
        // Calcul des pourcentages pour les descriptions
        $pourcentageActifs = $totalEmployes > 0 ? round(($actifs / $totalEmployes) * 100) : 0;
        $pourcentageCDI = $totalEmployes > 0 ? round(($cdi / $totalEmployes) * 100) : 0;
        
        return [
            Stat::make('Total des employés', $totalEmployes)
                ->description('Tous les employés enregistrés')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
                
            Stat::make('Employés actifs', $actifs)
                ->description($pourcentageActifs . '% du total des employés')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([
                    $actifs,
                    $inactifs + $enConge,
                ]),
                
            Stat::make('En CDI', $cdi)
                ->description($pourcentageCDI . '% du total des employés')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->chart([
                    $cdi,
                    $cdd,
                    $stages,
                ]),
        ];
    }
}
