<?php

namespace App\Filament\Widgets;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AbonnementsStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Si l'utilisateur n'est pas un super admin, montrer uniquement les stats de son entreprise
        if (!auth()->user()->isSuperAdmin()) {
            $entrepriseId = auth()->user()->entreprise_id;
            
            $abonnementActif = Abonnement::where('entreprise_id', $entrepriseId)
                ->where('statut', 'actif')
                ->where('date_fin', '>', now())
                ->latest('date_debut')
                ->first();
                
            if (!$abonnementActif) {
                return [
                    Stat::make('Statut de l\'abonnement', 'Inactif')
                        ->description('Aucun abonnement actif')
                        ->descriptionIcon('heroicon-o-exclamation-circle')
                        ->color('danger'),
                    Stat::make('Jours restants', '0')
                        ->description('Abonnement expiré ou inactif')
                        ->descriptionIcon('heroicon-o-clock')
                        ->color('danger'),
                    Stat::make('Nombre d\'employés', Entreprise::find($entrepriseId)->employeurs()->count())
                        ->description('Employés enregistrés')
                        ->descriptionIcon('heroicon-o-users')
                        ->color('primary'),
                ];
            }
            
            $joursRestants = (int) now()->diffInDays($abonnementActif->date_fin, false);
            $planNom = $abonnementActif->planAbonnement->nom;
            $dateFinFormatee = $abonnementActif->date_fin->format('d/m/Y');
            
            return [
                Stat::make('Plan d\'abonnement', $planNom)
                    ->description('Expire le ' . $dateFinFormatee)
                    ->descriptionIcon('heroicon-o-calendar')
                    ->color('success'),
                Stat::make('Jours restants', $joursRestants > 0 ? (int) $joursRestants : '0')
                    ->description($joursRestants <= 0 ? 'Abonnement expiré' : ($joursRestants <= 7 ? 'Expiration proche' : 'Jours avant expiration'))
                    ->descriptionIcon('heroicon-o-clock')
                    ->color($joursRestants <= 0 ? 'danger' : ($joursRestants <= 7 ? 'warning' : 'success')),
                Stat::make('Montant', $abonnementActif->getMontantFormate())
                    ->description($abonnementActif->type_periode === 'mensuel' ? 'Mensuel' : 'Annuel')
                    ->descriptionIcon('heroicon-o-currency-dollar')
                    ->color('primary'),
            ];
        }
        
        // Stats pour super admin
        return [
            Stat::make('Abonnements actifs', Abonnement::where('statut', 'actif')->where('date_fin', '>', now())->count())
                ->description('Abonnements en cours')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success')
                ->chart(
                    $this->getAbonnementsParMois()
                ),
            Stat::make('Abonnements expirant bientôt', Abonnement::where('statut', 'actif')->whereBetween('date_fin', [now(), now()->addDays(7)])->count())
                ->description('Dans les 7 prochains jours')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('Revenu mensuel', $this->getRevenuMensuel() . ' XOF')
                ->description('Basé sur les abonnements actifs')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary')
                ->chart(
                    $this->getRevenuParMois()
                ),
        ];
    }
    
    private function getAbonnementsParMois(): array
    {
        $stats = DB::table('abonnements')
            ->select(DB::raw('MONTH(created_at) as mois'), DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();
            
        $data = array_fill(0, 12, 0);
        
        foreach ($stats as $stat) {
            $data[$stat->mois - 1] = $stat->total;
        }
        
        return $data;
    }
    
    private function getRevenuMensuel(): string
    {
        $revenu = Abonnement::where('statut', 'actif')
            ->where('date_fin', '>', now())
            ->where('type_periode', 'mensuel')
            ->sum('montant');
            
        $revenuAnnuel = Abonnement::where('statut', 'actif')
            ->where('date_fin', '>', now())
            ->where('type_periode', 'annuel')
            ->sum('montant');
            
        // Convertir le revenu annuel en équivalent mensuel
        $revenuAnnuelMensuel = $revenuAnnuel / 12;
        
        return number_format($revenu + $revenuAnnuelMensuel, 0, ',', ' ');
    }
    
    private function getRevenuParMois(): array
    {
        $stats = DB::table('abonnements')
            ->select(DB::raw('MONTH(created_at) as mois'), DB::raw('SUM(montant) as total'))
            ->whereYear('created_at', date('Y'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();
            
        $data = array_fill(0, 12, 0);
        
        foreach ($stats as $stat) {
            $data[$stat->mois - 1] = $stat->total;
        }
        
        return $data;
    }
}
