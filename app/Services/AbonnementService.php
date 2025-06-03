<?php

namespace App\Services;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use App\Models\CodePromo;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AbonnementService
{
    /**
     * Créer un nouvel abonnement pour une entreprise
     *
     * @param Entreprise $entreprise
     * @param PlanAbonnement $planAbonnement
     * @param array $data
     * @return Abonnement
     */
    public function creerAbonnement(Entreprise $entreprise, PlanAbonnement $planAbonnement, array $data)
    {
        DB::beginTransaction();
        try {
            // Calculer les dates de début et de fin
            $dateDebut = Carbon::parse($data['date_debut'] ?? now());
            $typePeriode = $data['type_periode'] ?? 'mensuel';
            $duree = $typePeriode === 'mensuel' ? 30 : 365;
            
            if (isset($data['duree_essai']) && $data['duree_essai'] > 0) {
                $typePeriode = 'essai';
                $duree = $data['duree_essai'];
            }
            
            $dateFin = $dateDebut->copy()->addDays($duree);
            
            // Calculer le montant en utilisant la même logique que ChangeAbonnementService
            $nombreEmployes = $data['nombre_personnels'] ?? $entreprise->employes()->count();
            
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
            } else {
                $coutFixe = $plan['cout_fixe'];
            }
            
            // Calculer le coût des utilisateurs
            $coutUtilisateurs = $nombreEmployes * $coutParEmploye;
            
            // Calculer le coût total
            $montant = $coutFixe + $coutUtilisateurs;
            
            // Appliquer une réduction pour la période annuelle (10 mois au lieu de 12)
            if ($typePeriode === 'annuel') {
                $montant = $montant * 10; // Correction: 10 mois au lieu de 12
            }
            
            // Appliquer le code promo si fourni
            $reductionCodePromo = 0;
            $codePromoId = null;
            
            if (isset($data['code_promo'])) {
                $codePromo = CodePromo::where('code', $data['code_promo'])
                    ->where('date_expiration', '>', now())
                    ->where('statut', 'actif')
                    ->first();
                
                if ($codePromo) {
                    $reductionCodePromo = $codePromo->type === 'pourcentage' 
                        ? $montant * ($codePromo->valeur / 100) 
                        : $codePromo->valeur;
                    
                    $montant -= $reductionCodePromo;
                    $codePromoId = $codePromo->id;
                }
            }
            
            // Créer l'abonnement
            $abonnement = Abonnement::create([
                'entreprise_id' => $entreprise->id,
                'plan_abonnement_id' => $planAbonnement->id,
                'code_promo_id' => $codePromoId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'type_periode' => $typePeriode,
                'montant' => $montant,
                'reduction_code_promo' => $reductionCodePromo,
                'statut' => $data['statut'] ?? 'actif',
                'mode_paiement' => $data['mode_paiement'] ?? 'carte',
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'notes' => $data['notes'] ?? null,
                'renouvellement_automatique' => $data['renouvellement_automatique'] ?? false,
                'periode_facturation' => $data['periode_facturation'] ?? $typePeriode,
                'methode_paiement' => $data['methode_paiement'] ?? null,
                'reference_client' => $data['reference_client'] ?? null,
                'facture_automatique' => $data['facture_automatique'] ?? true,
                'notes_facturation' => $data['notes_facturation'] ?? null,
                'nombre_personnels' => $data['nombre_personnels'] ?? $entreprise->employes()->count()
            ]);
            
            // Désactiver les autres abonnements actifs de l'entreprise
            Abonnement::where('entreprise_id', $entreprise->id)
                ->where('id', '!=', $abonnement->id)
                ->where('statut', 'actif')
                ->update(['statut' => 'inactif']);
            
            DB::commit();
            return $abonnement;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'abonnement: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Activer ou désactiver un abonnement
     *
     * @param Abonnement $abonnement
     * @param bool $activer
     * @return Abonnement
     */
    public function changerStatutAbonnement(Abonnement $abonnement, bool $activer = true)
    {
        DB::beginTransaction();
        try {
            $abonnement->update([
                'statut' => $activer ? 'actif' : 'inactif'
            ]);
            
            // Si on active cet abonnement, désactiver les autres abonnements actifs de l'entreprise
            if ($activer) {
                Abonnement::where('entreprise_id', $abonnement->entreprise_id)
                    ->where('id', '!=', $abonnement->id)
                    ->where('statut', 'actif')
                    ->update(['statut' => 'inactif']);
            }
            
            DB::commit();
            return $abonnement;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de statut de l\'abonnement: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Changer le plan d'abonnement
     *
     * @param Abonnement $abonnement
     * @param PlanAbonnement $nouveauPlan
     * @param array $data
     * @return Abonnement
     */
    public function changerPlanAbonnement(Abonnement $abonnement, PlanAbonnement $nouveauPlan, array $data = [])
    {
        DB::beginTransaction();
        try {
            // Calculer le nouveau montant
            $typePeriode = $data['type_periode'] ?? $abonnement->type_periode;
            $nombreEmployes = $data['nombre_personnels'] ?? $abonnement->nombre_personnels;
            
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
            } else {
                $coutFixe = $plan['cout_fixe'];
            }
            
            // Calculer le coût des utilisateurs
            $coutUtilisateurs = $nombreEmployes * $coutParEmploye;
            
            // Calculer le coût total
            $montant = $coutFixe + $coutUtilisateurs;
            
            // Appliquer une réduction pour la période annuelle (10 mois au lieu de 12)
            if ($typePeriode === 'annuel') {
                $montant = $montant * 10;
            }
            
            // Appliquer le code promo si fourni
            $reductionCodePromo = 0;
            $codePromoId = null;
            
            if (isset($data['code_promo'])) {
                $codePromo = CodePromo::where('code', $data['code_promo'])
                    ->where('date_expiration', '>', now())
                    ->where('statut', 'actif')
                    ->first();
                
                if ($codePromo) {
                    $reductionCodePromo = $codePromo->type === 'pourcentage' 
                        ? $montant * ($codePromo->valeur / 100) 
                        : $codePromo->valeur;
                    
                    $montant -= $reductionCodePromo;
                    $codePromoId = $codePromo->id;
                }
            }
            
            // Mettre à jour l'abonnement
            $abonnement->update([
                'plan_abonnement_id' => $nouveauPlan->id,
                'code_promo_id' => $codePromoId ?? $abonnement->code_promo_id,
                'type_periode' => $typePeriode,
                'montant' => $montant,
                'reduction_code_promo' => $reductionCodePromo,
                'renouvellement_automatique' => $data['renouvellement_automatique'] ?? $abonnement->renouvellement_automatique,
                'periode_facturation' => $data['periode_facturation'] ?? $abonnement->periode_facturation,
                'methode_paiement' => $data['methode_paiement'] ?? $abonnement->methode_paiement,
                'reference_client' => $data['reference_client'] ?? $abonnement->reference_client,
                'facture_automatique' => $data['facture_automatique'] ?? $abonnement->facture_automatique,
                'notes_facturation' => $data['notes_facturation'] ?? $abonnement->notes_facturation,
                'nombre_personnels' => $data['nombre_personnels'] ?? $abonnement->nombre_personnels
            ]);
            
            DB::commit();
            return $abonnement;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du changement de plan d\'abonnement: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Renouveler un abonnement
     *
     * @param Abonnement $abonnement
     * @param array $data
     * @return Abonnement
     */
    public function renouvelerAbonnement(Abonnement $abonnement, array $data = [])
    {
        DB::beginTransaction();
        try {
            // Calculer les nouvelles dates
            $dateDebut = Carbon::parse($data['date_debut'] ?? now());
            $typePeriode = $data['type_periode'] ?? $abonnement->type_periode;
            $duree = $typePeriode === 'mensuel' ? 30 : 365;
            $dateFin = $dateDebut->copy()->addDays($duree);
            
            // Calculer le montant
            $planAbonnement = $abonnement->planAbonnement;
            $montant = $typePeriode === 'mensuel' ? $planAbonnement->prix_mensuel : $planAbonnement->prix_annuel;
            
            // Appliquer le code promo si fourni
            $reductionCodePromo = 0;
            $codePromoId = null;
            
            if (isset($data['code_promo'])) {
                $codePromo = CodePromo::where('code', $data['code_promo'])
                    ->where('date_expiration', '>', now())
                    ->where('statut', 'actif')
                    ->first();
                
                if ($codePromo) {
                    $reductionCodePromo = $codePromo->type === 'pourcentage' 
                        ? $montant * ($codePromo->valeur / 100) 
                        : $codePromo->valeur;
                    
                    $montant -= $reductionCodePromo;
                    $codePromoId = $codePromo->id;
                }
            }
            
            // Mettre à jour l'abonnement
            $abonnement->update([
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'type_periode' => $typePeriode,
                'montant' => $montant,
                'code_promo_id' => $codePromoId,
                'reduction_code_promo' => $reductionCodePromo,
                'statut' => 'actif',
                'mode_paiement' => $data['mode_paiement'] ?? $abonnement->mode_paiement,
                'reference_paiement' => $data['reference_paiement'] ?? $abonnement->reference_paiement,
                'notes' => $data['notes'] ?? $abonnement->notes,
                'renouvellement_automatique' => $data['renouvellement_automatique'] ?? $abonnement->renouvellement_automatique,
                'periode_facturation' => $data['periode_facturation'] ?? $abonnement->periode_facturation,
                'methode_paiement' => $data['methode_paiement'] ?? $abonnement->methode_paiement,
                'reference_client' => $data['reference_client'] ?? $abonnement->reference_client,
                'facture_automatique' => $data['facture_automatique'] ?? $abonnement->facture_automatique,
                'notes_facturation' => $data['notes_facturation'] ?? $abonnement->notes_facturation,
                'nombre_personnels' => $data['nombre_personnels'] ?? $abonnement->nombre_personnels
            ]);
            
            DB::commit();
            return $abonnement;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du renouvellement de l\'abonnement: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Vérifier si une entreprise a un abonnement actif
     *
     * @param Entreprise $entreprise
     * @return bool
     */
    public function hasAbonnementActif(Entreprise $entreprise)
    {
        return $entreprise->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>', now())
            ->exists();
    }
    
    /**
     * Obtenir l'abonnement actif d'une entreprise
     *
     * @param Entreprise $entreprise
     * @return Abonnement|null
     */
    public function getAbonnementActif(Entreprise $entreprise)
    {
        return $entreprise->abonnements()
            ->where('statut', 'actif')
            ->where('date_fin', '>', now())
            ->latest('date_debut')
            ->first();
    }
    
    /**
     * Vérifier si une entreprise a accès à une fonctionnalité spécifique
     *
     * @param Entreprise $entreprise
     * @param string $fonctionnalite
     * @return bool
     */
    public function hasAccesToFeature(Entreprise $entreprise, string $fonctionnalite)
    {
        $abonnement = $this->getAbonnementActif($entreprise);
        
        if (!$abonnement) {
            return false;
        }
        
        return $abonnement->planAbonnement->hasFonctionnalite($fonctionnalite);
    }
    
    /**
     * Vérifier si un abonnement est proche de l'expiration
     *
     * @param Abonnement $abonnement
     * @param int $joursAvantAlerte
     * @return bool
     */
    public function isProcheExpiration(Abonnement $abonnement, int $joursAvantAlerte = 7)
    {
        return $abonnement->date_fin->diffInDays(now()) <= $joursAvantAlerte;
    }
    
    /**
     * Obtenir la liste des abonnements qui expirent bientôt
     *
     * @param int $joursAvantAlerte
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAbonnementsExpirantBientot(int $joursAvantAlerte = 7)
    {
        $dateLimite = now()->addDays($joursAvantAlerte);
        
        return Abonnement::where('statut', 'actif')
            ->whereBetween('date_fin', [now(), $dateLimite])
            ->get();
    }
    
    /**
     * Vérifier si une entreprise a dépassé la limite d'employés de son abonnement
     *
     * @param Entreprise $entreprise
     * @return bool
     */
    public function hasDepaseLimiteEmployes(Entreprise $entreprise)
    {
        $abonnement = $this->getAbonnementActif($entreprise);
        
        if (!$abonnement) {
            return true;
        }
        
        $nombreEmployes = $entreprise->employes()->count();
        return $nombreEmployes > $abonnement->planAbonnement->nombre_employes_max;
    }
    
    /**
     * Calculer le coût supplémentaire pour les employés en excès
     *
     * @param Entreprise $entreprise
     * @return float
     */
    public function calculerCoutSupplementaireEmployes(Entreprise $entreprise)
    {
        $abonnement = $this->getAbonnementActif($entreprise);
        
        if (!$abonnement || $abonnement->planAbonnement->cout_par_employe <= 0) {
            return 0;
        }
        
        $nombreEmployes = $entreprise->employes()->count();
        $nombreEmployesMax = $abonnement->planAbonnement->nombre_employes_max;
        
        if ($nombreEmployes <= $nombreEmployesMax) {
            return 0;
        }
        
        $employesExcedentaires = $nombreEmployes - $nombreEmployesMax;
        return $employesExcedentaires * $abonnement->planAbonnement->cout_par_employe;
    }
}
