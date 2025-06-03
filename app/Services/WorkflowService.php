<?php

namespace App\Services;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use App\Models\Facturation;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkflowService
{
    /**
     * Créer un compte utilisateur
     *
     * @param array $userData
     * @return User
     * @throws \Exception Si l'utilisateur existe déjà
     */
    public function createUserAccount(array $userData)
    {
        Log::info('Création du compte utilisateur', ['email' => $userData['email']]);
        
        return DB::transaction(function () use ($userData) {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('email', $userData['email'])->first();
            
            if ($existingUser) {
                // Vérifier s'il a une entreprise avec un abonnement actif
                $hasActiveSubscription = $existingUser->entreprises()
                    ->whereHas('abonnements', function($query) {
                        $query->where('statut', 'actif');
                    })
                    ->exists();

                if ($hasActiveSubscription) {
                    Log::info('Utilisateur déjà associé à une entreprise active', ['user_id' => $existingUser->id]);
                    throw new \Exception('Cet email est déjà utilisé et associé à une entreprise active. Veuillez vous connecter.');
                }

                // Sinon, on continue le process d'onboarding avec ce mail
                Log::info('Utilisateur existant sans entreprise active, onboarding possible', ['user_id' => $existingUser->id]);
                return $existingUser;
            }
            
            $user = User::create([
                'name' => $userData['full_name'] ?? 'Admin User',
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'telephone' => $userData['telephone'],
                'phone' => $userData['telephone'],
                'statut' => 'actif',
                'role' => 'admin', // L'utilisateur initial est admin
                'langue' => 'fr',   
                'fuseau_horaire' => 'Africa/Abidjan',
                'settings' => json_encode([
                    'notifications_email' => true,
                    'notifications_app' => true
                ]),
                'preferences' => json_encode([
                    'theme' => 'light',
                    'sidebar_collapsed' => false
                ])
            ]);
            
            // Vérifier si le rôle admin existe avant de l'assigner
            if (\Spatie\Permission\Models\Role::where('name', 'admin')->exists()) {
                // Assigner le rôle admin
                $user->assignRole('admin');
            } else {
                Log::warning('Le rôle admin n\'existe pas. Création du rôle.');
                // Créer le rôle admin s'il n'existe pas
                $adminRole = \Spatie\Permission\Models\Role::create([
                    'name' => 'admin',
                    'description' => 'Administrateur',
                    'is_system' => true,
                    'guard_name' => 'web'
                ]);
                
                // Assigner le rôle admin
                $user->assignRole($adminRole);
            }
            
            Log::info('User account created in database', ['user' => $user]);
            
            return $user;
        });
    }
    
    /**
     * Créer un compte entreprise
     *
     * @param array $companyData
     * @param User $user
     * @return Entreprise
     */
    public function createCompanyAccount(array $companyData, User $user)
    {
        Log::info('Création du compte entreprise', ['nom' => $companyData['company_name']]);
        
        return DB::transaction(function () use ($companyData, $user) {
            // Créer l'entreprise
            $entreprise = Entreprise::create([
                'nom' => $companyData['company_name'],
                'secteur_activite' => $companyData['industry'],
                'email' => $companyData['contact_email'],
                'telephone' => $companyData['contact_phone'],
                'adresse' => $companyData['address'],
                'statut' => 'actif',
                'nombre_employes' => $companyData['company_size'],
                'configuration' => json_encode([
                    'nombre_employes' => $companyData['company_size'],
                    'date_creation' => Carbon::now()->format('Y-m-d')
                ])
            ]);
            
            // Mettre à jour l'utilisateur avec l'ID de l'entreprise
            $user->update([
                'entreprise_id' => $entreprise->id
            ]);

            
            return $entreprise;
        });
    }
    
    /**
     * Créer un abonnement pour l'entreprise
     *
     * @param array $subscriptionData
     * @param Entreprise $entreprise
     * @return Abonnement
     */
    public function createSubscription(array $subscriptionData, Entreprise $entreprise)
    {
        Log::info('Création de l\'abonnement', [
            'entreprise' => $entreprise->nom,
            'plan' => $subscriptionData['subscription_plan']
        ]);

        
        return DB::transaction(function () use ($subscriptionData, $entreprise) {
            // Trouver le plan d'abonnement correspondant
            $planAbonnement = $this->findOrCreatePlanAbonnement($subscriptionData);
            
            // Créer l'abonnement
            $abonnement = Abonnement::create([
                'entreprise_id' => $entreprise->id,
                'plan_abonnement_id' => $planAbonnement->id,
                'date_debut' => Carbon::now(),
                'date_fin' => Carbon::now()->addMonth(),
                'type_periode' => 'mensuel',
                'montant' => $subscriptionData['total_cost'],
                'statut' => 'en_attente',
                'mode_paiement' => 'non_specifie', // Valeur par défaut
                'periode_facturation' => 'mensuel',
                'renouvellement_automatique' => true,
                'facture_automatique' => true,
                'nombre_personnels' => $entreprise->nombre_employes ?? 0
            ]);
            
            return $abonnement;
        });
    }
    
