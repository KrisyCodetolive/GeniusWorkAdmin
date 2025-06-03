<?php

namespace App\Services;

use App\Models\PlanAbonnement;
use Illuminate\Support\Collection;

class TarificationService
{
    /**
     * Coût par employé (fixe)
     */
    const COUT_PAR_EMPLOYE = 100;
    
    /**
     * Structure des plans d'abonnement
     */
    const PLANS = [
        // Starter
        ['min' => 0, 'max' => 14, 'nom' => 'Starter Level 1', 'cout_fixe' => 15000],
        ['min' => 15, 'max' => 24, 'nom' => 'Starter Level 2', 'cout_fixe' => 25000],
        
        // Business
        ['min' => 25, 'max' => 49, 'nom' => 'Business Level 1', 'cout_fixe' => 35000],
        ['min' => 50, 'max' => 74, 'nom' => 'Business Level 2', 'cout_fixe' => 50000],
        ['min' => 75, 'max' => 99, 'nom' => 'Business Level 3', 'cout_fixe' => 75000],
        
        // Enterprise
        ['min' => 100, 'max' => 199, 'nom' => 'Enterprise Level 1', 'cout_fixe' => 100000],
        ['min' => 200, 'max' => 299, 'nom' => 'Enterprise Level 2', 'cout_fixe' => 200000],
        ['min' => 300, 'max' => 399, 'nom' => 'Enterprise Level 3', 'cout_fixe' => 300000],
        ['min' => 400, 'max' => 499, 'nom' => 'Enterprise Level 4', 'cout_fixe' => 400000],
        ['min' => 500, 'max' => 599, 'nom' => 'Enterprise Level 5', 'cout_fixe' => 500000],
        ['min' => 600, 'max' => 699, 'nom' => 'Enterprise Level 6', 'cout_fixe' => 600000],
        ['min' => 700, 'max' => 799, 'nom' => 'Enterprise Level 7', 'cout_fixe' => 700000],
        ['min' => 800, 'max' => 899, 'nom' => 'Enterprise Level 8', 'cout_fixe' => 800000],
        ['min' => 900, 'max' => 999, 'nom' => 'Enterprise Level 9', 'cout_fixe' => 900000],
        ['min' => 1000, 'max' => 1099, 'nom' => 'Enterprise Level 10', 'cout_fixe' => 1000000],
    ];
    
    /**
     * Détermine le plan d'abonnement approprié en fonction du nombre d'employés
     *
     * @param int $nombreEmployes Nombre d'employés de l'entreprise
     * @return array Détails du plan d'abonnement
     */
    public function determinerPlan(int $nombreEmployes): array
    {
        foreach (self::PLANS as $plan) {
            if ($nombreEmployes >= $plan['min'] && $nombreEmployes <= $plan['max']) {
                return $plan;
            }
        }
        
        // Si le nombre d'employés dépasse les plans prédéfinis, calculer dynamiquement
        $niveau = floor($nombreEmployes / 100) + 1;
        $min = ($niveau - 1) * 100;
        $max = $min + 99;
        $coutFixe = $niveau * 100000;
        
        return [
            'min' => $min,
            'max' => $max,
            'nom' => 'Enterprise Level ' . $niveau,
            'cout_fixe' => $coutFixe
        ];
    }
    
    /**
     * Calcule le coût total d'un abonnement
     *
     * @param int $nombreEmployes Nombre d'employés
     * @return array Détails du coût
     */
    public function calculerCout(int $nombreEmployes): array
    {
        $plan = $this->determinerPlan($nombreEmployes);
        $coutFixe = $plan['cout_fixe'];
        $coutEmployes = $nombreEmployes * self::COUT_PAR_EMPLOYE;
        $coutTotal = $coutFixe + $coutEmployes;
        
        return [
            'plan' => $plan['nom'],
            'cout_fixe' => $coutFixe,
            'cout_par_employe' => self::COUT_PAR_EMPLOYE,
            'nombre_employes' => $nombreEmployes,
            'cout_employes' => $coutEmployes,
            'cout_total' => $coutTotal,
            'cout_mensuel_par_employe' => round($coutTotal / $nombreEmployes, 2),
            'cout_annuel' => $coutTotal * 12
        ];
    }
    
