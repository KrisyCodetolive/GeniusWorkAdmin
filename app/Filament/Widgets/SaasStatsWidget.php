<?php

namespace App\Filament\Widgets;

use App\Models\Entreprise;
use App\Models\Employeur;
use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SaasStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected static ?int $sort = 0; // Priorité plus élevée que EntrepriseStatsWidget

    // Cette fonction permet de déterminer si le widget doit être affiché
    public static function canView(): bool
    {
        $user = Auth::user();
        
        // Afficher uniquement pour les SuperAdmin et Support
        return $user && ($user->isSuperAdmin() || $user->isSupport());
    }

    protected function getStats(): array
    {
        // Utiliser le cache pour éviter de recalculer les statistiques trop souvent
        $cacheKey = "saas_stats_" . date('Y-m-d_H');
        $cacheDuration = 60; // minutes

        return Cache::remember($cacheKey, $cacheDuration, function () {
            // Dates importantes pour les calculs
            $today = Carbon::today();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();
            $startOfYear = Carbon::now()->startOfYear();
            $endOfYear = Carbon::now()->endOfYear();

            // ===== STATISTIQUES DES ENTREPRISES =====
            
            // Nombre total d'entreprises
            $totalEntreprises = Entreprise::count();
            
            // Nombre d'entreprises créées ce mois-ci
            $nouvellesEntreprises = Entreprise::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            
            // Nombre d'entreprises créées le mois précédent
            $entreprisesMoisPrecedent = Entreprise::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])->count();
            
            // Calculer la variation en pourcentage
            $variationEntreprises = $entreprisesMoisPrecedent > 0 
                ? round((($nouvellesEntreprises - $entreprisesMoisPrecedent) / $entreprisesMoisPrecedent) * 100) 
                : ($nouvellesEntreprises > 0 ? 100 : 0);
                
            $descriptionVariationEntreprises = $variationEntreprises >= 0 
                ? "+{$variationEntreprises}% par rapport au mois précédent" 
                : "{$variationEntreprises}% par rapport au mois précédent";
                
            // Nombre d'entreprises actives (avec abonnement actif)
            $entreprisesActives = Entreprise::whereHas('abonnements', function ($query) {
                $query->where('statut', 'actif');
            })->count();
            
            $pourcentageEntreprisesActives = $totalEntreprises > 0 
                ? round(($entreprisesActives / $totalEntreprises) * 100) 
                : 0;

            // ===== STATISTIQUES DES UTILISATEURS =====
            
            // Nombre total d'utilisateurs
            $totalUtilisateurs = User::count();
            
            // Nombre d'utilisateurs créés ce mois-ci
            $nouveauxUtilisateurs = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            
            // Nombre d'utilisateurs créés le mois précédent
            $utilisateursMoisPrecedent = User::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])->count();
            
            // Calculer la variation en pourcentage
            $variationUtilisateurs = $utilisateursMoisPrecedent > 0 
                ? round((($nouveauxUtilisateurs - $utilisateursMoisPrecedent) / $utilisateursMoisPrecedent) * 100) 
                : ($nouveauxUtilisateurs > 0 ? 100 : 0);
                
            $descriptionVariationUtilisateurs = $variationUtilisateurs >= 0 
                ? "+{$variationUtilisateurs}% par rapport au mois précédent" 
                : "{$variationUtilisateurs}% par rapport au mois précédent";
                
            // Nombre total d'employés
            $totalEmployes = Employeur::count();
            
            // Nombre d'employés créés ce mois-ci
            $nouveauxEmployes = Employeur::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            // ===== STATISTIQUES DES ABONNEMENTS =====
            
            // Nombre d'abonnements actifs
            $abonnementsActifs = Abonnement::where('statut', 'actif')->count();
            
            // Nombre d'abonnements expirés
            $abonnementsExpires = Abonnement::where('statut', 'expire')->count();
            
            // Nombre d'abonnements créés ce mois-ci
            $nouveauxAbonnements = Abonnement::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            
            // Nombre d'abonnements créés le mois précédent
            $abonnementsMoisPrecedent = Abonnement::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])->count();
            
            // Calculer la variation en pourcentage
            $variationAbonnements = $abonnementsMoisPrecedent > 0 
                ? round((($nouveauxAbonnements - $abonnementsMoisPrecedent) / $abonnementsMoisPrecedent) * 100) 
                : ($nouveauxAbonnements > 0 ? 100 : 0);
                
            $descriptionVariationAbonnements = $variationAbonnements >= 0 
                ? "+{$variationAbonnements}% par rapport au mois précédent" 
                : "{$variationAbonnements}% par rapport au mois précédent";
                
            // Plans d'abonnement les plus populaires
            $plansPopulaires = Abonnement::select('plan_abonnement_id', DB::raw('COUNT(*) as total'))
                ->where('statut', 'actif')
                ->groupBy('plan_abonnement_id')
                ->orderBy('total', 'desc')
                ->limit(3)
                ->get();
                
            $planPopulaire = '';
            if ($plansPopulaires->isNotEmpty()) {
                $planId = $plansPopulaires->first()->plan_abonnement_id;
                $plan = PlanAbonnement::find($planId);
                $planPopulaire = $plan ? $plan->nom : 'N/A';
            } else {
                $planPopulaire = 'Aucun';
            }

            // ===== STATISTIQUES FINANCIÈRES =====
            
            // Chiffre d'affaires ce mois-ci
            $caThisMonth = Paiement::whereBetween('date_paiement', [$startOfMonth, $endOfMonth])
                ->where('statut', 'valide')
                ->sum('montant');
                
            // Chiffre d'affaires le mois précédent
            $caPreviousMonth = Paiement::whereBetween('date_paiement', [$startOfPreviousMonth, $endOfPreviousMonth])
                ->where('statut', 'valide')
                ->sum('montant');
                
            // Calculer la variation en pourcentage
            $variationCA = $caPreviousMonth > 0 
                ? round((($caThisMonth - $caPreviousMonth) / $caPreviousMonth) * 100) 
                : ($caThisMonth > 0 ? 100 : 0);
                
            $descriptionVariationCA = $variationCA >= 0 
                ? "+{$variationCA}% par rapport au mois précédent" 
                : "{$variationCA}% par rapport au mois précédent";
                
            // Chiffre d'affaires annuel
            $caAnnuel = Paiement::whereBetween('date_paiement', [$startOfYear, $endOfYear])
                ->where('statut', 'valide')
                ->sum('montant');
                
            // Factures impayées
            $facturesImpayees = Facturation::where('statut_paiement', 'en_attente')->count();
            
            // Montant total des factures impayées
            $montantFacturesImpayees = Facturation::where('statut_paiement', 'en_attente')
                ->sum('montant_ttc');
                
            // Taux de conversion (entreprises avec abonnement / total entreprises)
            $tauxConversion = $totalEntreprises > 0 
                ? round(($entreprisesActives / $totalEntreprises) * 100) 
                : 0;
                
            // Revenu moyen par entreprise
            $revenuMoyenParEntreprise = $entreprisesActives > 0 
                ? round($caThisMonth / $entreprisesActives) 
                : 0;

            // ===== STATISTIQUES DE CROISSANCE =====
            
            // Calculer la croissance des entreprises sur les 6 derniers mois
            $croissanceEntreprises = [];
            for ($i = 5; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i)->endOfMonth();
                
                $count = Entreprise::whereBetween('created_at', [$startDate, $endDate])->count();
                $croissanceEntreprises[] = $count;
            }
            
            // Calculer la croissance du CA sur les 6 derniers mois
            $croissanceCA = [];
            for ($i = 5; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = Carbon::now()->subMonths($i)->endOfMonth();
                
                $ca = Paiement::whereBetween('date_paiement', [$startDate, $endDate])
                    ->where('statut', 'valide')
                    ->sum('montant');
                    
                $croissanceCA[] = round($ca / 10000); // Mise à l'échelle pour la visualisation
            }

            return [
                // ===== SECTION: ENTREPRISES =====
                Stat::make('Entreprises', $totalEntreprises)
                    ->description("{$entreprisesActives} entreprises actives ({$pourcentageEntreprisesActives}%)")
                    ->descriptionIcon('heroicon-m-building-office')
                    ->chart($croissanceEntreprises)
                    ->color('primary'),
                
                Stat::make('Nouvelles entreprises', $nouvellesEntreprises)
                    ->description($descriptionVariationEntreprises)
                    ->descriptionIcon('heroicon-m-plus-circle')
                    ->chart([
                        $entreprisesMoisPrecedent,
                        $nouvellesEntreprises
                    ])
                    ->color($variationEntreprises >= 0 ? 'success' : 'danger'),
                
                Stat::make('Taux de conversion', $tauxConversion . '%')
                    ->description('Entreprises avec abonnement actif')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->chart([
                        $entreprisesActives,
                        $totalEntreprises - $entreprisesActives
                    ])
                    ->color($tauxConversion > 50 ? 'success' : ($tauxConversion > 30 ? 'warning' : 'danger')),
                
                // ===== SECTION: UTILISATEURS =====
                Stat::make('Utilisateurs', $totalUtilisateurs)
                    ->description($nouveauxUtilisateurs . ' nouveaux ce mois-ci')
                    ->descriptionIcon('heroicon-m-users')
                    ->chart([
                        $utilisateursMoisPrecedent,
                        $nouveauxUtilisateurs,
                        $totalUtilisateurs - $nouveauxUtilisateurs - $utilisateursMoisPrecedent
                    ])
                    ->color('info'),
                
                Stat::make('Employés', $totalEmployes)
                    ->description($nouveauxEmployes . ' nouveaux ce mois-ci')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->chart([
                        $nouveauxEmployes,
                        $totalEmployes - $nouveauxEmployes
                    ])
                    ->color('info'),
                
                // ===== SECTION: ABONNEMENTS =====
                Stat::make('Abonnements actifs', $abonnementsActifs)
                    ->description("Plan le plus populaire: {$planPopulaire}")
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->chart([
                        $abonnementsActifs,
                        $abonnementsExpires,
                        $nouveauxAbonnements
                    ])
                    ->color('success'),
                
                Stat::make('Nouveaux abonnements', $nouveauxAbonnements)
                    ->description($descriptionVariationAbonnements)
                    ->descriptionIcon('heroicon-m-plus')
                    ->chart([
                        $abonnementsMoisPrecedent,
                        $nouveauxAbonnements
                    ])
                    ->color($variationAbonnements >= 0 ? 'success' : 'danger'),
                
                // ===== SECTION: FINANCES =====
                Stat::make('CA mensuel', number_format($caThisMonth, 0, ',', ' ') . ' FCFA')
                    ->description($descriptionVariationCA)
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->chart($croissanceCA)
                    ->color($variationCA >= 0 ? 'success' : 'danger'),
                
                Stat::make('CA annuel', number_format($caAnnuel, 0, ',', ' ') . ' FCFA')
                    ->description('Projection annuelle')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->chart([
                        $caThisMonth,
                        $caPreviousMonth,
                        $caAnnuel / 12 // Moyenne mensuelle
                    ])
                    ->color('success'),
                
                Stat::make('Revenu moyen', number_format($revenuMoyenParEntreprise, 0, ',', ' ') . ' FCFA')
                    ->description('Par entreprise active')
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->chart([
                        $revenuMoyenParEntreprise,
                        $caThisMonth / ($totalEntreprises ?: 1)
                    ])
                    ->color('info'),
                
                Stat::make('Factures impayées', $facturesImpayees)
                    ->description('Montant: ' . number_format($montantFacturesImpayees, 0, ',', ' ') . ' FCFA')
                    ->descriptionIcon('heroicon-m-exclamation-circle')
                    ->chart([
                        $facturesImpayees,
                        $montantFacturesImpayees / 10000 // Mise à l'échelle pour la visualisation
                    ])
                    ->color($facturesImpayees > 0 ? 'danger' : 'success'),
            ];
        });
    }
}
