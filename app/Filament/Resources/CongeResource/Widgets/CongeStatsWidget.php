<?php

namespace App\Filament\Resources\CongeResource\Widgets;

use App\Models\Conge;
use App\Models\TypeConge;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CongeStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Si l'utilisateur est SuperAdmin ou Support, il voit toutes les données
        // Sinon, il ne voit que les données de son entreprise
        $entrepriseId = $user->entreprise_id;
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->isSupport();
        
        // Requête de base pour les congés
        $baseQuery = Conge::query();
        
        // Filtrer par entreprise si l'utilisateur n'est pas SuperAdmin ou Support
        if (!$isSuperAdminOrSupport && $entrepriseId) {
            $baseQuery->whereHas('employeur', function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            });
        }
        
        // Nombre total de demandes de congés
        $totalConges = (clone $baseQuery)->count();
        
        // Nombre de demandes en attente
        $congesEnAttente = (clone $baseQuery)->where('statut', 'en_attente')->count();
        
        // Nombre de demandes approuvées
        $congesApprouves = (clone $baseQuery)->where('statut', 'approuve')->count();
        
        // Nombre de demandes rejetées
        $congesRejetes = (clone $baseQuery)->where('statut', 'rejete')->count();
        
        // Nombre de demandes annulées
        $congesAnnules = (clone $baseQuery)->where('statut', 'annule')->count();
        
        // Taux d'approbation (en pourcentage)
        $tauxApprobation = $totalConges > 0 
            ? round(($congesApprouves / $totalConges) * 100) 
            : 0;
        
        // Demandes de congés ce mois-ci
        $congesCeMois = (clone $baseQuery)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        
        // Congés payés vs non payés
        $congesPayes = (clone $baseQuery)->where('est_paye', true)->count();
        $congesNonPayes = $totalConges - $congesPayes;
        $pourcentageCongesPayes = $totalConges > 0 
            ? round(($congesPayes / $totalConges) * 100) 
            : 0;
        
        // Durée moyenne des congés approuvés
        $dureeMoyenne = (clone $baseQuery)
            ->where('statut', 'approuve')
            ->avg('duree_jours');
        $dureeMoyenne = $dureeMoyenne ? round($dureeMoyenne, 1) : 0;
        
        // Type de congé le plus demandé
        $typeCongePopulaire = null;
        if ($totalConges > 0) {
            $typeCongeId = (clone $baseQuery)
                ->select('type_conge_id', DB::raw('COUNT(*) as total'))
                ->groupBy('type_conge_id')
                ->orderBy('total', 'desc')
                ->first();
                
            if ($typeCongeId) {
                $typeCongePopulaire = TypeConge::find($typeCongeId->type_conge_id);
            }
        }
        
        // Congés à venir dans les 30 prochains jours
        $dateDebut = Carbon::now();
        $dateFin = Carbon::now()->addDays(30);
        $congesAVenir = (clone $baseQuery)
            ->where('statut', 'approuve')
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                    ->orWhere(function ($query) use ($dateDebut, $dateFin) {
                        $query->where('date_debut', '<=', $dateDebut)
                            ->where('date_fin', '>=', $dateFin);
                    });
            })
            ->count();
        
        // Nombre d'employés en congé aujourd'hui
        $employesEnCongeAujourdhui = (clone $baseQuery)
            ->where('statut', 'approuve')
            ->where(function ($query) {
                $today = Carbon::today();
                $query->where('date_debut', '<=', $today)
                    ->where('date_fin', '>=', $today);
            })
            ->distinct('employeur_id')
            ->count('employeur_id');
        
        return [
            Stat::make('Demandes en attente', $congesEnAttente)
                ->description('Nécessitent une validation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($congesEnAttente > 0 ? 'warning' : 'success'),
                
            Stat::make('Demandes approuvées', $congesApprouves)
                ->description($tauxApprobation . '% de taux d\'approbation')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Demandes rejetées', $congesRejetes)
                ->description($congesRejetes > 0 ? 'Demandes non validées' : 'Aucun rejet')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($congesRejetes > 0 ? 'danger' : 'gray'),
                
            Stat::make('Congés ce mois-ci', $congesCeMois)
                ->description('Demandes créées en ' . Carbon::now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),
                
            Stat::make('Durée moyenne', $dureeMoyenne . ' jours')
                ->description('Pour les congés approuvés')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
                
            Stat::make('Congés payés', $pourcentageCongesPayes . '%')
                ->description($congesPayes . ' sur ' . $totalConges . ' demandes')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Type le plus demandé', $typeCongePopulaire ? $typeCongePopulaire->nom : 'Aucun')
                ->description($typeCongePopulaire ? 'Type de congé populaire' : 'Pas assez de données')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
                
            Stat::make('En congé aujourd\'hui', $employesEnCongeAujourdhui)
                ->description('Employés absents ce jour')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('info'),
                
            Stat::make('Congés à venir', $congesAVenir)
                ->description('Dans les 30 prochains jours')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
