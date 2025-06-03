<?php

namespace App\Filament\Widgets;

use App\Models\Entreprise;
use App\Models\Employeur;
use App\Models\Departement;
use App\Models\Site;
use App\Models\Presence;
use App\Models\Conge;
use App\Models\Filiale;
use App\Models\Supplementaire;
use App\Models\RetardAbsence;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use App\Models\AppareilBiometrique;
use App\Models\MethodePointage;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EntrepriseStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '60s';
    protected static bool $isLazy = true;
    protected static ?int $sort = 1;

    // Cette fonction permet de déterminer si le widget doit être affiché
    public static function canView(): bool
    {
        $user = Auth::user();
        
        // Afficher uniquement si l'utilisateur a une entreprise associée
        // ou si l'utilisateur est SuperAdmin/Support
        return $user && ($user->entreprise_id || $user->isSuperAdmin() || $user->isSupport());
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Si l'utilisateur n'a pas d'entreprise et n'est pas SuperAdmin/Support, retourner un tableau vide
        if (!$user || (!$user->entreprise && !$user->isSuperAdmin() && !$user->isSupport())) {
            return [];
        }
        
        // Pour les SuperAdmin/Support sans entreprise sélectionnée, utiliser la première entreprise
        if (!$user->entreprise && ($user->isSuperAdmin() || $user->isSupport())) {
            $entreprise = Entreprise::first();
            if (!$entreprise) {
                return [];
            }
        } else {
            $entreprise = $user->entreprise;
        }

        // Utiliser le cache pour éviter de recalculer les statistiques trop souvent
        $cacheKey = "entreprise_stats_{$entreprise->id}_" . date('Y-m-d_H');
        $cacheDuration = 60; // minutes

        return Cache::remember($cacheKey, $cacheDuration, function () use ($entreprise, $user) {
            // Dates importantes pour les calculs
            $today = Carbon::today();
            $yesterday = Carbon::yesterday();
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();

            // ===== STATISTIQUES DES EMPLOYÉS =====
            
            // Calculer le nombre d'employés actifs
            $totalEmployes = Employeur::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->count();
                
            // Calculer le nombre d'employés par genre
            $employesHommes = Employeur::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->where('genre', 'M')
                ->count();
                
            $employesFemmes = Employeur::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->where('genre', 'F')
                ->count();
                
            $ratioGenre = $totalEmployes > 0 
                ? round(($employesHommes / $totalEmployes) * 100) . '% H / ' . round(($employesFemmes / $totalEmployes) * 100) . '% F'
                : 'N/A';
                
            // Calculer le nombre d'employés par type de contrat
            $employesCDI = Employeur::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->where('type_contrat', 'cdi')
                ->count();
                
            $employesCDD = Employeur::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->where('type_contrat', 'cdd')
                ->count();
                
            $employesStage = Employeur::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->where('type_contrat', 'stage')
                ->count();
                
            // Calculer le nombre d'employés recrutés ce mois-ci
            $nouveauxEmployes = Employeur::where('entreprise_id', $entreprise->id)
                ->whereBetween('date_embauche', [$startOfMonth, $endOfMonth])
                ->count();
                
            // Calculer le nombre d'employés recrutés le mois précédent
            $nouveauxEmployesMoisPrecedent = Employeur::where('entreprise_id', $entreprise->id)
                ->whereBetween('date_embauche', [$startOfPreviousMonth, $endOfPreviousMonth])
                ->count();
                
            // Calculer la variation en pourcentage
            $variationEmployes = $nouveauxEmployesMoisPrecedent > 0 
                ? round((($nouveauxEmployes - $nouveauxEmployesMoisPrecedent) / $nouveauxEmployesMoisPrecedent) * 100) 
                : ($nouveauxEmployes > 0 ? 100 : 0);
                
            $descriptionVariationEmployes = $variationEmployes > 0 
                ? "+{$variationEmployes}% par rapport au mois précédent" 
                : "{$variationEmployes}% par rapport au mois précédent";

            // ===== STATISTIQUES DES PRÉSENCES =====
            
            // Calculer le nombre d'employés actifs aujourd'hui (avec des présences)
            $employesActiveToday = DB::table('presences')
                ->join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->whereDate('presences.created_at', Carbon::today())
                ->whereIn('presences.type', ['entree', 'sortie'])
                ->whereNull('presences.deleted_at')
                ->whereNull('employeurs.deleted_at')
                ->select('presences.employeur_id')
                ->distinct()
                ->count();
            
            // Calculer le pourcentage d'employés actifs
            $activePercentage = $totalEmployes > 0 ? round(($employesActiveToday / $totalEmployes) * 100) : 0;
            
            // Calculer le nombre d'employés actifs hier
            $employesActiveYesterday = DB::table('presences')
                ->join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->whereDate('presences.created_at', $yesterday)
                ->whereIn('presences.type', ['entree', 'sortie'])
                ->whereNull('presences.deleted_at')
                ->whereNull('employeurs.deleted_at')
                ->select('presences.employeur_id')
                ->distinct()
                ->count();
                
            // Calculer la variation en pourcentage
            $variationPresences = $employesActiveYesterday > 0 
                ? round((($employesActiveToday - $employesActiveYesterday) / $employesActiveYesterday) * 100) 
                : ($employesActiveToday > 0 ? 100 : 0);
                
            $descriptionVariationPresences = $variationPresences >= 0 
                ? "+{$variationPresences}% par rapport à hier" 
                : "{$variationPresences}% par rapport à hier";

            // ===== STATISTIQUES DES RETARDS ET ABSENCES =====
            
            // Calculer le nombre de retards aujourd'hui
            $retardsToday = Presence::join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('presences.statut', 'retard')
                ->whereDate('presences.date_heure_entree', $today)
                ->count();
                
            // Calculer le nombre d'absences aujourd'hui
            $absencesToday = Presence::join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('presences.statut', 'absent')
                ->whereDate('presences.date_heure_entree', $today)
                ->count();
                
            // Calculer le nombre total d'incidents aujourd'hui
            $totalIncidentsToday = $retardsToday + $absencesToday;
            
            // Calculer le nombre de retards cette semaine
            $retardsWeek = Presence::join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('presences.statut', 'retard')
                ->whereBetween('presences.date_heure_entree', [$startOfWeek, $endOfWeek])
                ->count();
                
            // Calculer le nombre d'absences cette semaine
            $absencesWeek = Presence::join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('presences.statut', 'absent')
                ->whereBetween('presences.date_heure_entree', [$startOfWeek, $endOfWeek])
                ->count();
                
            // Calculer le nombre total d'incidents cette semaine
            $totalIncidentsWeek = $retardsWeek + $absencesWeek;
            
            // Calculer le taux d'assiduité (pourcentage d'employés sans retards ni absences)
            $employesAvecIncidents = DB::table('presences')
                ->join('employeurs', 'presences.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->whereIn('presences.statut', ['retard', 'absent'])
                ->whereBetween('presences.date_heure_entree', [$startOfMonth, $endOfMonth])
                ->select('presences.employeur_id')
                ->distinct()
                ->count();
                
            $tauxAssiduite = $totalEmployes > 0 
                ? round((($totalEmployes - $employesAvecIncidents) / $totalEmployes) * 100) 
                : 100;

            // ===== STATISTIQUES DES HEURES SUPPLÉMENTAIRES =====
            
            // Calculer le nombre d'heures supplémentaires ce mois-ci
            $supplementairesMonth = Supplementaire::join('employeurs', 'supplementaires.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->whereBetween('supplementaires.date', [$startOfMonth, $endOfMonth])
                ->sum('nombre_heures');
                
            $heuresSupplementaires = round($supplementairesMonth, 1);
            
            // Calculer le nombre d'employés ayant fait des heures supplémentaires
            $employesAvecSupplementaires = Supplementaire::join('employeurs', 'supplementaires.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->whereBetween('supplementaires.date', [$startOfMonth, $endOfMonth])
                ->select('supplementaires.employeur_id')
                ->distinct()
                ->count();
                
            // Calculer le pourcentage d'employés avec des heures supplémentaires
            $pourcentageSupplementaires = $totalEmployes > 0 
                ? round(($employesAvecSupplementaires / $totalEmployes) * 100) 
                : 0;

            // ===== STATISTIQUES DES CONGÉS =====
            
            // Obtenir le nombre de congés en attente
            $congesEnAttente = DB::table('conges')
                ->join('employeurs', 'conges.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('conges.statut', 'en_attente')
                ->whereNull('conges.deleted_at')
                ->count();
                
            // Obtenir le nombre de congés approuvés ce mois-ci
            $congesApprouves = DB::table('conges')
                ->join('employeurs', 'conges.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('conges.statut', 'approuve')
                ->whereBetween('conges.date_debut', [$startOfMonth, $endOfMonth])
                ->whereNull('conges.deleted_at')
                ->count();
                
            // Obtenir le nombre de congés en cours
            $congesEnCours = DB::table('conges')
                ->join('employeurs', 'conges.employeur_id', '=', 'employeurs.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->where('conges.statut', 'approuve')
                ->where('conges.date_debut', '<=', $today)
                ->where('conges.date_fin', '>=', $today)
                ->whereNull('conges.deleted_at')
                ->count();
                
            // Obtenir le type de congé le plus demandé
            $typeCongePopulaire = DB::table('conges')
                ->join('employeurs', 'conges.employeur_id', '=', 'employeurs.id')
                ->join('type_conges', 'conges.type_conge_id', '=', 'type_conges.id')
                ->where('employeurs.entreprise_id', $entreprise->id)
                ->whereBetween('conges.date_debut', [$startOfMonth, $endOfMonth])
                ->select('type_conges.nom', DB::raw('COUNT(*) as total'))
                ->groupBy('type_conges.nom')
                ->orderBy('total', 'desc')
                ->first();
                
            $typeCongePopulaireNom = $typeCongePopulaire ? $typeCongePopulaire->nom : 'Aucun';

            // ===== STATISTIQUES DE L'ENTREPRISE =====
            
            // Obtenir le nombre de départements
            $departements = $entreprise->departements()->count();
            
            // Obtenir le nombre de sites
            $sites = $entreprise->sites()->count();
            
            // Obtenir le nombre de filiales
            $filiales = Filiale::where('entreprise_id', $entreprise->id)
                ->where('statut', Filiale::STATUT_ACTIF)
                ->count();
                
            // Obtenir le nombre d'employés dans les filiales
            $employesFiliales = DB::table('employeurs')
                ->join('departements', 'employeurs.departement_id', '=', 'departements.id')
                ->join('filiales', 'departements.filiale_id', '=', 'filiales.id')
                ->where('filiales.entreprise_id', $entreprise->id)
                ->where('employeurs.statut', 'actif')
                ->where('filiales.statut', Filiale::STATUT_ACTIF)
                ->whereNull('employeurs.deleted_at')
                ->whereNull('departements.deleted_at')
                ->whereNull('filiales.deleted_at')
                ->distinct()
                ->count('employeurs.id');
                
            // Obtenir le nombre d'appareils biométriques
            $appareilsBiometriques = AppareilBiometrique::where('entreprise_id', $entreprise->id)
                ->count();
                
            // Obtenir les méthodes de pointage utilisées
            $methodesPointage = MethodePointage::where('entreprise_id', $entreprise->id)
                ->where('statut', 'actif')
                ->count();

            // ===== STATISTIQUES DE L'ABONNEMENT =====
            
            // Obtenir l'abonnement actif
            $abonnementActif = $entreprise->abonnements()
                ->where('statut', 'actif')
                ->orderBy('date_fin', 'desc')
                ->first();
            
            // Calculer les jours restants pour l'abonnement
            $joursRestants = $abonnementActif ? (int)now()->diffInDays($abonnementActif->date_fin, false) : 0;
            
            // Obtenir le nombre de factures impayées
            $facturesImpayees = $entreprise->facturations()
                ->where('statut_paiement', 'en_attente')
                ->count();
                
            // Obtenir le montant total des factures impayées
            $montantFacturesImpayees = $entreprise->facturations()
                ->where('statut_paiement', 'en_attente')
                ->sum('montant_ttc');

            return [
                // ===== SECTION: EMPLOYÉS =====
                Stat::make('Employés', $totalEmployes)
                    ->description("Répartition: {$ratioGenre}")
                    ->descriptionIcon('heroicon-m-users')
                    ->chart([
                        $employesHommes, 
                        $employesFemmes
                    ])
                    ->color('primary'),
                
                Stat::make('Nouveaux employés', $nouveauxEmployes)
                    ->description($descriptionVariationEmployes)
                    ->descriptionIcon('heroicon-m-user-plus')
                    ->chart([
                        $nouveauxEmployesMoisPrecedent,
                        $nouveauxEmployes
                    ])
                    ->color($variationEmployes >= 0 ? 'success' : 'danger'),
                
                Stat::make('Types de contrats', "CDI: {$employesCDI} | CDD: {$employesCDD} | Stage: {$employesStage}")
                    ->description('Répartition des contrats')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->chart([
                        $employesCDI,
                        $employesCDD,
                        $employesStage
                    ])
                    ->color('info'),
                
                // ===== SECTION: PRÉSENCES ET ABSENCES =====
                Stat::make('Présences aujourd\'hui', $employesActiveToday)
                    ->description($activePercentage . '% des employés présents')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->chart([
                        $employesActiveYesterday,
                        $employesActiveToday
                    ])
                    ->color($activePercentage > 50 ? 'success' : 'warning'),
                
                Stat::make('Taux d\'assiduité', $tauxAssiduite . '%')
                    ->description('Employés sans retards ni absences')
                    ->descriptionIcon('heroicon-m-clock')
                    ->chart([
                        $totalEmployes - $employesAvecIncidents,
                        $employesAvecIncidents
                    ])
                    ->color($tauxAssiduite > 90 ? 'success' : ($tauxAssiduite > 75 ? 'warning' : 'danger')),
                
                Stat::make('Incidents aujourd\'hui', $totalIncidentsToday)
                    ->description("Retards: {$retardsToday} | Absences: {$absencesToday}")
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->chart([
                        $retardsToday,
                        $absencesToday,
                        $retardsWeek,
                        $absencesWeek
                    ])
                    ->color($totalIncidentsToday > 0 ? 'danger' : 'success'),
                
                // ===== SECTION: CONGÉS =====
                Stat::make('Congés en attente', $congesEnAttente)
                    ->description($congesEnAttente > 0 ? 'Demandes à traiter' : 'Aucune demande en attente')
                    ->descriptionIcon($congesEnAttente > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                    ->chart([
                        $congesEnAttente,
                        $congesApprouves,
                        $congesEnCours
                    ])
                    ->color($congesEnAttente > 0 ? 'warning' : 'success'),
                
                Stat::make('Congés en cours', $congesEnCours)
                    ->description("Type le plus demandé: {$typeCongePopulaireNom}")
                    ->descriptionIcon('heroicon-m-calendar')
                    ->chart([
                        $congesEnCours,
                        $congesApprouves - $congesEnCours
                    ])
                    ->color('info'),
                
                Stat::make('Heures supplémentaires', $heuresSupplementaires . 'h')
                    ->description("{$pourcentageSupplementaires}% des employés concernés")
                    ->descriptionIcon('heroicon-m-clock')
                    ->chart([
                        $heuresSupplementaires / ($totalEmployes ?: 1),
                        $employesAvecSupplementaires,
                        $totalEmployes - $employesAvecSupplementaires
                    ])
                    ->color('warning'),
                
                // ===== SECTION: STRUCTURE =====
                Stat::make('Départements', $departements)
                    ->description('Départements de l\'entreprise')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->chart([
                        $departements,
                        $sites,
                        $filiales
                    ])
                    ->color('success'),
                    
                Stat::make('Sites', $sites)
                    ->description("{$appareilsBiometriques} appareil(s) biométrique(s)")
                    ->descriptionIcon('heroicon-m-map-pin')
                    ->chart([
                        $sites,
                        $appareilsBiometriques,
                        $methodesPointage
                    ])
                    ->color('info'),
                    
                Stat::make('Filiales', $filiales)
                    ->description($employesFiliales . ' employés dans les filiales')
                    ->descriptionIcon('heroicon-m-building-storefront')
                    ->chart([
                        $filiales,
                        $employesFiliales,
                        $totalEmployes - $employesFiliales
                    ])
                    ->color('success'),
                
            ];
        });
    }
}
