<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use App\Services\ChangeAbonnementService;
use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChangeAbonnementController extends Controller
{
    protected $changeAbonnementService;
    protected $abonnementService;

    public function __construct(ChangeAbonnementService $changeAbonnementService, AbonnementService $abonnementService)
    {
        $this->changeAbonnementService = $changeAbonnementService;
        $this->abonnementService = $abonnementService;
    }

    /**
     * Afficher le formulaire de changement d'abonnement
     *
     * @param Request $request
     * @param string $abonnementId
     * @return \Illuminate\View\View
     */
    public function showChangeForm(Request $request, string $abonnementId)
    {
        $abonnement = Abonnement::findOrFail($abonnementId);
        $entreprise = $abonnement->entreprise;
        
        // Vérifier que l'utilisateur a les droits sur cette entreprise
        $this->authorize('view', $entreprise);

        $planAbonnementActuel = $abonnement->planAbonnement;
        
        // Récupérer les plans d'abonnement disponibles
        $plansAbonnement = PlanAbonnement::actif()->parPriorite()->get();
        
        return view('abonnements.change-form', [
            'abonnement' => $abonnement,
            'planAbonnementActuel' => $planAbonnementActuel,
            'entreprise' => $entreprise,
            'plansAbonnement' => $plansAbonnement,
            'nombreEmployesActuel' => $abonnement->nombre_personnels ?? $entreprise->employes()->count(),
            'typePeriodeActuel' => $abonnement->type_periode
        ]);
    }

    /**
     * Traiter la demande de changement d'abonnement
     *
     * @param Request $request
     * @param string $abonnementId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processChangeRequest(Request $request, string $abonnementId)
    {
        $abonnement = Abonnement::findOrFail($abonnementId);
        $planAbonnementActuel = $abonnement->planAbonnement;

        
        // Validation avec règle personnalisée pour le nombre d'employés
        $validated = $request->validate([
            'nombre_employes' => [
                'required',
                'integer',
                'min:' . $planAbonnementActuel->nombre_employes_max // Doit être au moins égal au max du plan actuel
            ],
            'type_periode' => 'required|in:mensuel,annuel'
        ]);
        
        $entreprise = $abonnement->entreprise;
        
        try {
            // Préparer le changement d'abonnement
            $changementData = $this->changeAbonnementService->preparerChangementAbonnement(
                $abonnement,
                $validated['nombre_employes'],
                $validated['type_periode']
            );
            
            // Stocker les informations dans la session pour les récupérer à l'étape suivante
            session()->put('changement_abonnement', [
                'abonnement_id' => $abonnement->id,
                'plan_abonnement_id' => $changementData['plan_abonnement']->id,
                'facturation_id' => $changementData['facturation']->id,
                'nombre_employes' => $validated['nombre_employes'],
                'type_periode' => $validated['type_periode'],
                'montant' => $changementData['calcul_cout']
            ]);
            
            // Rediriger vers la page de paiement 
            return redirect()->route('abonnements.change.payment');
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la préparation du changement d'abonnement", [
                'abonnement_id' => $abonnement->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => "Une erreur est survenue lors de la préparation du changement d'abonnement: " . $e->getMessage()]);
        }
    }

    /**
     * Afficher la page de confirmation du changement d'abonnement
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showConfirmation(Request $request)

    {

        $changementData = session()->get('changement_abonnement');
        
        if (!$changementData) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => "Aucune demande de changement d'abonnement en cours."]);
        }
        
        $abonnement = Abonnement::findOrFail($changementData['abonnement_id']);
        $nouveauPlan = PlanAbonnement::findOrFail($changementData['plan_abonnement_id']);
        $facturation = $abonnement->facturations()->findOrFail($changementData['facturation_id']);

        
        return view('abonnements.change-confirm', [
            'abonnement' => $abonnement,
            'nouveauPlan' => $nouveauPlan,
            'facturation' => $facturation,
            'nombreEmployes' => $changementData['nombre_employes'],
            'typePeriode' => $changementData['type_periode'],
            'montant' => $changementData['montant']
        ]);
    }

    /**
     * Initialiser le paiement pour le changement d'abonnement
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initierPaiement(Request $request)
    {
        $changementData = session()->get('changement_abonnement');
        
        if (!$changementData) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => "Aucune demande de changement d'abonnement en cours."]);
        }
        
        $abonnement = Abonnement::findOrFail($changementData['abonnement_id']);
        $facturation = $abonnement->facturations()->findOrFail($changementData['facturation_id']);

        
        // Initialiser le paiement via Paystack
        $user = Auth::user();
        $resultatPaiement = $this->changeAbonnementService->initialiserPaiement($facturation, $user, [
            'methode' => 'card',
            'type_transaction' => 'changement_abonnement'
        ]);
        
        if ($resultatPaiement['success']) {
            // Rediriger vers la page de paiement Paystack
            return redirect()->away($resultatPaiement['redirect_url']);
        } else {
            // En cas d'erreur, rediriger vers la page de confirmation avec un message d'erreur
            return redirect()->route('abonnements.change.confirm')
                ->withErrors(['error' => "Erreur lors de l'initialisation du paiement: " . $resultatPaiement['message']]);
        }
    }

    /**
     * Traiter le retour du paiement (callback)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handlePaymentCallback(Request $request)
    {

       
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => "Référence de paiement manquante."]);
        }
        
        // Récupérer le paiement
        $paiement = \App\Models\Paiement::where('reference', $reference)
            ->orWhere('reference_externe', $reference)
            ->first();
        
        if (!$paiement) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => "Paiement non trouvé."]);
        }
        
        // Vérifier le statut du paiement
        $paystackService = app(\App\Services\Paiement\PaystackService::class);
        $resultatVerification = $paystackService->verifierStatut($paiement);
        
        if ($resultatVerification['success'] && $resultatVerification['status'] === 'complete') {
            // Paiement réussi, finaliser le changement d'abonnement
            $changementData = session()->get('changement_abonnement');
            
            if ($changementData) {
                $abonnement = Abonnement::findOrFail($changementData['abonnement_id']);
                $nouveauPlan = PlanAbonnement::findOrFail($changementData['plan_abonnement_id']);
                
                try {
                    // Finaliser le changement d'abonnement
                    $this->changeAbonnementService->finaliserChangementAbonnement($abonnement, $nouveauPlan, [
                        'type_periode' => $changementData['type_periode'],
                        'nombre_employes' => $changementData['nombre_employes'],
                        'mode_paiement' => 'carte',
                        'reference_paiement' => $paiement->reference
                    ]);
                    
                    // Nettoyer la session
                    session()->forget('changement_abonnement');
                    
                    // Rediriger vers une page de succès
                    return redirect()->route('abonnements.change.success')
                        ->with('success', "Votre abonnement a été mis à jour avec succès!");
                } catch (\Exception $e) {
                    Log::error("Erreur lors de la finalisation du changement d'abonnement", [
                        'abonnement_id' => $abonnement->id,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    return redirect()->route('dashboard')
                        ->withErrors(['error' => "Une erreur est survenue lors de la finalisation du changement d'abonnement: " . $e->getMessage()]);
                }
            }
        }
        
        // Si le paiement a échoué ou est en attente
        if ($resultatVerification['status'] === 'en_attente' || $resultatVerification['status'] === 'en_traitement') {
            return redirect()->route('dashboard')
                ->with('info', "Votre paiement est en cours de traitement. Vous serez notifié une fois le paiement confirmé.");
        }
        
        // Si le paiement a échoué
        return redirect()->route('dashboard')
            ->withErrors(['error' => "Le paiement a échoué. Veuillez réessayer ou contacter le support."]);
    }

    /**
     * Afficher la page de succès
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showSuccess(Request $request)
    {
        return view('abonnements.change-success');
    }
}
