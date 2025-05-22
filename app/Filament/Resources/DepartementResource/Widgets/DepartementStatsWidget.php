<?php

namespace App\Filament\Resources\DepartementResource\Widgets;

use App\Models\Departement;
use App\Models\Employeur;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartementStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Departement::query();
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        $totalDepartements = $query->count();
        
        // Statistiques par statut
        $actifs = (clone $query)->where('statut', Departement::STATUT_ACTIF)->count();
        $inactifs = (clone $query)->where('statut', Departement::STATUT_INACTIF)->count();
        
        // Statistiques par niveau hiérarchique
        $niveaux = (clone $query)
            ->select('niveau', DB::raw('count(*) as total'))
            ->groupBy('niveau')
            ->orderBy('niveau')
            ->pluck('total', 'niveau')
            ->toArray();
        
        // Nombre moyen d'employés par département
        $moyenneEmployes = Employeur::query()
            ->when(!$user->isSuperAdmin() && !$user->isSupport(), function ($query) use ($user) {
                $query->where('entreprise_id', $user->entreprise_id);
            })
            ->whereNotNull('departement_id')
            ->groupBy('departement_id')
            ->select('departement_id', DB::raw('count(*) as total'))
            ->get()
            ->avg('total');
        
        return [
            Stat::make('Total des départements', $totalDepartements)
                ->description('Tous les départements')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
                
            Stat::make('Départements actifs', $actifs)
                ->description($totalDepartements > 0 ? round(($actifs / $totalDepartements) * 100) . '% du total' : '0%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([
                    $actifs,
                    $inactifs,
                ]),
                
            Stat::make('Moyenne d\'employés', round($moyenneEmployes, 1))
                ->description('Par département')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('warning'),
        ];
    }
}
