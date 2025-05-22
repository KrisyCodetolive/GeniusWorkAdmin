<?php

namespace App\Filament\Widgets;

use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TarificationPlansWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Récupérer les plans d'abonnement
        $starterPlan = PlanAbonnement::where('nom', 'like', '%Starter%')->first();
        $sideBizPlan = PlanAbonnement::where('nom', 'like', '%Side Business%')->first();
        $enterprisePlan = PlanAbonnement::where('nom', 'like', '%Enterprise%')->first();
        
        // Compter les abonnements actifs par plan
        $starterCount = $starterPlan ? $this->getAbonnementsCount($starterPlan->id) : 0;
        $sideBizCount = $sideBizPlan ? $this->getAbonnementsCount($sideBizPlan->id) : 0;
        $enterpriseCount = $enterprisePlan ? $this->getAbonnementsCount($enterprisePlan->id) : 0;
        
        // Calculer le revenu total par plan
        $starterRevenue = $starterPlan ? $this->getAbonnementsRevenue($starterPlan->id) : 0;
        $sideBizRevenue = $sideBizPlan ? $this->getAbonnementsRevenue($sideBizPlan->id) : 0;
        $enterpriseRevenue = $enterprisePlan ? $this->getAbonnementsRevenue($enterprisePlan->id) : 0;
        
        // Formater les montants
        $starterRevenueFormatted = number_format($starterRevenue, 0, ',', ' ') . ' FCFA';
        $sideBizRevenueFormatted = number_format($sideBizRevenue, 0, ',', ' ') . ' FCFA';
        $enterpriseRevenueFormatted = number_format($enterpriseRevenue, 0, ',', ' ') . ' FCFA';
        
        return [
            Stat::make('Starter Plan (1-50 utilisateurs)', $starterCount)
                ->description('10,000 FCFA + 100 FCFA/utilisateur')
                ->descriptionIcon('heroicon-o-user-group')
                ->chart($this->getMonthlyTrendData($starterPlan ? $starterPlan->id : null))
                ->color('info'),
                
            Stat::make('Side Business Plan (50-100 utilisateurs)', $sideBizCount)
                ->description('15,000 FCFA + 100 FCFA/utilisateur')
                ->descriptionIcon('heroicon-o-building-office')
                ->chart($this->getMonthlyTrendData($sideBizPlan ? $sideBizPlan->id : null))
                ->color('warning'),
                
            Stat::make('Enterprise Plan (100+ utilisateurs)', $enterpriseCount)
                ->description('30,000 FCFA + 100 FCFA/utilisateur')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->chart($this->getMonthlyTrendData($enterprisePlan ? $enterprisePlan->id : null))
                ->color('success'),
        ];
    }
    
    private function getAbonnementsCount($planId): int
    {
        $query = Abonnement::where('plan_abonnement_id', $planId)
            ->where('statut', 'actif')
            ->where('date_fin', '>', now());
            
        // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        }
        
        return $query->count();
    }
    
    private function getAbonnementsRevenue($planId): float
    {
        $query = Abonnement::where('plan_abonnement_id', $planId)
            ->where('statut', 'actif')
            ->where('date_fin', '>', now());
            
        // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $query->where('entreprise_id', auth()->user()->entreprise_id);
        }
        
        return $query->sum('montant');
    }
    
    private function getMonthlyTrendData($planId): array
    {
        if (!$planId) {
            return array_fill(0, 6, 0);
        }
        
        $data = [];
        
        // Récupérer les données des 6 derniers mois
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            $query = Abonnement::where('plan_abonnement_id', $planId)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month);
                
            // Si l'utilisateur n'est pas un super admin, filtrer par entreprise
            if (!auth()->user()->isSuperAdmin()) {
                $query->where('entreprise_id', auth()->user()->entreprise_id);
            }
            
            $data[] = $query->count();
        }
        
        return $data;
    }
    
    public static function canView(): bool
    {
        return auth()->user()->isSuperAdmin(); // Visible pour tous les utilisateurs
    }
}
