<?php

namespace App\Filament\Widgets;

use App\Models\Conge;
use App\Models\SoldeConge;
use App\Models\TypeConge;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CongesStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        // Récupération des statistiques
        $totalConges = Conge::count();
        $congesEnAttente = Conge::where('statut', 'en_attente')->count();
        $congesApprouves = Conge::where('statut', 'approuve')->count();
        
        // Calcul du taux d'approbation
        $tauxApprobation = $totalConges > 0 
            ? round(($congesApprouves / $totalConges) * 100, 1) 
            : 0;
            
        // Calcul de la moyenne des jours de congés pris par employé
        $moyenneJoursParEmploye = Conge::where('statut', 'approuve')
            ->select('employeur_id', DB::raw('SUM(duree_jours) as total_jours'))
            ->groupBy('employeur_id')
            ->get()
            ->avg('total_jours') ?? 0;
            
        // Nombre de congés pris ce mois-ci
        $congesMoisCourant = Conge::where('statut', 'approuve')
            ->whereMonth('date_debut', Carbon::now()->month)
            ->whereYear('date_debut', Carbon::now()->year)
            ->count();
            
        return [
            Stat::make('Total des demandes', $totalConges)
                ->description('Toutes les demandes de congés')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
                
            Stat::make('Demandes en attente', $congesEnAttente)
                ->description('Nécessitent une validation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($congesEnAttente > 10 ? 'warning' : 'primary'),
                
            Stat::make('Taux d\'approbation', $tauxApprobation . '%')
                ->description('Des demandes sont approuvées')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Moyenne par employé', number_format($moyenneJoursParEmploye, 1) . ' jours')
                ->description('De congés pris par employé')
                ->descriptionIcon('heroicon-m-user')
                ->color('primary'),
                
            Stat::make('Congés ce mois-ci', $congesMoisCourant)
                ->description('Demandes approuvées pour ' . Carbon::now()->locale('fr')->monthName)
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
        ];
    }
}
