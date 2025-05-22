<?php

namespace App\Services;

use App\Models\Entreprise;
use App\Models\Facturation;
use App\Models\FraisUsage;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RapportFinancierService
{
    /**
     * Génère un rapport financier pour une entreprise sur une période donnée.
     *
     * @param Entreprise $entreprise
     * @param array $options
     * @return array
     */
    public function genererRapportFinancier(Entreprise $entreprise, array $options = []): array
    {
        // Paramètres par défaut
        $dateDebut = $options['date_debut'] ?? Carbon::now()->startOfMonth();
        $dateFin = $options['date_fin'] ?? Carbon::now();
        
        // Récupérer les factures de la période
        $factures = Facturation::where('entreprise_id', $entreprise->id)
            ->whereBetween('date_facturation', [$dateDebut, $dateFin])
            ->get();
        
        // Récupérer les frais d'usage de la période
        $fraisUsages = FraisUsage::where('entreprise_id', $entreprise->id)
            ->whereBetween('date_debut', [$dateDebut, $dateFin])
            ->get();
        
        // Calculer les statistiques
        $totalFacture = $factures->sum('montant_total');
        $totalPaye = $factures->where('statut', 'payee')->sum('montant_total');
        $totalImpaye = $factures->whereIn('statut', ['non_payee', 'en_retard'])->sum('montant_total');
        
        $totalFraisUsage = $fraisUsages->sum('montant');
        $totalFraisFactures = $fraisUsages->where('facture_id', '!=', null)->sum('montant');
        $totalFraisNonFactures = $fraisUsages->whereNull('facture_id')->sum('montant');
        
        // Répartition par type de frais
        $repartitionParType = $fraisUsages
            ->groupBy('type_frais')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('montant'),
                    'moyenne' => $items->count() > 0 ? $items->sum('montant') / $items->count() : 0
                ];
            });
        
        // Évolution mensuelle
        $evolutionMensuelle = $this->calculerEvolutionMensuelle($entreprise, $dateDebut, $dateFin);
        
        // Taux de recouvrement
        $tauxRecouvrement = $totalFacture > 0 ? ($totalPaye / $totalFacture) * 100 : 0;
        
        // Retard moyen de paiement (en jours)
        $retardMoyen = $this->calculerRetardMoyenPaiement($factures);
        
        return [
            'periode' => [
                'debut' => $dateDebut->format('Y-m-d'),
                'fin' => $dateFin->format('Y-m-d'),
            ],
            'factures' => [
                'total' => $totalFacture,
                'paye' => $totalPaye,
                'impaye' => $totalImpaye,
                'count' => $factures->count(),
                'taux_recouvrement' => $tauxRecouvrement,
                'retard_moyen' => $retardMoyen,
            ],
            'frais_usage' => [
                'total' => $totalFraisUsage,
                'facture' => $totalFraisFactures,
                'non_facture' => $totalFraisNonFactures,
                'count' => $fraisUsages->count(),
                'repartition_par_type' => $repartitionParType,
            ],
            'evolution_mensuelle' => $evolutionMensuelle,
        ];
    }
    
    /**
     * Génère un rapport financier global pour toutes les entreprises.
     *
     * @param array $options
     * @return array
     */
    public function genererRapportGlobal(array $options = []): array
    {
        // Paramètres par défaut
        $dateDebut = $options['date_debut'] ?? Carbon::now()->startOfYear();
        $dateFin = $options['date_fin'] ?? Carbon::now();
        
        // Récupérer toutes les entreprises actives
        $entreprises = Entreprise::where('actif', true)->get();
        
        // Initialiser les totaux
        $totalFacture = 0;
        $totalPaye = 0;
        $totalImpaye = 0;
        $totalFraisUsage = 0;
        $countFactures = 0;
        $countFraisUsage = 0;
        
        // Rapports par entreprise
        $rapportsParEntreprise = [];
        
        foreach ($entreprises as $entreprise) {
            $rapport = $this->genererRapportFinancier($entreprise, [
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]);
            
            $rapportsParEntreprise[$entreprise->id] = [
                'entreprise' => $entreprise->nom,
                'rapport' => $rapport,
            ];
            
            // Agréger les totaux
            $totalFacture += $rapport['factures']['total'];
            $totalPaye += $rapport['factures']['paye'];
            $totalImpaye += $rapport['factures']['impaye'];
            $totalFraisUsage += $rapport['frais_usage']['total'];
            $countFactures += $rapport['factures']['count'];
            $countFraisUsage += $rapport['frais_usage']['count'];
        }
        
        // Calculer les statistiques globales
        $tauxRecouvrementGlobal = $totalFacture > 0 ? ($totalPaye / $totalFacture) * 100 : 0;
        
        // Top 5 des entreprises par montant facturé
        $topEntreprises = collect($rapportsParEntreprise)
            ->sortByDesc(function ($item) {
                return $item['rapport']['factures']['total'];
            })
            ->take(5)
            ->map(function ($item) {
                return [
                    'entreprise' => $item['entreprise'],
                    'montant_total' => $item['rapport']['factures']['total'],
                    'taux_recouvrement' => $item['rapport']['factures']['taux_recouvrement'],
                ];
            })
            ->values()
            ->toArray();
        
        // Évolution mensuelle globale
        $evolutionMensuelleGlobale = $this->calculerEvolutionMensuelleGlobale($dateDebut, $dateFin);
        
        return [
            'periode' => [
                'debut' => $dateDebut->format('Y-m-d'),
                'fin' => $dateFin->format('Y-m-d'),
            ],
            'global' => [
                'total_facture' => $totalFacture,
                'total_paye' => $totalPaye,
                'total_impaye' => $totalImpaye,
                'total_frais_usage' => $totalFraisUsage,
                'count_factures' => $countFactures,
                'count_frais_usage' => $countFraisUsage,
                'taux_recouvrement' => $tauxRecouvrementGlobal,
            ],
            'top_entreprises' => $topEntreprises,
            'evolution_mensuelle' => $evolutionMensuelleGlobale,
            'rapports_par_entreprise' => $rapportsParEntreprise,
        ];
    }
    
    /**
     * Calcule l'évolution mensuelle des factures et frais d'usage pour une entreprise.
     *
     * @param Entreprise $entreprise
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @return array
     */
    protected function calculerEvolutionMensuelle(Entreprise $entreprise, Carbon $dateDebut, Carbon $dateFin): array
    {
        $evolution = [];
        
        // Cloner les dates pour ne pas modifier les originales
        $currentMonth = clone $dateDebut;
        $endMonth = clone $dateFin;
        
        // Ajuster au début du mois
        $currentMonth->startOfMonth();
        $endMonth->endOfMonth();
        
        while ($currentMonth->lte($endMonth)) {
            $monthStart = clone $currentMonth;
            $monthEnd = clone $currentMonth->copy()->endOfMonth();
            
            // Récupérer les factures du mois
            $facturesMois = Facturation::where('entreprise_id', $entreprise->id)
                ->whereBetween('date_facturation', [$monthStart, $monthEnd])
                ->get();
            
            // Récupérer les frais d'usage du mois
            $fraisUsagesMois = FraisUsage::where('entreprise_id', $entreprise->id)
                ->whereBetween('date_debut', [$monthStart, $monthEnd])
                ->get();
            
            $evolution[$currentMonth->format('Y-m')] = [
                'factures' => [
                    'total' => $facturesMois->sum('montant_total'),
                    'count' => $facturesMois->count(),
                ],
                'frais_usage' => [
                    'total' => $fraisUsagesMois->sum('montant'),
                    'count' => $fraisUsagesMois->count(),
                ],
            ];
            
            // Passer au mois suivant
            $currentMonth->addMonth();
        }
        
        return $evolution;
    }
    
    /**
     * Calcule l'évolution mensuelle globale des factures et frais d'usage.
     *
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @return array
     */
    protected function calculerEvolutionMensuelleGlobale(Carbon $dateDebut, Carbon $dateFin): array
    {
        $evolution = [];
        
        // Cloner les dates pour ne pas modifier les originales
        $currentMonth = clone $dateDebut;
        $endMonth = clone $dateFin;
        
        // Ajuster au début du mois
        $currentMonth->startOfMonth();
        $endMonth->endOfMonth();
        
        while ($currentMonth->lte($endMonth)) {
            $monthStart = clone $currentMonth;
            $monthEnd = clone $currentMonth->copy()->endOfMonth();
            
            // Récupérer les factures du mois
            $facturesMois = Facturation::whereBetween('date_facturation', [$monthStart, $monthEnd])->get();
            
            // Récupérer les frais d'usage du mois
            $fraisUsagesMois = FraisUsage::whereBetween('date_debut', [$monthStart, $monthEnd])->get();
            
            $evolution[$currentMonth->format('Y-m')] = [
                'factures' => [
                    'total' => $facturesMois->sum('montant_total'),
                    'count' => $facturesMois->count(),
                ],
                'frais_usage' => [
                    'total' => $fraisUsagesMois->sum('montant'),
                    'count' => $fraisUsagesMois->count(),
                ],
            ];
            
            // Passer au mois suivant
            $currentMonth->addMonth();
        }
        
        return $evolution;
    }
    
    /**
     * Calcule le retard moyen de paiement en jours.
     *
     * @param Collection $factures
     * @return float
     */
    protected function calculerRetardMoyenPaiement(Collection $factures): float
    {
        $facturesPayees = $factures->where('statut', 'payee')
            ->where('date_paiement', '!=', null);
        
        if ($facturesPayees->isEmpty()) {
            return 0;
        }
        
        $totalJoursRetard = 0;
        $count = 0;
        
        foreach ($facturesPayees as $facture) {
            $dateEcheance = Carbon::parse($facture->date_echeance);
            $datePaiement = Carbon::parse($facture->date_paiement);
            
            if ($datePaiement->gt($dateEcheance)) {
                $totalJoursRetard += $datePaiement->diffInDays($dateEcheance);
                $count++;
            }
        }
        
        return $count > 0 ? $totalJoursRetard / $count : 0;
    }
    
    /**
     * Génère un rapport de prévisions financières.
     *
     * @param array $options
     * @return array
     */
    public function genererPrevisionsFinancieres(array $options = []): array
    {
        // Paramètres par défaut
        $nombreMois = $options['nombre_mois'] ?? 3;
        
        // Date actuelle
        $now = Carbon::now();
        
        // Récupérer les données historiques des 6 derniers mois
        $dateDebutHistorique = Carbon::now()->subMonths(6)->startOfMonth();
        $dateFinHistorique = Carbon::now()->endOfMonth();
        
        // Récupérer les factures historiques
        $facturesHistoriques = Facturation::whereBetween('date_facturation', [$dateDebutHistorique, $dateFinHistorique])
            ->get()
            ->groupBy(function ($facture) {
                return Carbon::parse($facture->date_facturation)->format('Y-m');
            });
        
        // Récupérer les frais d'usage historiques
        $fraisUsagesHistoriques = FraisUsage::whereBetween('date_debut', [$dateDebutHistorique, $dateFinHistorique])
            ->get()
            ->groupBy(function ($frais) {
                return Carbon::parse($frais->date_debut)->format('Y-m');
            });
        
        // Calculer les moyennes mensuelles
        $moyenneFactures = $facturesHistoriques->map->sum('montant_total')->avg();
        $moyenneFraisUsage = $fraisUsagesHistoriques->map->sum('montant')->avg();
        
        // Calculer les taux de croissance moyens
        $tauxCroissanceFactures = $this->calculerTauxCroissanceMoyen($facturesHistoriques);
        $tauxCroissanceFraisUsage = $this->calculerTauxCroissanceMoyen($fraisUsagesHistoriques);
        
        // Générer les prévisions
        $previsions = [];
        $moisCourant = Carbon::now()->startOfMonth();
        
        for ($i = 0; $i < $nombreMois; $i++) {
            $moisCourant = $moisCourant->copy()->addMonth();
            $moisKey = $moisCourant->format('Y-m');
            
            // Appliquer le taux de croissance pour chaque mois
            $previsionFactures = $moyenneFactures * pow(1 + $tauxCroissanceFactures, $i + 1);
            $previsionFraisUsage = $moyenneFraisUsage * pow(1 + $tauxCroissanceFraisUsage, $i + 1);
            
            $previsions[$moisKey] = [
                'factures' => [
                    'total' => round($previsionFactures, 2),
                ],
                'frais_usage' => [
                    'total' => round($previsionFraisUsage, 2),
                ],
                'total' => round($previsionFactures + $previsionFraisUsage, 2),
            ];
        }
        
        return [
            'historique' => [
                'debut' => $dateDebutHistorique->format('Y-m-d'),
                'fin' => $dateFinHistorique->format('Y-m-d'),
                'moyenne_factures' => $moyenneFactures,
                'moyenne_frais_usage' => $moyenneFraisUsage,
                'taux_croissance_factures' => $tauxCroissanceFactures,
                'taux_croissance_frais_usage' => $tauxCroissanceFraisUsage,
            ],
            'previsions' => $previsions,
        ];
    }
    
    /**
     * Calcule le taux de croissance moyen à partir des données historiques.
     *
     * @param Collection $donneesMensuelles
     * @return float
     */
    protected function calculerTauxCroissanceMoyen(Collection $donneesMensuelles): float
    {
        if ($donneesMensuelles->count() <= 1) {
            return 0;
        }
        
        $mois = $donneesMensuelles->keys()->sort();
        $tauxCroissance = [];
        
        for ($i = 1; $i < $mois->count(); $i++) {
            $moisPrecedent = $mois[$i - 1];
            $moisCourant = $mois[$i];
            
            $totalPrecedent = $donneesMensuelles[$moisPrecedent]->sum('montant_total') ?: 
                              $donneesMensuelles[$moisPrecedent]->sum('montant');
            
            $totalCourant = $donneesMensuelles[$moisCourant]->sum('montant_total') ?: 
                            $donneesMensuelles[$moisCourant]->sum('montant');
            
            if ($totalPrecedent > 0) {
                $tauxCroissance[] = ($totalCourant - $totalPrecedent) / $totalPrecedent;
            }
        }
        
        return empty($tauxCroissance) ? 0 : array_sum($tauxCroissance) / count($tauxCroissance);
    }
}
