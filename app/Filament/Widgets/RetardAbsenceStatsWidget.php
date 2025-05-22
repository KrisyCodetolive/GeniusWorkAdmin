<?php

namespace App\Filament\Widgets;

use App\Models\Presence;
use App\Models\Employeur;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RetardAbsenceStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    
    protected function getStats(): array
    {
        // Récupérer les statistiques des retards et absences
        $today = Carbon::today();
        $startOfMonth = Carbon::today()->startOfMonth();
        $endOfMonth = Carbon::today()->endOfMonth();
        
        // Statistiques des retards
        $retardsAujourdhui = Presence::where('statut', 'retard')
            ->whereDate('date_heure_entree', $today)
            ->count();
            
        $retardsMois = Presence::where('statut', 'retard')
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->count();
            
        $moyenneRetards = Presence::where('statut', 'retard')
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->avg('retard');
            
        // Statistiques des absences
        $absencesAujourdhui = Presence::where('statut', 'absent')
            ->whereDate('date_heure_entree', $today)
            ->count();
            
        $absencesMois = Presence::where('statut', 'absent')
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->count();
            
        // Statistiques combinées
        $totalIncidentsAujourdhui = $retardsAujourdhui + $absencesAujourdhui;
        $totalIncidentsMois = $retardsMois + $absencesMois;
            
        // Employé avec le plus d'incidents ce mois-ci
        try {
            $employeProblematique = DB::table('presences')
                ->select(DB::raw('`user_id`, COUNT(*) as total_incidents'))
                ->whereIn('statut', ['retard', 'absent'])
                ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
                ->groupBy('user_id')
                ->orderBy('total_incidents', 'desc')
                ->first();
        } catch (\Exception $e) {
            // Fallback en cas d'erreur SQL
            $employeProblematique = null;
        }
            
        $nomEmployeProblematique = 'Aucun';
        $detailsIncidents = '';
        if ($employeProblematique && isset($employeProblematique->user_id)) {
            $user = User::find($employeProblematique->user_id);
            if ($user) {
                $nomEmployeProblematique = $user->nom . ' ' . $user->prenom;
                
                // Détails des incidents pour cet employé
                $nbRetards = Presence::where('user_id', $employeProblematique->user_id)
                    ->where('statut', 'retard')
                    ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
                    ->count();
                    
                $nbAbsences = Presence::where('user_id', $employeProblematique->user_id)
                    ->where('statut', 'absent')
                    ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
                    ->count();
                    
                $detailsIncidents = "({$nbRetards} retards, {$nbAbsences} absences)";
            }
        }
        
        // Taux de validation
        $totalIncidents = Presence::whereIn('statut', ['retard', 'absent'])
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->count();
            
        $incidentsValides = Presence::whereIn('statut', ['retard', 'absent'])
            ->whereBetween('date_heure_entree', [$startOfMonth, $endOfMonth])
            ->whereNotNull('validateur_id')
            ->count();
            
        $tauxValidation = $totalIncidents > 0 ? round(($incidentsValides / $totalIncidents) * 100) : 0;
        
        return [
            // Première ligne - Statistiques du jour
            Stat::make('Retards aujourd\'hui', $retardsAujourdhui)
                ->description('Nombre de retards enregistrés aujourd\'hui')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Absences aujourd\'hui', $absencesAujourdhui)
                ->description('Nombre d\'absences enregistrées aujourd\'hui')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
                
            Stat::make('Total incidents aujourd\'hui', $totalIncidentsAujourdhui)
                ->description('Total des retards et absences aujourd\'hui')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('gray'),
                
            // Deuxième ligne - Statistiques du mois
            Stat::make('Retards du mois', $retardsMois)
                ->description('Total des retards pour le mois en cours')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
                
            Stat::make('Absences du mois', $absencesMois)
                ->description('Total des absences pour le mois en cours')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('danger'),
                
            Stat::make('Durée moyenne des retards', number_format($moyenneRetards ?? 0, 0) . ' min')
                ->description('Durée moyenne des retards ce mois-ci')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),
                
            // Troisième ligne - Statistiques avancées
            Stat::make('Employé le plus problématique', $nomEmployeProblematique)
                ->description($detailsIncidents)
                ->descriptionIcon('heroicon-m-user')
                ->color('info'),
                
            Stat::make('Taux de validation', $tauxValidation . '%')
                ->description('Pourcentage d\'incidents validés ce mois-ci')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($tauxValidation > 80 ? 'success' : ($tauxValidation > 50 ? 'warning' : 'danger')),
                
            Stat::make('Total incidents du mois', $totalIncidentsMois)
                ->description('Total des retards et absences ce mois-ci')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('gray'),
        ];
    }
}
