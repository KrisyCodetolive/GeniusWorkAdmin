<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\WorkflowService;

class WorkflowController extends Controller
{
    protected $workflowService;

    /**
     * Constructeur avec injection du service
     */
    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
        
        // Appliquer le middleware auth à toutes les méthodes sauf celles du workflow d'inscription
        $this->middleware('auth')->except([
            'pricingGotoWorkflow',     // Goto Workflow by Pricing
            'index',                   // Page d'accueil du workflow
            'createUserAccount',       // Étape 1: Création du compte utilisateur (formulaire)
            'storeUserAccount',        // Étape 1: Enregistrement du compte utilisateur
            'createCompanyAccount',    // Étape 2: Création du compte entreprise (formulaire)
            'storeCompanyAccount',     // Étape 2: Enregistrement du compte entreprise
            'selectSubscription',      // Étape 3: Sélection de l'abonnement (formulaire)
            'storeSubscription',       // Étape 3: Enregistrement de l'abonnement
            'showPayment',             // Étape 4: Affichage de la page de paiement
            'processPayment',          // Étape 4: Traitement du paiement
            'showSuccess',             // Page de succès après paiement
            'downloadInvoice',         // Téléchargement de la facture
            'createStripeSession',     // Création d'une session Stripe
            'handlePaymentCallback',   // Gestion des callbacks de paiement
            'handlePaymentSuccess',    // Gestion du succès du paiement
            'handlePaymentCancel'      // Gestion de l'annulation du paiement
        ]);
    }

    // Goto Workflow by Pricing
    public function pricingGotoWorkflow(Request $request){

    
        // Stock in Session UserNumber 
        $request->session()->put('user_number', $request->UserNumber);

        return redirect()->route('workflow.index');
    }

    /**
     * Afficher la page d'accueil du workflow
     */
    public function index()
    {
        return view('workflow.index');
    }

    /**
     * Afficher l'étape 1 : Création du compte utilisateur
     */
    public function createUserAccount()
    {
        return view('workflow.user-account');
    }

    /**
     * Traiter les données de l'étape 1
     */
    public function storeUserAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Stocker les données dans la session pour l'étape suivante
        $request->session()->put('user_account', [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => $request->password,
        ]);

        // Créer l'utilisateur dans la base de données si l'option est activée
        if (config('workflow.save_data_immediately', false)) {
            try {
                $user = $this->workflowService->createUserAccount($request->session()->get('user_account'));
                $request->session()->put('user_id', $user->id);
                Log::info('User account created in database', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('Error creating user account', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('workflow.company');
    }

    /**
     * Afficher l'étape 2 : Création du compte entreprise
     */
    public function createCompanyAccount()
    {
        // Vérifier si l'étape 1 a été complétée
        if (!session()->has('user_account')) {
            return redirect()->route('workflow.user');
        }

        return view('workflow.company-account');
    }

    /**
     * Traiter les données de l'étape 2
     */
    public function storeCompanyAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'company_size' => 'required|integer|min:1',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Stocker les données dans la session pour l'étape suivante
        $request->session()->put('company_account', [
            'company_name' => $request->company_name,
            'industry' => $request->industry,
            'address' => $request->address,
            'company_size' => $request->company_size,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
        ]);

        // Créer l'entreprise dans la base de données si l'option est activée
        if (config('workflow.save_data_immediately', false) && session()->has('user_id')) {
            try {
                $user = \App\Models\User::find(session('user_id'));
                if ($user) {
                    $entreprise = $this->workflowService->createCompanyAccount(
                        $request->session()->get('company_account'),
                        $user
                    );
                    $request->session()->put('entreprise_id', $entreprise->id);
                    Log::info('Company account created in database', ['entreprise_id' => $entreprise->id]);
                }
            } catch (\Exception $e) {
                Log::error('Error creating company account', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('workflow.subscription');
    }

    /**
     * Afficher l'étape 3 : Sélection de l'abonnement
     */
    public function selectSubscription()
    {
        // Vérifier si l'étape 2 a été complétée
        if (!session()->has('company_account')) {
            return redirect()->route('workflow.company');
        }

        $companySize = session('company_account.company_size');
        
        // Déterminer le forfait et le coût fixe
        $forfait = '';
        $coutFixe = 0;
        
        if ($companySize >= 1 && $companySize <= 50) {
            $forfait = 'Starter';
            $coutFixe = 10000;
        } elseif ($companySize > 50 && $companySize <= 100) {
            $forfait = 'Side Business';
            $coutFixe = 15000;
        } elseif ($companySize > 100) {
            $forfait = 'Enterprise';
            $coutFixe = 30000;
        }
        
        // Calculer le coût des utilisateurs
        $coutUtilisateurs = $companySize * 100;
        
        // Calculer le coût total
        $coutTotal = $coutFixe + $coutUtilisateurs;

        return view('workflow.subscription', [
            'forfait' => $forfait,
            'coutFixe' => $coutFixe,
            'coutUtilisateurs' => $coutUtilisateurs,
            'coutTotal' => $coutTotal,
            'companySize' => $companySize,
        ]);
    }

    /**
     * Traiter les données de l'étape 3
     */
    public function storeSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_plan' => 'required|string',
            'base_cost' => 'required|numeric',
            'user_cost' => 'required|numeric',
            'total_cost' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Stocker les données dans la session pour l'étape suivante
        $request->session()->put('subscription', [
            'subscription_plan' => $request->subscription_plan,
            'base_cost' => $request->base_cost,
            'user_cost' => $request->user_cost,
            'total_cost' => $request->total_cost,
        ]);

        // Créer l'abonnement dans la base de données si l'option est activée
        if (config('workflow.save_data_immediately', false) && session()->has('entreprise_id')) {
            try {
                $entreprise = \App\Models\Entreprise::find(session('entreprise_id'));
                if ($entreprise) {
                    $abonnement = $this->workflowService->createSubscription(
                        $request->session()->get('subscription'),
                        $entreprise
                    );
                    $request->session()->put('abonnement_id', $abonnement->id);
                    Log::info('Subscription created in database', ['abonnement_id' => $abonnement->id]);
                }
            } catch (\Exception $e) {
                Log::error('Error creating subscription', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('workflow.payment');
    }

    /**
     * Afficher l'étape 4 : Paiement
     */
    public function showPayment()
    {
        // Vérifier si l'utilisateur a complété l'étape précédente
        if (!session()->has('subscription')) {
            return redirect()->route('workflow.subscription')
                ->with('error', 'Veuillez d\'abord sélectionner un abonnement.');
        }

        // Si nous n'avons pas encore de facturation_id en session, créer une facturation temporaire
        if (!session()->has('facturation_id')) {
            try {
                // Vérifier si nous avons déjà créé un utilisateur et une entreprise
                if (session()->has('user_id') && session()->has('entreprise_id') && session()->has('abonnement_id')) {
                    // Récupérer l'abonnement existant
                    $abonnement = \App\Models\Abonnement::find(session('abonnement_id'));
                    
                    if ($abonnement) {
                        // Créer une facturation pour l'abonnement existant
                        $facturation = $this->workflowService->processPayment(
                            [
                                'payment_method' => 'pending',
                                'payment_date' => now(),
                                'invoice_number' => 'GW-TEMP-' . now()->format('YmdHis'),
                            ],
                            $abonnement
                        );
                        
                        if ($facturation) {
                            session()->put('facturation_id', $facturation->id);
                            Log::info('Facturation temporaire créée pour le paiement', ['facturation_id' => $facturation->id]);
                        }
                    } else {
                        Log::error('Abonnement non trouvé malgré un ID en session', ['abonnement_id' => session('abonnement_id')]);
                        session()->forget('abonnement_id');
                    }
                } else if (config('workflow.save_data_immediately', false)) {
                    // Créer l'utilisateur s'il n'existe pas déjà
                    $user = $this->workflowService->createUserAccount(session('user_account'));
                    session()->put('user_id', $user->id);
                    
                    // Créer l'entreprise
                    $entreprise = $this->workflowService->createCompanyAccount(session('company_account'), $user);
                    session()->put('entreprise_id', $entreprise->id);
                    
                    try {
                        // Créer l'abonnement avec gestion d'erreur
                        $abonnement = $this->workflowService->createSubscription(session('subscription'), $entreprise);
                        
                        if ($abonnement) {
                            session()->put('abonnement_id', $abonnement->id);
                            
                            // Créer la facturation
                            $facturation = $this->workflowService->processPayment(
                                [
                                    'payment_method' => 'pending',
                                    'payment_date' => now(),
                                    'invoice_number' => 'GW-TEMP-' . now()->format('YmdHis'),
                                ],
                                $abonnement
                            );
                            
                            if ($facturation) {
                                session()->put('facturation_id', $facturation->id);
                                Log::info('Workflow partiel exécuté et facturation temporaire créée', ['facturation_id' => $facturation->id]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de la création de l\'abonnement', ['error' => $e->getMessage()]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création de la facturation temporaire', ['error' => $e->getMessage()]);
            }
        }

        // Vérifier si nous avons bien un facturation_id maintenant
        $facturation_id = session('facturation_id');
        Log::info('Affichage de la page de paiement', ['facturation_id' => $facturation_id]);

        // Si nous n'avons pas de facturation_id, rediriger vers l'étape précédente
        if (!$facturation_id) {
            return redirect()->route('workflow.subscription')
                ->with('error', 'Une erreur est survenue lors de la préparation du paiement. Veuillez réessayer.');
        }

        return view('workflow.payment', [
            'subscription' => session('subscription'),
            'company' => session('company_account'),
            'user' => session('user_account'),
            'facturation_id' => $facturation_id,
        ]);
    }

    /**
     * Traiter le paiement et générer une facture
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:card,mobile_money,bank_transfer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Stocker les données dans la session
        $paymentData = [
            'payment_method' => $request->payment_method,
            'payment_date' => now(),
            'invoice_number' => 'GW-' . now()->format('YmdHis'),
        ];
        
        $request->session()->put('payment', $paymentData);

        // Traiter le paiement dans la base de données
        if (config('workflow.save_data_immediately', false) && session()->has('abonnement_id')) {
            try {
                $abonnement = \App\Models\Abonnement::find(session('abonnement_id'));
                if ($abonnement) {
                    $facturation = $this->workflowService->processPayment(
                        $paymentData,
                        $abonnement
                    );
                    $request->session()->put('facturation_id', $facturation->id);
                    Log::info('Payment processed in database', ['facturation_id' => $facturation->id]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing payment', ['error' => $e->getMessage()]);
            }
        } else if (!config('workflow.save_data_immediately', false)) {
            // Exécuter le workflow complet si les données n'ont pas été enregistrées précédemment
            try {
                $result = $this->workflowService->executeCompleteWorkflow(
                    session('user_account'),
                    session('company_account'),
                    session('subscription'),
                    $paymentData
                );
                
                // Stocker les IDs dans la session
                $request->session()->put('user_id', $result['user']->id);
                $request->session()->put('entreprise_id', $result['entreprise']->id);
                $request->session()->put('abonnement_id', $result['abonnement']->id);
                $request->session()->put('facturation_id', $result['facturation']->id);
                
                Log::info('Complete workflow executed successfully', [
                    'user_id' => $result['user']->id,
                    'entreprise_id' => $result['entreprise']->id
                ]);
            } catch (\Exception $e) {
                Log::error('Error executing complete workflow', ['error' => $e->getMessage()]);
            }
        }

        // Générer une facture PDF
        $pdf = PDF::loadView('pdf.invoice', [
            'subscription' => session('subscription'),
            'company' => session('company_account'),
            'user' => session('user_account'),
            'payment' => session('payment'),
        ]);

        // Rediriger vers l'étape 5 : Tableau de bord d'administration
        return redirect()->route('workflow.dashboard');
    }

    /**
     * Afficher la page d'administration après un paiement réussi
     */
    public function showDashboard()
    {
        // Vérifier si l'inscription est complète
        if (!session()->has('payment')) {
            return redirect()->route('workflow.index');
        }

        // Récupérer les données de la base de données si disponibles
        $userData = null;
        $entrepriseData = null;
        $abonnementData = null;
        $facturationData = null;

        if (session()->has('user_id')) {
            $userData = \App\Models\User::find(session('user_id'));
        }
        
        if (session()->has('entreprise_id')) {
            $entrepriseData = \App\Models\Entreprise::find(session('entreprise_id'));
        }
        
        if (session()->has('abonnement_id')) {
            $abonnementData = \App\Models\Abonnement::find(session('abonnement_id'));
        }
        
        if (session()->has('facturation_id')) {
            $facturationData = \App\Models\Facturation::find(session('facturation_id'));
        }

        return view('workflow.dashboard', [
            'subscription' => session('subscription'),
            'company' => session('company_account'),
            'user' => session('user_account'),
            'payment' => session('payment'),
            'userData' => $userData,
            'entrepriseData' => $entrepriseData,
            'abonnementData' => $abonnementData,
            'facturationData' => $facturationData,
        ]);
    }

    /**
     * Télécharger la facture PDF
     */
    public function downloadInvoice()
    {
        // Vérifier si l'inscription est complète
        if (!session()->has('payment')) {
            return redirect()->route('workflow.index');
        }

        $pdf = PDF::loadView('pdf.invoice', [
            'subscription' => session('subscription'),
            'company' => session('company_account'),
            'user' => session('user_account'),
            'payment' => session('payment'),
        ]);

        return $pdf->download('GENIUS WORK-Invoice.pdf');
    }

    /**
     * Afficher la page de succès après un paiement validé
     * 
     * Cette méthode est accessible sans authentification.
     * 
     * @param string $paiementReference
     * @return \Illuminate\Http\Response
     */
    public function showSuccess($paiementReference)
    {
        // Récupérer le paiement
        $paiement = \App\Models\Paiement::where('reference', $paiementReference)->firstOrFail();
        
        // Vérifier que le paiement est bien validé
        if (!$paiement->estComplete()) {
            return redirect()->route('paiements.statut', $paiement->reference)
                ->with('error', 'Le paiement n\'est pas encore validé.');
        }
        
        // Récupérer les données associées
        $facturation = $paiement->facturation;
        $abonnement = $paiement->abonnement ?? $facturation->abonnement;
        $entreprise = $paiement->entreprise ?? $abonnement->entreprise;
        
        // Récupérer l'utilisateur (administrateur de l'entreprise)
        $user = auth()->user();
        
        // Si l'utilisateur n'est pas disponible dans la session, essayer de le récupérer depuis l'abonnement
        if (!$user && $abonnement && $abonnement->user_id) {
            $user = \App\Models\User::find($abonnement->user_id);
        }
        
        // Si l'utilisateur n'est toujours pas disponible, essayer de récupérer l'administrateur de l'entreprise
        if (!$user && $entreprise) {
            // Utiliser la relation users() pour récupérer l'administrateur
            $user = $entreprise->users()->where('role', 'admin')->first();
            
            // Si aucun utilisateur avec le rôle admin n'est trouvé, prendre le premier utilisateur
            if (!$user) {
                $user = $entreprise->users()->first();
            }
        }
        
        // Journaliser les informations de récupération de l'utilisateur
        \Illuminate\Support\Facades\Log::info('Tentative de récupération de l\'utilisateur pour l\'envoi d\'emails', [
            'auth_user' => auth()->check() ? auth()->id() : null,
            'abonnement_user_id' => $abonnement->user_id ?? null,
            'entreprise_id' => $entreprise ? $entreprise->id : null,
            'user_trouve' => $user ? $user->id : null
        ]);
        
        // Envoyer les emails de bienvenue via le service d'email seulement si l'utilisateur est disponible
        if ($user) {
            $emailService = app(\App\Services\Mail\EmailServiceInterface::class);
            $emailsSent = $this->sendWelcomeEmails($emailService, $user, $entreprise, $abonnement);
            
            // Envoyer les SMS de bienvenue
            $smsService = app(\App\Services\SMS\SMSServiceInterface::class);
            $smsSent = $this->sendWelcomeSMS($smsService, $user, $entreprise, $abonnement);
            
            // Ajouter un message flash pour notifier l'utilisateur
            $message = '';
            
            // Message pour les emails
            if ($emailsSent['user'] || $emailsSent['company']) {
                $message = 'Un email de bienvenue a été envoyé à ';
                
                if ($emailsSent['user'] && $emailsSent['company']) {
                    $message .= 'votre adresse email personnelle et à l\'adresse email de votre entreprise.';
                } elseif ($emailsSent['user']) {
                    $message .= 'votre adresse email personnelle.';
                } else {
                    $message .= 'l\'adresse email de votre entreprise.';
                }
            }
            
            // Message pour les SMS
            if ($smsSent['user'] || $smsSent['company']) {
                if (!empty($message)) {
                    $message .= ' De plus, un SMS de bienvenue a été envoyé à ';
                } else {
                    $message = 'Un SMS de bienvenue a été envoyé à ';
                }
                
                if ($smsSent['user'] && $smsSent['company']) {
                    $message .= 'votre numéro de téléphone personnel et au numéro de téléphone de votre entreprise.';
                } elseif ($smsSent['user']) {
                    $message .= 'votre numéro de téléphone personnel.';
                } else {
                    $message .= 'au numéro de téléphone de votre entreprise.';
                }
            }
            
            if (!empty($message)) {
                session()->flash('success', $message);
            }
        } else {
            \Illuminate\Support\Facades\Log::warning('Impossible d\'envoyer les emails de bienvenue : utilisateur non trouvé', [
                'paiement_reference' => $paiement->reference,
                'entreprise_id' => $entreprise ? $entreprise->id : null,
                'abonnement_id' => $abonnement ? $abonnement->id : null
            ]);
        }
        
        return view('workflow.success', [
            'paiement' => $paiement,
            'facturation' => $facturation,
            'abonnement' => $abonnement,
            'entreprise' => $entreprise
        ]);
    }
    
    /**
     * Envoyer les emails de bienvenue à l'utilisateur et à l'entreprise
     *
     * @param \App\Services\Mail\EmailServiceInterface $emailService
     * @param \App\Models\User|null $user
     * @param \App\Models\Entreprise|null $entreprise
     * @param \App\Models\Abonnement|null $abonnement
     * @return array Statuts d'envoi des emails [user => bool, company => bool]
     */
    private function sendWelcomeEmails($emailService, $user, $entreprise, $abonnement)
    {
        $emailStatus = [
            'user' => false,
            'company' => false
        ];
        
        // Vérifier que tous les paramètres nécessaires sont présents
        if (!$user || !$entreprise || !$abonnement) {
            \Illuminate\Support\Facades\Log::warning('Impossible d\'envoyer les emails de bienvenue : paramètres manquants', [
                'user_present' => (bool)$user,
                'entreprise_present' => (bool)$entreprise,
                'abonnement_present' => (bool)$abonnement
            ]);
            return $emailStatus;
        }
        
        try {
            // Envoyer l'email de bienvenue à l'utilisateur
            $emailStatus['user'] = $emailService->sendUserWelcome($user, $entreprise, $abonnement);
            
            // Journaliser le résultat de l'envoi de l'email utilisateur
            \Illuminate\Support\Facades\Log::info('Email de bienvenue utilisateur', [
                'user_id' => $user->id,
                'email' => $user->email,
                'statut' => $emailStatus['user'] ? 'envoyé' : 'échec'
            ]);
            
            // Envoyer l'email de bienvenue à l'entreprise
            $emailStatus['company'] = $emailService->sendCompanyWelcome($user, $entreprise, $abonnement);
            
            // Journaliser le résultat de l'envoi de l'email entreprise
            \Illuminate\Support\Facades\Log::info('Email de bienvenue entreprise', [
                'entreprise_id' => $entreprise->id,
                'email' => $entreprise->email,
                'statut' => $emailStatus['company'] ? 'envoyé' : 'échec'
            ]);
            
            // Les logs sont déjà gérés par le service EmailLogger
        } catch (\Exception $e) {
            // Journaliser l'erreur
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des emails de bienvenue', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Créer une instance du logger d'emails pour enregistrer l'erreur
            $emailLogger = new \App\Services\Mail\EmailLogger();
            $emailLogger->emailError($e, [
                'user_id' => $user->id,
                'entreprise_id' => $entreprise->id,
                'abonnement_id' => $abonnement->id,
                'context' => 'workflow_success'
            ]);
        }
        
        return $emailStatus;
    }

    /**
     * Envoyer les SMS de bienvenue à l'utilisateur et à l'entreprise
     *
     * @param \App\Services\SMS\SMSServiceInterface $smsService
     * @param \App\Models\User|null $user
     * @param \App\Models\Entreprise|null $entreprise
     * @param \App\Models\Abonnement|null $abonnement
     * @return array Statuts d'envoi des SMS [user => bool, company => bool]
     */
    private function sendWelcomeSMS($smsService, $user, $entreprise, $abonnement)
    {
        $smsStatus = [
            'user' => false,
            'company' => false
        ];
        
        // Vérifier que tous les paramètres nécessaires sont présents
        if (!$user || !$entreprise || !$abonnement) {
            \Illuminate\Support\Facades\Log::warning('Impossible d\'envoyer les SMS de bienvenue : paramètres manquants', [
                'user_present' => (bool)$user,
                'entreprise_present' => (bool)$entreprise,
                'abonnement_present' => (bool)$abonnement
            ]);
            
            // Si l'utilisateur est manquant mais que l'entreprise est présente, essayer de récupérer l'administrateur
            if (!$user && $entreprise) {
                try {
                    // Récupérer l'administrateur de l'entreprise
                    $admin = $entreprise->users()->whereHas('roles', function($query) {
                        $query->where('name', 'admin');
                    })->first();
                    
                    if ($admin) {
                        $user = $admin;
                        \Illuminate\Support\Facades\Log::info('Utilisateur administrateur récupéré pour l\'envoi des SMS', [
                            'user_id' => $user->id,
                            'entreprise_id' => $entreprise->id
                        ]);
                    } else {
                        // Récupérer le premier utilisateur de l'entreprise si aucun admin n'est trouvé
                        $user = $entreprise->users()->first();
                        if ($user) {
                            \Illuminate\Support\Facades\Log::info('Premier utilisateur récupéré pour l\'envoi des SMS', [
                                'user_id' => $user->id,
                                'entreprise_id' => $entreprise->id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération de l\'utilisateur pour l\'envoi des SMS', [
                        'message' => $e->getMessage(),
                        'entreprise_id' => $entreprise->id ?? 'unknown'
                    ]);
                }
            }
            
            // Si toujours des paramètres manquants après tentative de récupération
            if (!$user || !$entreprise || !$abonnement) {
                return $smsStatus;
            }
        }
        
        try {
            // Envoyer le SMS de bienvenue à l'utilisateur (le service vérifiera la présence du numéro)
            $userSmsResult = $smsService->sendUserWelcomeSMS($user, $entreprise, $abonnement);
            $smsStatus['user'] = $userSmsResult['success'] ?? false;
            
            // Récupérer le numéro de téléphone pour le log (utiliser la même logique que dans le service)
            $userPhone = $user->telephone ?? $user->phone_number ?? $user->phone ?? 'non disponible';
            
            // Journaliser le résultat de l'envoi du SMS utilisateur
            \Illuminate\Support\Facades\Log::info('SMS de bienvenue utilisateur', [
                'user_id' => $user->id,
                'phone' => $userPhone,
                'statut' => $smsStatus['user'] ? 'envoyé' : 'échec',
                'details' => $userSmsResult
            ]);
            
            // Envoyer le SMS de bienvenue à l'entreprise (le service vérifiera la présence du numéro)
            $companySmsResult = $smsService->sendCompanyWelcomeSMS($user, $entreprise, $abonnement);
            $smsStatus['company'] = $companySmsResult['success'] ?? false;
            
            // Récupérer le numéro de téléphone pour le log (utiliser la même logique que dans le service)
            $companyPhone = $entreprise->telephone ?? $entreprise->phone_number ?? $entreprise->phone ?? 'non disponible';
            
            // Journaliser le résultat de l'envoi du SMS entreprise
            \Illuminate\Support\Facades\Log::info('SMS de bienvenue entreprise', [
                'entreprise_id' => $entreprise->id,
                'phone' => $companyPhone,
                'statut' => $smsStatus['company'] ? 'envoyé' : 'échec',
                'details' => $companySmsResult
            ]);
            
        } catch (\Exception $e) {
            // Journaliser l'erreur
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi des SMS de bienvenue', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? 'unknown',
                'entreprise_id' => $entreprise->id ?? 'unknown'
            ]);
        }
        
        return $smsStatus;
    }

    /**
     * Gérer le callback de paiement Paystack
     */
    public function handlePaymentCallback(Request $request)
    {
        // Vérifier si le paiement a réussi
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('workflow.payment')
                ->with('error', 'Référence de paiement manquante');
        }

        try {
            // Vérifier le statut du paiement auprès de Paystack
            $paystack = new \Yabacon\Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->verify([
                'reference' => $reference
            ]);

            if ('success' === $tranx->data->status) {
                // Paiement réussi, traiter la commande
                $paymentData = [
                    'payment_method' => 'card', // Par défaut, peut être mis à jour
                    'payment_gateway' => 'paystack',
                    'payment_date' => now(),
                    'invoice_number' => 'GW-' . now()->format('YmdHis'),
                    'reference' => $reference,
                    'amount' => $tranx->data->amount / 100, // Convertir de kobo à la devise
                    'status' => 'paid',
                ];
                
                // Stocker les données dans la session
                session()->put('payment', $paymentData);
                
                // Traiter le paiement dans la base de données
                if (config('workflow.save_data_immediately', false) && session()->has('abonnement_id')) {
                    try {
                        $abonnement = \App\Models\Abonnement::find(session('abonnement_id'));
                        if ($abonnement) {
                            $facturation = $this->workflowService->processPayment(
                                $paymentData,
                                $abonnement
                            );
                            session()->put('facturation_id', $facturation->id);
                            Log::info('Payment processed in database', ['facturation_id' => $facturation->id]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing payment', ['error' => $e->getMessage()]);
                    }
                } else if (!config('workflow.save_data_immediately', false)) {
                    // Exécuter le workflow complet si les données n'ont pas été enregistrées précédemment
                    try {
                        $result = $this->workflowService->executeCompleteWorkflow(
                            session('user_account'),
                            session('company_account'),
                            session('subscription'),
                            $paymentData
                        );
                        
                        // Stocker les IDs dans la session
                        session()->put('user_id', $result['user']->id);
                        session()->put('entreprise_id', $result['entreprise']->id);
                        session()->put('abonnement_id', $result['abonnement']->id);
                        session()->put('facturation_id', $result['facturation']->id);
                        
                        Log::info('Complete workflow executed successfully', [
                            'user_id' => $result['user']->id,
                            'entreprise_id' => $result['entreprise']->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error executing complete workflow', ['error' => $e->getMessage()]);
                    }
                }

                // Rediriger vers la page de succès
                return redirect()->route('workflow.dashboard')
                    ->with('success', 'Paiement effectué avec succès !');
            }

            // Paiement échoué
            return redirect()->route('workflow.payment')
                ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du paiement Paystack', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('workflow.payment')
                ->with('error', 'Une erreur est survenue lors de la vérification du paiement. Veuillez contacter le support.');
        }
    }

    /**
     * Gérer le succès du paiement Stripe
     */
    public function handlePaymentSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        if (!$sessionId) {
            return redirect()->route('workflow.payment')
                ->with('error', 'ID de session manquant');
        }

        try {
            // Initialiser Stripe avec la clé secrète
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // Récupérer la session
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                // Paiement réussi, traiter la commande
                $paymentData = [
                    'payment_method' => $session->metadata->payment_method ?? 'card',
                    'payment_gateway' => 'stripe',
                    'payment_date' => now(),
                    'invoice_number' => 'GW-' . now()->format('YmdHis'),
                    'reference' => $session->client_reference_id,
                    'amount' => $session->amount_total / 100, // Convertir de centimes à la devise
                    'status' => 'paid',
                ];
                
                // Stocker les données dans la session
                session()->put('payment', $paymentData);
                
                // Traiter le paiement dans la base de données
                if (config('workflow.save_data_immediately', false) && session()->has('abonnement_id')) {
                    try {
                        $abonnement = \App\Models\Abonnement::find(session('abonnement_id'));
                        if ($abonnement) {
                            $facturation = $this->workflowService->processPayment(
                                $paymentData,
                                $abonnement
                            );
                            session()->put('facturation_id', $facturation->id);
                            Log::info('Payment processed in database', ['facturation_id' => $facturation->id]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing payment', ['error' => $e->getMessage()]);
                    }
                } else if (!config('workflow.save_data_immediately', false)) {
                    // Exécuter le workflow complet si les données n'ont pas été enregistrées précédemment
                    try {
                        $result = $this->workflowService->executeCompleteWorkflow(
                            session('user_account'),
                            session('company_account'),
                            session('subscription'),
                            $paymentData
                        );
                        
                        // Stocker les IDs dans la session
                        session()->put('user_id', $result['user']->id);
                        session()->put('entreprise_id', $result['entreprise']->id);
                        session()->put('abonnement_id', $result['abonnement']->id);
                        session()->put('facturation_id', $result['facturation']->id);
                        
                        Log::info('Complete workflow executed successfully', [
                            'user_id' => $result['user']->id,
                            'entreprise_id' => $result['entreprise']->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error executing complete workflow', ['error' => $e->getMessage()]);
                    }
                }

                // Rediriger vers la page de succès
                return redirect()->route('workflow.dashboard')
                    ->with('success', 'Paiement effectué avec succès !');
            }

            // Paiement échoué
            return redirect()->route('workflow.payment')
                ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du paiement Stripe', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('workflow.payment')
                ->with('error', 'Une erreur est survenue lors de la vérification du paiement. Veuillez contacter le support.');
        }
    }

    /**
     * Créer une session de paiement Stripe
     */
    public function createStripeSession(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'payment_method' => 'required|string',
            'reference' => 'required|string',
            'customer_email' => 'required|email',
            'success_url' => 'required|string',
            'cancel_url' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Initialiser Stripe avec la clé secrète
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // Créer une session de paiement
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $request->currency,
                        'product_data' => [
                            'name' => 'Abonnement GENIUS WORK',
                            'description' => 'Paiement de l\'abonnement GENIUS WORK',
                        ],
                        'unit_amount' => $request->amount * 100, // Montant en centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $request->success_url,
                'cancel_url' => $request->cancel_url,
                'client_reference_id' => $request->reference,
                'customer_email' => $request->customer_email,
                'metadata' => [
                    'payment_method' => $request->payment_method,
                    'reference' => $request->reference,
                ],
            ]);

            // Retourner l'URL de la session
            return response()->json(['url' => $session->url]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la session Stripe', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gérer le callback de paiement Paystack
     */
    public function handlePaymentCallbackcopy(Request $request)
    {
        // Vérifier si le paiement a réussi
        $reference = $request->query('reference');
        
        if (!$reference) {
            return redirect()->route('workflow.payment')
                ->with('error', 'Référence de paiement manquante');
        }

        try {
            // Vérifier le statut du paiement auprès de Paystack
            $paystack = new \Yabacon\Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->verify([
                'reference' => $reference
            ]);

            if ('success' === $tranx->data->status) {
                // Paiement réussi, traiter la commande
                $paymentData = [
                    'payment_method' => 'card', // Par défaut, peut être mis à jour
                    'payment_gateway' => 'paystack',
                    'payment_date' => now(),
                    'invoice_number' => 'GW-' . now()->format('YmdHis'),
                    'reference' => $reference,
                    'amount' => $tranx->data->amount / 100, // Convertir de kobo à la devise
                    'status' => 'paid',
                ];
                
                // Stocker les données dans la session
                session()->put('payment', $paymentData);
                
                // Traiter le paiement dans la base de données
                if (config('workflow.save_data_immediately', false) && session()->has('abonnement_id')) {
                    try {
                        $abonnement = \App\Models\Abonnement::find(session('abonnement_id'));
                        if ($abonnement) {
                            $facturation = $this->workflowService->processPayment(
                                $paymentData,
                                $abonnement
                            );
                            session()->put('facturation_id', $facturation->id);
                            Log::info('Payment processed in database', ['facturation_id' => $facturation->id]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing payment', ['error' => $e->getMessage()]);
                    }
                } else if (!config('workflow.save_data_immediately', false)) {
                    // Exécuter le workflow complet si les données n'ont pas été enregistrées précédemment
                    try {
                        $result = $this->workflowService->executeCompleteWorkflow(
                            session('user_account'),
                            session('company_account'),
                            session('subscription'),
                            $paymentData
                        );
                        
                        // Stocker les IDs dans la session
                        session()->put('user_id', $result['user']->id);
                        session()->put('entreprise_id', $result['entreprise']->id);
                        session()->put('abonnement_id', $result['abonnement']->id);
                        session()->put('facturation_id', $result['facturation']->id);
                        
                        Log::info('Complete workflow executed successfully', [
                            'user_id' => $result['user']->id,
                            'entreprise_id' => $result['entreprise']->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error executing complete workflow', ['error' => $e->getMessage()]);
                    }
                }

                // Rediriger vers la page de succès
                return redirect()->route('workflow.dashboard')
                    ->with('success', 'Paiement effectué avec succès !');
            }

            // Paiement échoué
            return redirect()->route('workflow.payment')
                ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du paiement Paystack', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('workflow.payment')
                ->with('error', 'Une erreur est survenue lors de la vérification du paiement. Veuillez contacter le support.');
        }
    }

    /**
     * Gérer le succès du paiement Stripe
     */
    public function handlePaymentSuccesscopy(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        if (!$sessionId) {
            return redirect()->route('workflow.payment')
                ->with('error', 'ID de session manquant');
        }

        try {
            // Initialiser Stripe avec la clé secrète
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            // Récupérer la session
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                // Paiement réussi, traiter la commande
                $paymentData = [
                    'payment_method' => $session->metadata->payment_method ?? 'card',
                    'payment_gateway' => 'stripe',
                    'payment_date' => now(),
                    'invoice_number' => 'GW-' . now()->format('YmdHis'),
                    'reference' => $session->client_reference_id,
                    'amount' => $session->amount_total / 100, // Convertir de centimes à la devise
                    'status' => 'paid',
                ];
                
                // Stocker les données dans la session
                session()->put('payment', $paymentData);
                
                // Traiter le paiement dans la base de données
                if (config('workflow.save_data_immediately', false) && session()->has('abonnement_id')) {
                    try {
                        $abonnement = \App\Models\Abonnement::find(session('abonnement_id'));
                        if ($abonnement) {
                            $facturation = $this->workflowService->processPayment(
                                $paymentData,
                                $abonnement
                            );
                            session()->put('facturation_id', $facturation->id);
                            Log::info('Payment processed in database', ['facturation_id' => $facturation->id]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing payment', ['error' => $e->getMessage()]);
                    }
                } else if (!config('workflow.save_data_immediately', false)) {
                    // Exécuter le workflow complet si les données n'ont pas été enregistrées précédemment
                    try {
                        $result = $this->workflowService->executeCompleteWorkflow(
                            session('user_account'),
                            session('company_account'),
                            session('subscription'),
                            $paymentData
                        );
                        
                        // Stocker les IDs dans la session
                        session()->put('user_id', $result['user']->id);
                        session()->put('entreprise_id', $result['entreprise']->id);
                        session()->put('abonnement_id', $result['abonnement']->id);
                        session()->put('facturation_id', $result['facturation']->id);
                        
                        Log::info('Complete workflow executed successfully', [
                            'user_id' => $result['user']->id,
                            'entreprise_id' => $result['entreprise']->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error executing complete workflow', ['error' => $e->getMessage()]);
                    }
                }

                // Rediriger vers la page de succès
                return redirect()->route('workflow.dashboard')
                    ->with('success', 'Paiement effectué avec succès !');
            }

            // Paiement échoué
            return redirect()->route('workflow.payment')
                ->with('error', 'Le paiement a échoué. Veuillez réessayer.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du paiement Stripe', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('workflow.payment')
                ->with('error', 'Une erreur est survenue lors de la vérification du paiement. Veuillez contacter le support.');
        }
    }

    /**
     * Gérer l'annulation du paiement
     */
    public function handlePaymentCancel(Request $request)
    {
        return redirect()->route('workflow.payment')
            ->with('info', 'Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.');
    }
}
