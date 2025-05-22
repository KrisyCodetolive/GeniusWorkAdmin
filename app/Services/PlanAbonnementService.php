<?php

namespace App\Services;

use App\Models\PlanAbonnement;
use App\Models\Entreprise;
use Illuminate\Support\Collection;

class PlanAbonnementService
{
    /**
     * Récupère tous les plans d'abonnement actifs
     *
     * @return Collection
     */
    public function getPlansActifs(): Collection
    {
        return PlanAbonnement::actif()->parPriorite()->get();
    }
    
    /**
     * Récupère les plans d'abonnement recommandés pour une entreprise
     *
     * @param Entreprise $entreprise L'entreprise concernée
     * @return Collection
     */
    public function getPlansRecommandes(Entreprise $entreprise): Collection
    {
        $nombreEmployes = $entreprise->getEmployeCount();
        
        return PlanAbonnement::actif()
            ->where(function($query) use ($nombreEmployes) {
                $query->where('nombre_employes_min', '<=', $nombreEmployes)
                      ->where('nombre_employes_max', '>=', $nombreEmployes);
            })
            ->parPriorite()
            ->get();
    }
    
    /**
     * Calcule le coût total d'un plan pour une entreprise
     *
     * @param PlanAbonnement $plan Le plan d'abonnement
     * @param Entreprise $entreprise L'entreprise concernée
     * @param string $typePeriode Type de période (mensuel ou annuel)
     * @return array Tableau contenant les détails du coût
     */
    public function calculerCout(PlanAbonnement $plan, Entreprise $entreprise, string $typePeriode = 'mensuel'): array
    {
        $nombreEmployes = $entreprise->getEmployeCount();
        
        // Prix de base selon la période
        $prixBase = $typePeriode === 'mensuel' ? $plan->prix_mensuel : $plan->prix_annuel;
        
        // Coût supplémentaire par employé si applicable
        $coutSupplementaire = 0;
        if ($plan->cout_par_employe > 0) {
            $coutSupplementaire = $nombreEmployes * $plan->cout_par_employe;
        }
        
        // Coût total
        $coutTotal = $prixBase + $coutSupplementaire;
        
        return [
            'plan_id' => $plan->id,
            'plan_nom' => $plan->nom,
            'type_periode' => $typePeriode,
            'prix_base' => $prixBase,
            'nombre_employes' => $nombreEmployes,
            'cout_par_employe' => $plan->cout_par_employe,
            'cout_supplementaire' => $coutSupplementaire,
            'cout_total' => $coutTotal,
            'devise' => $plan->devise,
            'fonctionnalites' => $plan->fonctionnalites
        ];
    }
    
    /**
     * Calcule le coût prorata d'un plan pour une durée spécifique
     *
     * @param PlanAbonnement $plan Le plan d'abonnement
     * @param Entreprise $entreprise L'entreprise concernée
     * @param string $typePeriode Type de période (mensuel ou annuel)
     * @param int $joursRestants Nombre de jours restants
     * @param int $joursTotal Nombre total de jours dans la période
     * @return array Tableau contenant les détails du coût
     */
    public function calculerCoutProrata(
        PlanAbonnement $plan, 
        Entreprise $entreprise, 
        string $typePeriode = 'mensuel', 
        int $joursRestants = 0, 
        int $joursTotal = 30
    ): array {
        // Calculer le coût complet
        $coutComplet = $this->calculerCout($plan, $entreprise, $typePeriode);
        
        // Calculer le ratio prorata
        $ratio = $joursRestants / $joursTotal;
        
        // Appliquer le ratio au coût total
        $coutProrata = $coutComplet['cout_total'] * $ratio;
        
        return array_merge($coutComplet, [
            'jours_restants' => $joursRestants,
            'jours_total' => $joursTotal,
            'ratio_prorata' => $ratio,
            'cout_prorata' => $coutProrata
        ]);
    }
    
    /**
     * Calcule la différence de coût entre deux plans
     *
     * @param PlanAbonnement $planActuel Le plan actuel
     * @param PlanAbonnement $nouveauPlan Le nouveau plan
     * @param Entreprise $entreprise L'entreprise concernée
     * @param string $typePeriode Type de période (mensuel ou annuel)
     * @return array Tableau contenant les détails de la différence
     */
    public function calculerDifferenceCout(
        PlanAbonnement $planActuel, 
        PlanAbonnement $nouveauPlan, 
        Entreprise $entreprise, 
        string $typePeriode = 'mensuel'
    ): array {
        // Calculer les coûts des deux plans
        $coutActuel = $this->calculerCout($planActuel, $entreprise, $typePeriode);
        $coutNouveau = $this->calculerCout($nouveauPlan, $entreprise, $typePeriode);
        
        // Calculer la différence
        $difference = $coutNouveau['cout_total'] - $coutActuel['cout_total'];
        
        return [
            'plan_actuel' => $coutActuel,
            'nouveau_plan' => $coutNouveau,
            'difference' => $difference,
            'est_upgrade' => $difference > 0,
            'devise' => $planActuel->devise
        ];
    }
    
    /**
     * Compare les fonctionnalités entre deux plans
     *
     * @param PlanAbonnement $planActuel Le plan actuel
     * @param PlanAbonnement $nouveauPlan Le nouveau plan
     * @return array Tableau contenant les détails de la comparaison
     */
    public function comparerFonctionnalites(PlanAbonnement $planActuel, PlanAbonnement $nouveauPlan): array
    {
        $fonctionnalitesActuelles = $planActuel->fonctionnalites;
        $nouvellesFonctionnalites = $nouveauPlan->fonctionnalites;
        
        // Fonctionnalités ajoutées (présentes dans le nouveau plan mais pas dans l'actuel)
        $fonctionnalitesAjoutees = array_diff($nouvellesFonctionnalites, $fonctionnalitesActuelles);
        
        // Fonctionnalités supprimées (présentes dans l'actuel mais pas dans le nouveau)
        $fonctionnalitesSupprimees = array_diff($fonctionnalitesActuelles, $nouvellesFonctionnalites);
        
        // Fonctionnalités communes
        $fonctionnalitesCommunes = array_intersect($fonctionnalitesActuelles, $nouvellesFonctionnalites);
        
        return [
            'plan_actuel' => [
                'id' => $planActuel->id,
                'nom' => $planActuel->nom,
                'fonctionnalites' => $fonctionnalitesActuelles
            ],
            'nouveau_plan' => [
                'id' => $nouveauPlan->id,
                'nom' => $nouveauPlan->nom,
                'fonctionnalites' => $nouvellesFonctionnalites
            ],
            'fonctionnalites_ajoutees' => $fonctionnalitesAjoutees,
            'fonctionnalites_supprimees' => $fonctionnalitesSupprimees,
            'fonctionnalites_communes' => $fonctionnalitesCommunes
        ];
    }
    
    /**
     * Vérifie si un plan est adapté pour une entreprise
     *
     * @param PlanAbonnement $plan Le plan d'abonnement
     * @param Entreprise $entreprise L'entreprise concernée
     * @return bool
     */
    public function estPlanAdapte(PlanAbonnement $plan, Entreprise $entreprise): bool
    {
        $nombreEmployes = $entreprise->getEmployeCount();
        
        return $plan->isAdapteForEmployeCount($nombreEmployes);
    }
}
