<?php

namespace App\Filament\Resources\SiteResource\Widgets;

use App\Models\Site;
use App\Models\Employeur;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SiteStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
    
    protected function getStats(): array
    {
        $user = Auth::user();
        $query = Site::query();
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        $totalSites = $query->count();
        
        // Statistiques par statut
        $actifs = (clone $query)->where('statut', 'actif')->count();
        $inactifs = (clone $query)->where('statut', 'inactif')->count();
        
        // Statistiques par geofencing
        $avecGeofencing = (clone $query)->where('has_geofencing', true)->count();
        
        // Nombre moyen d'employés par site
        $sites = (clone $query)->get();
        $moyenneEmployes = 0;
        
        if ($totalSites > 0) {
            $totalEmployes = 0;
            foreach ($sites as $site) {
                $totalEmployes += Employeur::where('entreprise_id', $site->entreprise_id)->count();
            }
            $moyenneEmployes = $totalEmployes / $totalSites;
        }
        
        return [
            Stat::make('Total des sites', $totalSites)
                ->description('Tous les sites enregistrés')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
                
            Stat::make('Sites actifs', $actifs)
                ->description($totalSites > 0 ? round(($actifs / $totalSites) * 100) . '% du total' : '0%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([
                    $actifs,
                    $inactifs,
                ]),
                
            Stat::make('Sites avec geofencing', $avecGeofencing)
                ->description($totalSites > 0 ? round(($avecGeofencing / $totalSites) * 100) . '% du total' : '0%')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('warning'),
        ];
    }
}
