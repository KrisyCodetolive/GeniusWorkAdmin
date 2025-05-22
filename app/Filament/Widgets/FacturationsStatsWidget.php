<?php

namespace App\Filament\Widgets;

use App\Models\Facturation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacturationsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Si l'utilisateur n'est pas un super admin, montrer seulement ses facturations
        if (!auth()->user()->isSuperAdmin()) {
            $entrepriseId = auth()->user()->entreprise_id;
            
            $totalFacturations = Facturation::where('entreprise_id', $entrepriseId)->count();
            $totalPaye = Facturation::where('entreprise_id', $entrepriseId)
                ->where('statut_paiement', 'paye')
                ->sum('montant_ttc');
            $totalImpaye = Facturation::where('entreprise_id', $entrepriseId)
                ->where('statut_paiement', 'impaye')
                ->sum('montant_ttc');
            $facturesEchues = Facturation::where('entreprise_id', $entrepriseId)
                ->where('statut_paiement', 'impaye')
                ->where('date_echeance', '<', now())
                ->count();
            
            return [
          
            ];
        }
        
        // Pour les super admins, montrer les statistiques globales
        $totalFacturations = Facturation::count();
        $totalPaye = Facturation::where('statut_paiement', 'paye')->sum('montant_ttc');
        $totalImpaye = Facturation::where('statut_paiement', 'impaye')->sum('montant_ttc');
        
        // Calculer le taux de recouvrement
        $tauxRecouvrement = 0;
        $totalFacture = $totalPaye + $totalImpaye;
        if ($totalFacture > 0) {
            $tauxRecouvrement = round(($totalPaye / $totalFacture) * 100);
        }
        
        // Calculer les factures échues
        $facturesEchues = Facturation::where('statut_paiement', 'impaye')
            ->where('date_echeance', '<', now())
            ->count();
            
        // Calculer le revenu mensuel moyen sur les 6 derniers mois
        $revenuMensuelMoyen = Facturation::where('statut_paiement', 'paye')
            ->where('date_facturation', '>=', now()->subMonths(6))
            ->select(DB::raw('YEAR(date_facturation) as year, MONTH(date_facturation) as month, SUM(montant_ttc) as total'))
            ->groupBy('year', 'month')
            ->get()
            ->avg('total') ?: 0;
        
        return [
            Stat::make('Taux de recouvrement', $tauxRecouvrement . '%')
                ->description('Pourcentage des factures payées')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->chart([
                    $tauxRecouvrement,
                    100 - $tauxRecouvrement,
                ])
                ->color($tauxRecouvrement >= 70 ? 'success' : ($tauxRecouvrement >= 50 ? 'warning' : 'danger')),
            Stat::make('Montant total payé', number_format($totalPaye, 0, ',', ' ') . ' XOF')
                ->description('Montant total des factures payées')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Montant total impayé', number_format($totalImpaye, 0, ',', ' ') . ' XOF')
                ->description('Montant total des factures impayées')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('danger'),
            Stat::make('Revenu mensuel moyen', number_format($revenuMensuelMoyen, 0, ',', ' ') . ' XOF')
                ->description('Sur les 6 derniers mois')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),
            Stat::make('Factures échues', $facturesEchues)
                ->description('Factures impayées dont la date d\'échéance est dépassée')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
    
    public static function canView(): bool
    {
        return true; // Visible pour tous les utilisateurs
    }
}
