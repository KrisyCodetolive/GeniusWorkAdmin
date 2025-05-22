<?php

namespace App\Filament\Widgets;

use App\Models\Paiement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaiementsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Si l'utilisateur n'est pas un super admin, montrer seulement ses paiements
        if (!auth()->user()->isSuperAdmin()) {
            $entrepriseId = auth()->user()->entreprise_id;
            
            $totalPaiements = Paiement::where('entreprise_id', $entrepriseId)->count();
            $totalMontant = Paiement::where('entreprise_id', $entrepriseId)
                ->where('statut', 'complete')
                ->sum('montant');
            $paiementsEnAttente = Paiement::where('entreprise_id', $entrepriseId)
                ->where('statut', 'en_attente')
                ->count();
            
            return [
                Stat::make('Total des paiements', $totalPaiements)
                    ->description('Nombre total de paiements')
                    ->descriptionIcon('heroicon-o-banknotes')
                    ->color('primary'),
                Stat::make('Montant total payé', number_format($totalMontant, 0, ',', ' ') . ' XOF')
                    ->description('Montant total des paiements complétés')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
                Stat::make('Paiements en attente', $paiementsEnAttente)
                    ->description('Nombre de paiements en attente')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('warning'),
            ];
        }
        
        // Pour les super admins, montrer les statistiques globales
        $totalPaiements = Paiement::count();
        $totalMontant = Paiement::where('statut', 'complete')->sum('montant');
        $paiementsEnAttente = Paiement::where('statut', 'en_attente')->count();
        
        // Calculer le taux de conversion (paiements complétés / total des paiements)
        $tauxConversion = 0;
        if ($totalPaiements > 0) {
            $tauxConversion = round((Paiement::where('statut', 'complete')->count() / $totalPaiements) * 100);
        }
        
        // Calculer le revenu mensuel moyen sur les 6 derniers mois
        $revenuMensuelMoyen = Paiement::where('statut', 'complete')
            ->where('date_paiement', '>=', now()->subMonths(6))
            ->select(DB::raw('YEAR(date_paiement) as year, MONTH(date_paiement) as month, SUM(montant) as total'))
            ->groupBy('year', 'month')
            ->get()
            ->avg('total') ?: 0;
            
        // Calculer la répartition des méthodes de paiement
        $methodesRepartition = Paiement::where('statut', 'complete')
            ->select('methode', DB::raw('COUNT(*) as count'))
            ->groupBy('methode')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get()
            ->pluck('count', 'methode')
            ->toArray();
            
        $methodesLabels = [
            'card' => 'Carte bancaire',
            'mobile_money' => 'Mobile Money',
            'virement' => 'Virement',
            'especes' => 'Espèces',
            'cheque' => 'Chèque',
            'autre' => 'Autre',
        ];
        
        $topMethode = 'N/A';
        if (!empty($methodesRepartition)) {
            $topMethode = $methodesLabels[array_key_first($methodesRepartition)] ?? array_key_first($methodesRepartition);
        }
        
        return [
            Stat::make('Taux de conversion', $tauxConversion . '%')
                ->description('Pourcentage des paiements complétés')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->chart([
                    $tauxConversion,
                    100 - $tauxConversion,
                ])
                ->color($tauxConversion >= 70 ? 'success' : ($tauxConversion >= 50 ? 'warning' : 'danger')),
            Stat::make('Montant total collecté', number_format($totalMontant, 0, ',', ' ') . ' XOF')
                ->description('Montant total des paiements complétés')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Revenu mensuel moyen', number_format($revenuMensuelMoyen, 0, ',', ' ') . ' XOF')
                ->description('Sur les 6 derniers mois')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),
            Stat::make('Méthode de paiement préférée', $topMethode)
                ->description('Méthode la plus utilisée')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('info'),
            Stat::make('Paiements en attente de validation', $paiementsEnAttente)
                ->description('Paiements nécessitant une validation')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }
    
    public static function canView(): bool
    {
        return true; // Visible pour tous les utilisateurs
    }
}