    /**
     * Trouver ou créer un plan d'abonnement
     *
     * @param array $subscriptionData
     * @return PlanAbonnement
     */
    private function findOrCreatePlanAbonnement(array $subscriptionData)
    {
        // Chercher un plan existant
        $plan = PlanAbonnement::where('nom', $subscriptionData['subscription_plan'])->first();
        
        if (!$plan) {
            // Créer un nouveau plan si nécessaire
            $plan = PlanAbonnement::create([
                'nom' => $subscriptionData['subscription_plan'],
                'description' => 'Plan ' . $subscriptionData['subscription_plan'],
                'prix_mensuel' => $subscriptionData['base_cost'],
                'prix_annuel' => $subscriptionData['base_cost'] * 10, // 10 mois au lieu de 12 pour incitation
                'cout_par_employe' => 100, // Coût par employé fixé à 100 FCFA
                'statut' => 'actif',
                'devise' => 'FCFA',
                'periode_facturation' => 'mensuel'
            ]);
            
            // Définir les limites du nombre d'employés selon le plan
            switch ($subscriptionData['subscription_plan']) {
                case 'Starter':
                    $plan->nombre_employes_min = 1;
                    $plan->nombre_employes_max = 50;
                    break;
                case 'Side Business':
                    $plan->nombre_employes_min = 51;
                    $plan->nombre_employes_max = 100;
                    break;
                case 'Enterprise':
                    $plan->nombre_employes_min = 101;
                    $plan->nombre_employes_max = 1000; // Illimité
                    break;
            }
            
            $plan->save();
        }
        
        return $plan;
    }
    
    /**
     * Traiter le paiement et générer une facture
     *
     * @param array $paymentData
     * @param Abonnement $abonnement
     * @return Facturation
     */
    public function processPayment(array $paymentData, Abonnement $abonnement)
    {
        Log::info('Traitement du paiement', [
            'methode' => $paymentData['payment_method'],
            'abonnement' => $abonnement->id
        ]);
        
        return DB::transaction(function () use ($paymentData, $abonnement) {
            // Convertir la méthode de paiement en une valeur valide pour l'enum mode_paiement
            $modePaiement = 'non_specifie'; // Valeur par défaut
            
            // Si la méthode de paiement correspond à une valeur valide, l'utiliser
            if (in_array($paymentData['payment_method'], ['carte', 'virement', 'especes'])) {
                $modePaiement = $paymentData['payment_method'];
            }
            
            // Mettre à jour l'abonnement avec la méthode de paiement
            $abonnement->update([
                'mode_paiement' => $modePaiement,
                'reference_paiement' => $paymentData['invoice_number'],
                // L'abonnement reste en attente jusqu'à ce que le paiement soit validé
                'statut' => 'en_attente'
            ]);
            
            // Créer la facture
            $facturation = Facturation::create([
                'entreprise_id' => $abonnement->entreprise_id,
                'abonnement_id' => $abonnement->id,
                'numero_facture' => $paymentData['invoice_number'],
                'date_facturation' => Carbon::now(),
                'date_echeance' => Carbon::now()->addDays(7),
                'montant_ht' => $abonnement->montant,
                'taux_tva' => 0, // À ajuster selon la réglementation fiscale
                'montant_tva' => 0,
                'montant_ttc' => $abonnement->montant,
                'statut_paiement' => 'en_attente',
                'mode_paiement' => $modePaiement,
                'reference_paiement' => $paymentData['invoice_number'],
                'devise' => 'FCFA'
            ]);
            
            return $facturation;
        });
    }
    
    /**
     * Exécuter le workflow complet d'inscription
     *
     * @param array $userData
     * @param array $companyData
     * @param array $subscriptionData
     * @param array $paymentData
     * @return array
     */
    public function executeCompleteWorkflow(array $userData, array $companyData, array $subscriptionData, array $paymentData)
    {
        Log::info('Démarrage du workflow complet d\'inscription', ['email' => $userData['email']]);
        
        return DB::transaction(function () use ($userData, $companyData, $subscriptionData, $paymentData) {
            // Étape 1: Créer le compte utilisateur
            $user = $this->createUserAccount($userData);
            
            // Étape 2: Créer le compte entreprise
            $entreprise = $this->createCompanyAccount($companyData, $user);
            
            // Étape 3: Créer l'abonnement
            $abonnement = $this->createSubscription($subscriptionData, $entreprise);
            
            // Étape 4: Traiter le paiement et générer la facture
            $facturation = $this->processPayment($paymentData, $abonnement);
            
            return [
                'user' => $user,
                'entreprise' => $entreprise,
                'abonnement' => $abonnement,
                'facturation' => $facturation
            ];
        });
    }