    /**
     * Crée ou met à jour les plans d'abonnement dans la base de données
     *
     * @return Collection Collection des plans d'abonnement
     */
    public function synchroniserPlansAbonnement(): Collection
    {
        $plans = collect();
        
        foreach (self::PLANS as $planData) {
            $plan = PlanAbonnement::updateOrCreate(
                ['nom' => $planData['nom']],
                [
                    'description' => 'Plan ' . $planData['nom'],
                    'prix_mensuel' => $planData['cout_fixe'],
                    'prix_annuel' => $planData['cout_fixe'] * 10, // 10 mois au lieu de 12 pour incitation
                    'nombre_employes_min' => $planData['min'],
                    'nombre_employes_max' => $planData['max'],
                    'cout_par_employe' => self::COUT_PAR_EMPLOYE,
                    'statut' => 'actif',
                    'devise' => 'FCFA',
                    'periode_facturation' => 'mensuel',
                    'fonctionnalites' => $this->getFonctionnalitesParPlan($planData['nom'])
                ]
            );
            
            $plans->push($plan);
        }
        
        return $plans;
    }
    
    /**
     * Obtient les fonctionnalités disponibles pour un plan spécifique
     *
     * @param string $nomPlan Nom du plan
     * @return array Liste des fonctionnalités
     */
    private function getFonctionnalitesParPlan(string $nomPlan): array
    {
        // Fonctionnalités de base disponibles pour tous les plans
        $fonctionnalites = [
            'gestion_presences',
            'gestion_conges',
            'rapports_basiques',
            'application_mobile',
            'support_technique',
            'mises_a_jour_gratuites'
        ];
        
        // Fonctionnalités supplémentaires pour les plans Business
        if (strpos($nomPlan, 'Business') !== false || strpos($nomPlan, 'Enterprise') !== false) {
            $fonctionnalites = array_merge($fonctionnalites, [
                'rapports_avances',
                'integration_systeme_externe',
                'personnalisation_workflow'
            ]);
        }
        
        // Fonctionnalités exclusives pour les plans Enterprise
        if (strpos($nomPlan, 'Enterprise') !== false) {
            $fonctionnalites = array_merge($fonctionnalites, [
                'support_dedie',
                'api_complete',
                'sla_garanti',
                'formation_personnalisee',
                'audit_securite'
            ]);
            
            // Fonctionnalités additionnelles pour les niveaux Enterprise supérieurs (à partir du niveau 5)
            if (preg_match('/Enterprise Level (\d+)/', $nomPlan, $matches)) {
                $niveau = (int) $matches[1];
                if ($niveau >= 5) {
                    $fonctionnalites = array_merge($fonctionnalites, [
                        'consultant_dedie',
                        'personnalisation_complete',
                        'hebergement_dedie'
                    ]);
                }
            }
        }
        
        return $fonctionnalites;
    }
    
    /**
     * Obtient tous les plans d'abonnement disponibles avec leurs détails
     *
     * @return array Liste des plans avec leurs détails
     */
    public function getTousLesPlans(): array
    {
        $result = [];
        
        foreach (self::PLANS as $plan) {
            $result[] = [
                'nom' => $plan['nom'],
                'min_employes' => $plan['min'],
                'max_employes' => $plan['max'],
                'cout_fixe' => $plan['cout_fixe'],
                'cout_par_employe' => self::COUT_PAR_EMPLOYE,
                'fonctionnalites' => $this->getFonctionnalitesParPlan($plan['nom'])
            ];
        }
        
        return $result;
    }
}
