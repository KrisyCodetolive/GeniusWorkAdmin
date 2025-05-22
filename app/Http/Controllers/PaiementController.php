<?php

namespace App\Http\Controllers;

use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;
use App\Services\Paiement\PaiementService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    protected $paiementService;
    protected $workflowService;

    /**
     * Constructeur avec injection du service
     */
    public function __construct(PaiementService $paiementService, WorkflowService $workflowService)
    {
        $this->paiementService = $paiementService;
        $this->workflowService = $workflowService;
        
        // Exclure les routes de callback du middleware d'authentification
        $this->middleware('auth')->except([
            'paystackCallback',
            'stripeCallback',
            'manuelCallback',
            'gatewayCallback',
            'initialiser',
            'statut'
        ]);
    }

    /**
     * Afficher la page de choix de la méthode de paiement
     *
     * @param string $facturationId
     * @return \Illuminate\Http\Response
     */
    public function choisirMethode($facturationId)                                      
    {
        $facturation = Facturation::findOrFail($facturationId);
        
        // Vérifier que l'utilisateur a le droit de payer cette facture
        if (Auth::id() !== $facturation->user_id && 
            Auth::id() !== $facturation->entreprise->user_id && 
            !Auth::user()->hasRole('admin')) {
            abort(403, 'Vous n\'êtes pas autorisé à payer cette facture');
        }

        // Vérifier que la facture n'est pas déjà payée
        if ($facturation->statut_paiement === 'payé') {
            return redirect()->route('facturations.show', $facturation->id)
                ->with('info', 'Cette facture a déjà été payée.');
        }

        // Obtenir les passerelles disponibles
        $passerelles = $this->paiementService->getPasserellesDisponibles(Auth::user());

        return view('paiements.choisir-methode', [
            'facturation' => $facturation,
            'passerelles' => $passerelles
        ]);
    }

    /**
     * Initialiser un paiement
     *
     * @param Request $request
     * @param string $facturationId
     * @return \Illuminate\Http\Response
     */
    public function initialiser(Request $request, $facturationId)
    {
        $facturation = Facturation::findOrFail($facturationId);
        
        Log::info('Initialisation d\'un paiement', [
            'facturation_id' => $facturationId,
            'passerelle' => $request->passerelle ?? 'non spécifiée',
            'methode' => $request->methode ?? 'non spécifiée',
            'montant' => $facturation->montant_ttc ?? 0,
            'devise' => $facturation->devise ?? 'XOF'
        ]);
        
        // Déterminer l'utilisateur initiateur du paiement
        $initiateur = Auth::user();
        
        // Si l'utilisateur n'est pas authentifié, utiliser l'utilisateur en session
        // ou l'utilisateur associé à la facturation
        if (!$initiateur) {
            // Vérifier si nous avons un utilisateur en session
            if (session()->has('user_id')) {
                $initiateur = User::find(session('user_id'));
                Log::info('Utilisateur récupéré depuis la session', [
                    'user_id' => session('user_id'),
                    'email' => $initiateur->email ?? 'non trouvé'
                ]);
            } 
            
            // Si toujours pas d'initiateur, essayer de récupérer l'utilisateur associé à la facturation
            if (!$initiateur) {
                $initiateur = $facturation->user ?? $facturation->entreprise->user;
                Log::info('Utilisateur récupéré depuis la facturation', [
                    'facturation_id' => $facturation->id,
                    'user_id' => $initiateur->id ?? null,
                    'email' => $initiateur->email ?? 'non trouvé'
                ]);
            }
            
            if (!$initiateur) {
                Log::error('Impossible de déterminer l\'utilisateur initiateur du paiement', [
                    'facturation_id' => $facturation->id,
                    'session_user_id' => session('user_id') ?? 'non défini'
                ]);
                return redirect()->route('workflow.index')
                    ->with('error', 'Une erreur est survenue lors de l\'initialisation du paiement.');
            }
        } else {
            Log::info('Utilisateur authentifié pour le paiement', [
                'user_id' => $initiateur->id,
                'email' => $initiateur->email
            ]);
            
            // Si l'utilisateur est authentifié, vérifier qu'il a le droit de payer cette facture
            if (Auth::id() !== $facturation->user_id && 
                Auth::id() !== $facturation->entreprise->user_id && 
                !Auth::user()->hasRole('admin')) {
                Log::warning('Tentative d\'accès non autorisé au paiement', [
                    'user_id' => Auth::id(),
                    'facturation_id' => $facturation->id
                ]);
                abort(403, 'Vous n\'êtes pas autorisé à payer cette facture');
            }
        }

        // Vérifier que la facture n'est pas déjà payée
        if ($facturation->statut_paiement === 'payé') {
            Log::info('Tentative de paiement d\'une facture déjà payée', [
                'facturation_id' => $facturation->id,
                'user_id' => $initiateur->id
            ]);
            return redirect()->route('facturations.show', $facturation->id)
                ->with('info', 'Cette facture a déjà été payée.');
        }

        $request->validate([
            'passerelle' => 'required|string|in:' . implode(',', [
                Paiement::PASSERELLE_PAYSTACK,
                Paiement::PASSERELLE_STRIPE,
                Paiement::PASSERELLE_MANUEL
            ]),
            'methode' => 'required|string|in:' . implode(',', [
                Paiement::METHODE_CARTE,
                Paiement::METHODE_MOBILE_MONEY,
                Paiement::METHODE_VIREMENT,
                Paiement::METHODE_CHEQUE,
                Paiement::METHODE_ESPECES
            ])
        ]);

        try {
            Log::info('Appel du service de paiement pour initialisation', [
                'passerelle' => $request->passerelle,
                'methode' => $request->methode,
                'facturation_id' => $facturation->id,
                'initiateur_id' => $initiateur->id
            ]);
            
            // Initialiser le paiement
            $result = $this->paiementService->initialiserPaiement(
                $facturation,
                $initiateur,
                [
                    'passerelle' => $request->passerelle,
                    'methode' => $request->methode
                ]
            );

            if (!$result['success']) {
                Log::error('Échec de l\'initialisation du paiement', [
                    'message' => $result['message'] ?? 'Erreur inconnue',
                    'facturation_id' => $facturation->id,
                    'passerelle' => $request->passerelle
                ]);
                return redirect()->back()->with('error', $result['message']);
            }

            $paiement = $result['paiement'];
            
            Log::info('Paiement initialisé avec succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'passerelle' => $paiement->passerelle,
                'methode' => $paiement->methode,
                'montant' => $paiement->montant,
                'devise' => $paiement->devise
            ]);

            // Démarrer le workflow pour le paiement
            $workflowResult = $this->workflowService->demarrerWorkflow($paiement);
            
            if (!$workflowResult) {
                Log::error('Échec du démarrage du workflow pour le paiement', [
                    'paiement_id' => $paiement->id,
                    'reference' => $paiement->reference
                ]);
            }

            // Rediriger en fonction de la passerelle
            switch ($request->passerelle) {
                case Paiement::PASSERELLE_PAYSTACK:
                    Log::info('Redirection vers Paystack', [
                        'paiement_id' => $paiement->id,
                        'redirect_url' => $result['redirect_url'] ?? 'non définie'
                    ]);
                    return redirect()->away($result['redirect_url']);
                
                case Paiement::PASSERELLE_STRIPE:
                    Log::info('Redirection vers la page de paiement Stripe', [
                        'paiement_id' => $paiement->id,
                        'client_secret' => isset($result['client_secret']) ? 'défini' : 'non défini'
                    ]);
                    return view('paiements.stripe.checkout', [
                        'paiement' => $paiement,
                        'client_secret' => $result['client_secret'],
                        'public_key' => $result['public_key']
                    ]);
                
                case Paiement::PASSERELLE_MANUEL:
                    Log::info('Redirection vers les instructions de paiement manuel', [
                        'paiement_id' => $paiement->id,
                        'reference' => $paiement->reference
                    ]);
                    return redirect()->route('paiements.manuel.instructions', $paiement->reference);
                
                default:
                    Log::warning('Passerelle de paiement non prise en charge', [
                        'passerelle' => $request->passerelle,
                        'paiement_id' => $paiement->id
                    ]);
                    return redirect()->back()->with('error', 'Passerelle de paiement non prise en charge.');
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'initialisation du paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'facturation_id' => $facturation->id,
                'passerelle' => $request->passerelle ?? 'non spécifiée'
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'initialisation du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le statut d'un paiement
     *
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function statut($reference)
    {
        $paiement = Paiement::where('reference', $reference)->firstOrFail();
        
        // Vérifier si l'utilisateur est authentifié
        if (Auth::check()) {
            // Vérifier que l'utilisateur a le droit de voir ce paiement
            if (Auth::id() !== $paiement->initiateur_id && 
                Auth::id() !== $paiement->facturation->user_id && 
                Auth::id() !== $paiement->entreprise->user_id && 
                !Auth::user()->hasRole('admin')) {
                abort(403, 'Vous n\'êtes pas autorisé à accéder à ce paiement');
            }
        } else {
            // Si l'utilisateur n'est pas authentifié, vérifier s'il a un utilisateur en session
            // et si cet utilisateur est l'initiateur du paiement
            if (session()->has('user_id')) {
                $sessionUserId = session('user_id');
                
                // Vérifier si l'utilisateur en session est l'initiateur du paiement
                if ($sessionUserId != $paiement->initiateur_id && 
                    $sessionUserId != $paiement->facturation->user_id && 
                    $sessionUserId != $paiement->entreprise->user_id) {
                    
                    Log::warning('Tentative d\'accès non autorisé au statut de paiement', [
                        'session_user_id' => $sessionUserId,
                        'paiement_reference' => $reference
                    ]);
                    
                    abort(403, 'Vous n\'êtes pas autorisé à accéder à ce paiement');
                }
            } else {
                // Si aucun utilisateur en session, vérifier si le paiement est lié au workflow en cours
                if (!session()->has('facturation_id') || session('facturation_id') != $paiement->facturation_id) {
                    Log::warning('Tentative d\'accès au statut de paiement sans session utilisateur', [
                        'paiement_reference' => $reference
                    ]);
                    
                    abort(403, 'Vous n\'êtes pas autorisé à accéder à ce paiement');
                }
            }
        }

        // Vérifier le statut du paiement
        $result = $this->paiementService->verifierStatut($paiement);

        Log::info('Statut du paiement vérifié', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut,
            'est_complete' => $paiement->estComplete(),
            'resultat_verification' => $result['success'] ?? false
        ]);

        return view('paiements.statut', [
            'paiement' => $paiement,
            'result' => $result
        ]);
    }

    /**
     * Annuler un paiement
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function annuler(Request $request, $reference)
    {
        $paiement = Paiement::where('reference', $reference)->firstOrFail();
        
        // Vérifier les autorisations
        $userAuthorized = false;
        
        if (Auth::check()) {
            // Utilisateur authentifié
            $userAuthorized = (Auth::id() === $paiement->initiateur_id || Auth::user()->hasRole('admin'));
        } else if (session()->has('user_id')) {
            // Utilisateur en session
            $userAuthorized = (session('user_id') == $paiement->initiateur_id);
        }
        
        if (!$userAuthorized) {
            Log::warning('Tentative non autorisée d\'annulation de paiement', [
                'reference' => $reference,
                'user_id' => Auth::id() ?? session('user_id') ?? 'none'
            ]);
            abort(403, 'Vous n\'êtes pas autorisé à annuler ce paiement');
        }

        // Vérifier que le paiement peut être annulé
        if (!$paiement->estEnAttente() && !$paiement->statut === Paiement::STATUT_TRAITEMENT) {
            return redirect()->back()->with('error', 'Ce paiement ne peut plus être annulé.');
        }

        $request->validate([
            'raison' => 'nullable|string|max:500'
        ]);

        $result = $this->paiementService->annulerPaiement($paiement, $request->raison);

        if ($result) {
            Log::info('Paiement annulé avec succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'raison' => $request->raison ?? 'non spécifiée'
            ]);
            return redirect()->back()->with('success', 'Paiement annulé avec succès.');
        } else {
            Log::error('Échec de l\'annulation du paiement', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'raison' => $request->raison ?? 'non spécifiée'
            ]);
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'annulation du paiement.');
        }
    }

    /**
     * Demander un remboursement
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function demanderRemboursement(Request $request, $reference)
    {
        $paiement = Paiement::where('reference', $reference)->firstOrFail();
        
        // Vérifier les autorisations
        $userAuthorized = false;
        
        if (Auth::check()) {
            // Utilisateur authentifié
            $userAuthorized = (Auth::id() === $paiement->initiateur_id || Auth::user()->hasRole('admin'));
        } else if (session()->has('user_id')) {
            // Utilisateur en session
            $userAuthorized = (session('user_id') == $paiement->initiateur_id);
        }
        
        if (!$userAuthorized) {
            Log::warning('Tentative non autorisée de demande de remboursement', [
                'reference' => $reference,
                'user_id' => Auth::id() ?? session('user_id') ?? 'none'
            ]);
            abort(403, 'Vous n\'êtes pas autorisé à demander un remboursement pour ce paiement');
        }

        // Vérifier que le paiement peut être remboursé
        if (!$paiement->estComplete()) {
            return redirect()->back()->with('error', 'Ce paiement ne peut pas être remboursé.');
        }

        $request->validate([
            'raison' => 'required|string|max:500',
            'montant' => 'nullable|numeric|min:1|max:' . $paiement->montant
        ]);

        $result = $this->paiementService->rembourserPaiement(
            $paiement,
            $request->montant,
            $request->raison
        );

        if ($result) {
            Log::info('Demande de remboursement traitée avec succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'montant' => $request->montant ?? 'non spécifié',
                'raison' => $request->raison
            ]);
            return redirect()->back()->with('success', 'Demande de remboursement traitée avec succès.');
        } else {
            Log::error('Échec de la demande de remboursement', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'montant' => $request->montant ?? 'non spécifié',
                'raison' => $request->raison
            ]);
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la demande de remboursement.');
        }
    }

    /**
     * Callback pour Paystack
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function paystackCallback(Request $request)
    {
        // Cette route est appelée par Paystack après un paiement
        $reference = $request->reference ?? null;
        
        Log::info('Callback Paystack reçu', [
            'reference' => $reference,
            'request_data' => $request->all()
        ]);
        
        if (!$reference) {
            Log::error('Référence de paiement manquante dans le callback Paystack');
            return redirect()->route('workflow.index')->with('error', 'Référence de paiement manquante.');
        }

        $paiement = Paiement::where('reference', $reference)
            ->orWhere('reference_externe', $reference)
            ->first();

        if (!$paiement) {
            Log::error('Paiement non trouvé pour la référence Paystack', [
                'reference' => $reference
            ]);
            return redirect()->route('workflow.index')->with('error', 'Paiement non trouvé.');
        }

        Log::info('Paiement trouvé pour le callback Paystack', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut_actuel' => $paiement->statut
        ]);

        // Vérifier le statut du paiement
        $result = $this->paiementService->verifierStatut($paiement);
        
        Log::info('Statut du paiement Paystack vérifié', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut,
            'est_complete' => $paiement->estComplete(),
            'resultat_verification' => $result['success'] ?? false
        ]);
        
        // Si le paiement n'est pas encore complété mais que la vérification est un succès
        // On valide le paiement pour déclencher le workflow
        if ($result['success'] && !$paiement->estComplete() && $paiement->statut !== Paiement::STATUT_ECHOUE) {
            Log::info('Validation du paiement Paystack après vérification réussie', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            
            $paiement->valider(Auth::user(), 'Paiement validé automatiquement via callback Paystack');
        }
        
        // Si le paiement est validé, rediriger vers la page de succès
        if ($paiement->estComplete()) {
            Log::info('Paiement Paystack complété avec succès, redirection vers la page de succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            return redirect()->route('workflow.success', $paiement->reference);
        }

        // Sinon, rediriger vers la page de statut
        Log::info('Paiement Paystack non complété, redirection vers la page de statut', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut
        ]);
        return redirect()->route('paiements.statut', $paiement->reference);
    }

    /**
     * Callback pour Stripe
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function stripeCallback(Request $request)
    {
        // Cette route est appelée par le frontend après un paiement Stripe
        $paymentIntent = $request->payment_intent ?? null;
        
        Log::info('Callback Stripe reçu', [
            'payment_intent' => $paymentIntent,
            'request_data' => $request->all()
        ]);
        
        if (!$paymentIntent) {
            Log::error('Référence de paiement manquante dans le callback Stripe');
            return redirect()->route('workflow.index')->with('error', 'Référence de paiement manquante.');
        }

        $paiement = Paiement::where('reference_externe', $paymentIntent)->first();

        if (!$paiement) {
            Log::error('Paiement non trouvé pour la référence Stripe', [
                'payment_intent' => $paymentIntent
            ]);
            return redirect()->route('workflow.index')->with('error', 'Paiement non trouvé.');
        }

        Log::info('Paiement trouvé pour le callback Stripe', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut_actuel' => $paiement->statut
        ]);

        // Vérifier le statut du paiement
        $result = $this->paiementService->verifierStatut($paiement);
        
        Log::info('Statut du paiement Stripe vérifié', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut,
            'est_complete' => $paiement->estComplete(),
            'resultat_verification' => $result['success'] ?? false
        ]);
        
        // Si le paiement n'est pas encore complété mais que la vérification est un succès
        // On valide le paiement pour déclencher le workflow
        if ($result['success'] && !$paiement->estComplete() && $paiement->statut !== Paiement::STATUT_ECHOUE) {
            Log::info('Validation du paiement Stripe après vérification réussie', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            
            $paiement->valider(null, 'Paiement validé automatiquement via callback Stripe');
        }
        
        // Si le paiement est validé, rediriger vers la page de succès
        if ($paiement->estComplete()) {
            Log::info('Paiement Stripe complété avec succès, redirection vers la page de succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            return redirect()->route('workflow.success', $paiement->reference);
        }

        // Sinon, rediriger vers la page de statut
        Log::info('Paiement Stripe non complété, redirection vers la page de statut', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut
        ]);
        return redirect()->route('paiements.statut', $paiement->reference);
    }

    /**
     * Callback générique pour les passerelles de paiement
     *
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\Response
     */
    public function gatewayCallback(Request $request, $gateway)
    {
        Log::info('Callback reçu pour la passerelle: ' . $gateway, [
            'request' => $request->all(),
            'gateway' => $gateway
        ]);
        
        // Récupérer la référence du paiement depuis les paramètres de la requête
        $reference = $request->reference ?? $request->payment_reference ?? $request->payment_intent ?? null;
        
        if (!$reference) {
            Log::error('Référence de paiement manquante dans le callback', [
                'gateway' => $gateway,
                'request' => $request->all()
            ]);
            return redirect()->route('workflow.index')->with('error', 'Référence de paiement manquante.');
        }

        // Rechercher le paiement par référence ou référence externe
        $paiement = Paiement::where('reference', $reference)
            ->orWhere('reference_externe', $reference)
            ->first();

        if (!$paiement) {
            Log::error('Paiement non trouvé pour la référence: ' . $reference, [
                'gateway' => $gateway,
                'reference' => $reference
            ]);
            return redirect()->route('workflow.index')->with('error', 'Paiement non trouvé.');
        }

        Log::info('Paiement trouvé pour le callback', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'passerelle' => $paiement->passerelle,
            'statut_actuel' => $paiement->statut,
            'gateway_callback' => $gateway
        ]);

        // Vérifier le statut du paiement
        $result = $this->paiementService->verifierStatut($paiement);
        
        Log::info('Statut du paiement vérifié', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut,
            'est_complete' => $paiement->estComplete(),
            'resultat_verification' => $result['success'] ?? false,
            'gateway' => $gateway
        ]);
        
        // Si le paiement n'est pas encore complété mais que la vérification est un succès
        // On valide le paiement pour déclencher le workflow
        if ($result['success'] && !$paiement->estComplete() && $paiement->statut !== Paiement::STATUT_ECHOUE) {
            Log::info('Validation du paiement après vérification réussie', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'gateway' => $gateway
            ]);
            
            $paiement->valider(null, 'Paiement validé automatiquement via callback ' . $gateway);
        }
        
        // Si le paiement est validé, rediriger vers la page de succès
        if ($paiement->estComplete()) {
            Log::info('Paiement complété avec succès, redirection vers la page de succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference,
                'gateway' => $gateway
            ]);
            return redirect()->route('workflow.success', $paiement->reference);
        }

        // Sinon, rediriger vers la page de statut
        Log::info('Paiement non complété, redirection vers la page de statut', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut,
            'gateway' => $gateway
        ]);
        return redirect()->route('paiements.statut', $paiement->reference);
    }

    /**
     * Callback pour les paiements manuels
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function manuelCallback(Request $request)
    {
        // Cette route est appelée après un paiement manuel
        $reference = $request->reference ?? null;
        
        Log::info('Callback de paiement manuel reçu', [
            'reference' => $reference,
            'request_data' => $request->all()
        ]);
        
        if (!$reference) {
            Log::error('Référence de paiement manquante dans le callback manuel');
            return redirect()->route('workflow.index')->with('error', 'Référence de paiement manquante.');
        }

        $paiement = Paiement::where('reference', $reference)->first();

        if (!$paiement) {
            Log::error('Paiement non trouvé pour la référence de paiement manuel', [
                'reference' => $reference
            ]);
            return redirect()->route('workflow.index')->with('error', 'Paiement non trouvé.');
        }

        Log::info('Paiement trouvé pour le callback manuel', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut_actuel' => $paiement->statut
        ]);

        // Vérifier le statut du paiement
        $result = $this->paiementService->verifierStatut($paiement);
        
        Log::info('Statut du paiement manuel vérifié', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut,
            'est_complete' => $paiement->estComplete(),
            'resultat_verification' => $result['success'] ?? false
        ]);
        
        // Si le paiement n'est pas encore complété mais que la vérification est un succès
        // On valide le paiement pour déclencher le workflow
        if ($result['success'] && !$paiement->estComplete() && $paiement->statut !== Paiement::STATUT_ECHOUE) {
            Log::info('Validation du paiement manuel après vérification réussie', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            
            $paiement->valider(Auth::user(), 'Paiement validé automatiquement via callback manuel');
        }
        
        // Si le paiement est validé, rediriger vers la page de succès
        if ($paiement->estComplete()) {
            Log::info('Paiement manuel complété avec succès, redirection vers la page de succès', [
                'paiement_id' => $paiement->id,
                'reference' => $paiement->reference
            ]);
            return redirect()->route('workflow.success', $paiement->reference);
        }

        // Sinon, rediriger vers la page de statut
        Log::info('Paiement manuel non complété, redirection vers la page de statut', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference,
            'statut' => $paiement->statut
        ]);
        return redirect()->route('paiements.statut', $paiement->reference);
    }
}
