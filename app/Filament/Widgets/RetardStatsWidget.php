<?php

namespace App\Filament\Widgets;

use App\Models\Presence;
use App\Models\Employeur;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RetardStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    
    protected function getStats(): array
    {
        // Récupérer les statistiques des retards
        $today = Carbon::today();
        $startOfMonth = Carbon::today()->startOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();
        
        // Nombre de retards aujourd'hui
        $retardsAujourdhui = Presence::where('statut', 'retard')
            ->whereDate('date_heure_entree', $today)
            ->count();
            
        // Nombre de retards ce mois-ci
        $retardsMois = Presence::where('statut', 'retard')
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->count();
            
        // Durée moyenne des retards ce mois-ci
        $moyenneRetards = Presence::where('statut', 'retard')
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->avg('retard');
            
        // Employé avec le plus de retards ce mois-ci
        $employeRetardataire = DB::table('presences')
            ->select('user_id', DB::raw('count(*) as total_retards'))
            ->where('statut', 'retard')
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->groupBy('user_id')
            ->orderBy('total_retards', 'desc')
            ->first();
            
        $nomEmployeRetardataire = 'Aucun';
        if ($employeRetardataire) {
            $user = \App\Models\User::find($employeRetardataire->user_id);
            if ($user) {
                $nomEmployeRetardataire = $user->nom . ' ' . $user->prenom;
            }
        }
        
        return [
            Stat::make('Retards aujourd\'hui', $retardsAujourdhui)
                ->description('Nombre de retards enregistrés aujourd\'hui')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Retards du mois', $retardsMois)
                ->description('Total des retards pour le mois en cours')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger'),
                
            Stat::make('Durée moyenne', number_format($moyenneRetards ?? 0, 0) . ' min')
                ->description('Durée moyenne des retards ce mois-ci')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),
                
            Stat::make('Employé le plus en retard', $nomEmployeRetardataire)
                ->description('Employé avec le plus de retards ce mois-ci')
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),
        ];
    }
}
