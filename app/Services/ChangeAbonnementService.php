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
     * @return array
     */
    public function calculerForfaitEtCout(int $nombreEmployes, string $typePeriode = 'mensuel'): array
    {
        // Déterminer le forfait et le coût fixe
        $forfait = '';
        $coutFixe = 0;
        
        if ($nombreEmployes >= 1 && $nombreEmployes <= 50) {
            $forfait = 'Starter';
            $coutFixe = 10000;
        } elseif ($nombreEmployes > 50 && $nombreEmployes <= 100) {
            $forfait = 'Side Business';
            $coutFixe = 15000;
        } elseif ($nombreEmployes > 100) {
            $forfait = 'Entreprise';
            $coutFixe = 30000;
        }
        
        // Calculer le coût des utilisateurs
        $coutUtilisateurs = $nombreEmployes * 100;
        
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
            throw new \Exception("Plan d'abonnement non trouvé pour le forfait: {$calculCout['forfait']}");
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
                    'taille' => $data['nombre_employes']
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
