<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\Facturation;
use App\Models\PlanAbonnement;
use App\Models\User;
use App\Services\Paiement\PaystackService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChangeAbonnementService
{
    protected $abonnementService;
    protected $paystackService;

    public function __construct(AbonnementService $abonnementService, PaystackService $paystackService)
    {
        $this->abonnementService = $abonnementService;
        $this->paystackService = $paystackService;
    }

    /**
     * Calculer le forfait et le coût en fonction du nombre d'employés
     *
     * @param int $nombreEmployes
     * @param string $typePeriode
     * @return array
     */
    public function calculerForfaitEtCout(int $nombreEmployes, string $typePeriode = 'mensuel'): array
    {
        // Coût par employé (fixe)
        $coutParEmploye = 100;
        
        // Structure des plans d'abonnement selon TarificationService
        $plans = [
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
        
        // Déterminer le forfait et le coût fixe
        $forfait = '';
        $coutFixe = 0;
        $plan = null;
        
        // Trouver le plan approprié
        foreach ($plans as $p) {
            if ($nombreEmployes >= $p['min'] && $nombreEmployes <= $p['max']) {
                $plan = $p;
                break;
            }
        }
        
        // Si le nombre d'employés dépasse les plans prédéfinis, calculer dynamiquement
        if ($plan === null) {
            $niveau = floor($nombreEmployes / 100) + 1;
            $min = ($niveau - 1) * 100;
            $max = $min + 99;
            $coutFixe = $niveau * 100000;
            $forfait = 'Enterprise Level ' . $niveau;
        } else {
            $forfait = $plan['nom'];
            $coutFixe = $plan['cout_fixe'];
        }
        
        // Calculer le coût des utilisateurs
        $coutUtilisateurs = $nombreEmployes * $coutParEmploye;
        
        // Calculer le coût total
        $coutTotal = $coutFixe + $coutUtilisateurs;
        
        // Appliquer une réduction pour la période annuelle (10 mois au lieu de 12)
        if ($typePeriode === 'annuel') {
            $coutTotal = $coutTotal * 10;
            $periode = 'annuel';
        } else {
            $periode = 'mensuel';
        }
        
        return [
            'forfait' => $forfait,
            'cout_fixe' => $coutFixe,
            'cout_utilisateurs' => $coutUtilisateurs,
            'cout_total' => $coutTotal,
            'nombre_employes' => $nombreEmployes,
            'type_periode' => $periode
        ];
    }

    /**
     * Préparer le changement d'abonnement
     *
     * @param Abonnement $abonnement
     * @param int $nombreEmployes
     * @param string $typePeriode
     * @return array
     */
    public function preparerChangementAbonnement(Abonnement $abonnement, int $nombreEmployes, string $typePeriode = 'mensuel'): array
    {
        // Calculer le forfait et le coût
        $calculCout = $this->calculerForfaitEtCout($nombreEmployes, $typePeriode);
        
        // Trouver le plan d'abonnement correspondant
        $planAbonnement = PlanAbonnement::where('nom', $calculCout['forfait'])->first();
        
        if (!$planAbonnement) {
            // Créer un nouveau plan si nécessaire
            Log::info("Création automatique d'un plan d'abonnement", [
                'plan' => $calculCout['forfait'],
                'cout_fixe' => $calculCout['cout_fixe'],
                'nombre_employes' => $nombreEmployes
            ]);
            
            $planAbonnement = PlanAbonnement::create([
                'nom' => $calculCout['forfait'],
                'description' => 'Plan ' . $calculCout['forfait'],
                'prix_mensuel' => $calculCout['cout_fixe'],
                'prix_annuel' => $calculCout['cout_fixe'] * 10, // 10 mois au lieu de 12 pour incitation
                'cout_par_employe' => 100, // Coût par employé fixé à 100 FCFA
                'statut' => 'actif',
                'devise' => 'FCFA',
                'periode_facturation' => 'mensuel'
            ]);
            
            // Déterminer les limites du nombre d'employés selon le plan
            if (strpos($calculCout['forfait'], 'Starter Level 1') !== false) {
                $planAbonnement->nombre_employes_min = 0;
                $planAbonnement->nombre_employes_max = 14;
            } elseif (strpos($calculCout['forfait'], 'Starter Level 2') !== false) {
                $planAbonnement->nombre_employes_min = 15;
                $planAbonnement->nombre_employes_max = 24;
            } elseif (strpos($calculCout['forfait'], 'Business Level 1') !== false) {
                $planAbonnement->nombre_employes_min = 25;
                $planAbonnement->nombre_employes_max = 49;
            } elseif (strpos($calculCout['forfait'], 'Business Level 2') !== false) {
                $planAbonnement->nombre_employes_min = 50;
                $planAbonnement->nombre_employes_max = 74;
            } elseif (strpos($calculCout['forfait'], 'Business Level 3') !== false) {
                $planAbonnement->nombre_employes_min = 75;
                $planAbonnement->nombre_employes_max = 99;
            } elseif (preg_match('/Enterprise Level (\d+)/', $calculCout['forfait'], $matches)) {
                $level = (int)$matches[1];
                $minEmployes = 100 + ($level - 1) * 100;
                $maxEmployes = $minEmployes + 99;
                $planAbonnement->nombre_employes_min = $minEmployes;
                $planAbonnement->nombre_employes_max = $maxEmployes;
            }
            
            $planAbonnement->save();
        }
        
        // Calculer le montant en fonction de la période
        $montant = $typePeriode === 'mensuel' ? $planAbonnement->prix_mensuel : $planAbonnement->prix_annuel;
        
        // Si le montant est nul, utiliser le coût calculé
        if ($montant == 0) {
            $montant = $calculCout['cout_total'];
        }
        
        // Créer une facturation pour le changement d'abonnement
        $facturation = $this->creerFacturationChangement($abonnement, $calculCout['cout_total'], $typePeriode);
        
        return [
            'abonnement' => $abonnement,
            'plan_abonnement' => $planAbonnement,
            'facturation' => $facturation,
            'montant' => $montant,
            'type_periode' => $typePeriode,
            'nombre_employes' => $nombreEmployes,
            'calcul_cout' => $calculCout['cout_total']
        ];
    }

    /**
     * Créer une facturation pour le changement d'abonnement
     *
     * @param Abonnement $abonnement
     * @param float $montant
     * @param string $typePeriode
     * @return Facturation
     */
    protected function creerFacturationChangement(Abonnement $abonnement, float $montant, string $typePeriode): Facturation
    {
        $entreprise = $abonnement->entreprise;
        
        // Calculer les dates
        $dateFacturation = now();
        $dateEcheance = $dateFacturation->copy()->addDays(7); // Échéance à 7 jours
        
        // Calculer la TVA (si applicable)
        $tauxTva = config('facturation.taux_tva', 0); // Taux par défaut ou configurable
        $montantHt = $montant;
        $montantTva = $montantHt * ($tauxTva / 100);
        $montantTtc = $montantHt + $montantTva;
        
        // Générer un numéro de facture
        $numeroFacture = 'CHANGE-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        // Créer la facturation
        return Facturation::create([
            'entreprise_id' => $entreprise->id,
            'abonnement_id' => $abonnement->id,
            'numero_facture' => $numeroFacture,
            'date_facturation' => $dateFacturation,
            'date_echeance' => $dateEcheance,
            'montant_ht' => $montantHt,
            'taux_tva' => $tauxTva,
            'montant_tva' => $montantTva,
            'montant_ttc' => $montantTtc,
            'montant_paye' => $montantHt,   
            'statut_paiement' => 'en_attente',
            'notes' => "Changement d'abonnement - {$typePeriode}",
            'devise' => $abonnement->planAbonnement->devise ?? 'FCFA'
        ]);
    }

    /**
     * Initialiser le paiement pour le changement d'abonnement
     *
     * @param Facturation $facturation
     * @param User $initiateur
     * @param array $options
     * @return array
     */
    public function initialiserPaiement(Facturation $facturation, User $initiateur, array $options = []): array
    {
        return $this->paystackService->initialiser($facturation, $initiateur, $options);
    }

    /**
     * Finaliser le changement d'abonnement après paiement réussi
     *
     * @param Abonnement $abonnement
     * @param PlanAbonnement $nouveauPlan
     * @param array $data
     * @return Abonnement
     */
    public function finaliserChangementAbonnement(Abonnement $abonnement, PlanAbonnement $nouveauPlan, array $data): Abonnement
    {
        DB::beginTransaction();
        try {
            
            // Mettre à jour l'abonnement avec le nouveau plan
            $abonnementMisAJour = $this->abonnementService->changerPlanAbonnement($abonnement, $nouveauPlan, [
                'type_periode' => $data['type_periode'] ?? 'mensuel',
                'nombre_personnels' => $data['nombre_employes'] ?? $abonnement->nombre_personnels,
                'statut' => 'actif',
                'mode_paiement' => $data['mode_paiement'] ?? 'carte',
                'reference_paiement' => $data['reference_paiement'] ?? null
            ]);
            
          
            
            // Renouveler l'abonnement pour mettre à jour les dates
            $abonnementMisAJour->renouveler($data['type_periode'] ?? 'mensuel');
            
            Log::info("Abonnement renouvelé", [
                'abonnement_id' => $abonnement->id,
                'ancien_plan' => $abonnement->planAbonnement->nom,
                'nouveau_plan' => $nouveauPlan->nom,
                'type_periode' => $data['type_periode'] ?? 'mensuel',
                'nombre_employes' => $data['nombre_employes'] ?? $abonnement->nombre_personnels
            ]);
            
            // Mettre à jour le nombre d'employés de l'entreprise si nécessaire
            if (isset($data['nombre_employes']) && $data['nombre_employes'] > 0) {
                $entreprise = $abonnement->entreprise;
                $entreprise->update([
                    'nombre_employes' => $data['nombre_employes']
                ]);

                Log::info("Nombre d'employés mis à jour", [
                    'entreprise_id' => $entreprise->id,
                    'ancien_nombre_employes' => $abonnement->nombre_personnels,
                    'nouveau_nombre_employes' => $data['nombre_employes']
                ]);
            }
            
            // Journaliser le changement
            Log::info("Changement d'abonnement réussi", [
                'abonnement_id' => $abonnement->id,
                'ancien_plan' => $abonnement->planAbonnement->nom,
                'nouveau_plan' => $nouveauPlan->nom,
                'type_periode' => $data['type_periode'] ?? 'mensuel',
                'nombre_employes' => $data['nombre_employes'] ?? $abonnement->nombre_personnels
            ]);
            
            DB::commit();
            return $abonnementMisAJour;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du changement d'abonnement", [
                'abonnement_id' => $abonnement->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