    /**
     * Compléter le workflow après un paiement validé
     *
     * @param \App\Models\Paiement $paiement
     * @return bool
     */
    public function completeWorkflowAfterPayment(\App\Models\Paiement $paiement)
    {
        Log::info('Complétion du workflow après paiement validé', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'facturation_id' => $paiement->facturation_id,
            'abonnement_id' => $paiement->abonnement_id,
            'statut_paiement' => $paiement->statut,
            'methode' => $paiement->methode,
            'passerelle' => $paiement->passerelle
        ]);
        
        return DB::transaction(function () use ($paiement) {
            // Récupérer les objets associés
            $facturation = $paiement->facturation;
            $abonnement = $paiement->abonnement ?? $facturation->abonnement;
            $entreprise = $paiement->entreprise ?? $abonnement->entreprise;
            
            Log::info('Objets associés récupérés pour complétion du workflow', [
                'facturation_id' => $facturation->id ?? null,
                'facturation_statut' => $facturation->statut_paiement ?? null,
                'abonnement_id' => $abonnement->id ?? null,
                'abonnement_statut' => $abonnement->statut ?? null,
                'entreprise_id' => $entreprise->id ?? null,
                'entreprise_statut' => $entreprise->statut ?? null
            ]);
            
            // Mettre à jour le statut de la facturation
            if ($facturation) {
                $facturation->update([
                    'statut_paiement' => 'payé',
                    'mode_paiement' => $paiement->methode,
                    'reference_paiement' => $paiement->reference
                ]);
                
                Log::info('Facturation mise à jour avec succès', [
                    'facturation_id' => $facturation->id,
                    'nouveau_statut' => 'payé'
                ]);
            }
            
            // Activer l'abonnement
            if ($abonnement) {
                $abonnement->update([
                    'statut' => 'actif',
                    'mode_paiement' => $paiement->methode,
                    'reference_paiement' => $paiement->reference
                ]);
                
                Log::info('Abonnement activé avec succès', [
                    'abonnement_id' => $abonnement->id,
                    'nouveau_statut' => 'actif'
                ]);
            }
            
            // Mettre à jour le statut de l'entreprise si nécessaire
            if ($entreprise && $entreprise->statut !== 'actif') {
                $entreprise->update([
                    'statut' => 'actif'
                ]);
                
                Log::info('Entreprise activée avec succès', [
                    'entreprise_id' => $entreprise->id,
                    'nouveau_statut' => 'actif'
                ]);
            }
            
            Log::info('Workflow complété avec succès après paiement', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'facturation_id' => $facturation->id ?? null,
                'abonnement_id' => $abonnement->id ?? null,
                'entreprise_id' => $entreprise->id ?? null
            ]);
            
            return true;
        });
    }

    /**
     * Démarrer le workflow pour un paiement
     *
     * @param \App\Models\Paiement $paiement
     * @return bool
     */
    public function demarrerWorkflow(\App\Models\Paiement $paiement)
    {
        Log::info('Démarrage du workflow pour un paiement', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'facturation_id' => $paiement->facturation_id,
            'methode' => $paiement->methode,
            'passerelle' => $paiement->passerelle,
            'montant' => $paiement->montant,
            'devise' => $paiement->devise
        ]);
        
        // Vérifier que le paiement est bien lié à une facturation
        if (!$paiement->facturation) {
            Log::error('Impossible de démarrer le workflow: paiement sans facturation', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            return false;
        }
        
        // Vérifier que la facturation est liée à un abonnement
        $facturation = $paiement->facturation;
        if (!$facturation->abonnement) {
            Log::error('Impossible de démarrer le workflow: facturation sans abonnement', [
                'facturation_id' => $facturation->id,
                'paiement_id' => $paiement->id
            ]);
            return false;
        }
        
        // Mettre à jour le paiement avec les références de l'abonnement et de l'entreprise
        $abonnement = $facturation->abonnement;
        $entreprise = $abonnement->entreprise;
        
        Log::info('Associations trouvées pour le workflow', [
            'facturation_id' => $facturation->id,
            'facturation_numero' => $facturation->numero_facture ?? 'N/A',
            'abonnement_id' => $abonnement->id,
            'abonnement_plan' => $abonnement->plan_abonnement_id ?? 'N/A',
            'entreprise_id' => $entreprise->id,
            'entreprise_nom' => $entreprise->nom ?? 'N/A'
        ]);
        
        $paiement->update([
            'abonnement_id' => $abonnement->id,
            'entreprise_id' => $entreprise->id
        ]);
        
        Log::info('Workflow démarré avec succès pour le paiement', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'abonnement_id' => $abonnement->id,
            'entreprise_id' => $entreprise->id
        ]);
        
        return true;
    }
}
