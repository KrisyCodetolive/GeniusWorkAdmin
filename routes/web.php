<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfigurationPresenceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\PaiementManuelController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\FacturationController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\WebPointageController;
use App\Http\Controllers\SmartClockController;
use App\Http\Controllers\AbonnementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (app()->environment('production')) {
        return redirect('https://work.genius.ci');
    }
    
    // For local development, redirect to a local route
    return redirect()->route('mobile.pointage.scanner');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Route pour le téléchargement des tickets de visite
    Route::get('/visites/{visite}/telecharger-ticket', [App\Http\Controllers\TicketController::class, 'telechargerTicket'])
        ->name('visites.telecharger-ticket');
        
    // Route pour le rapport des visites
    Route::get('/visites/rapport', [App\Http\Controllers\RapportController::class, 'visites'])
        ->name('visites.rapport');
        
    // Route pour le téléchargement des sauvegardes d'entreprise
    Route::get('/entreprise/backup/download', [App\Http\Controllers\BackupController::class, 'download'])
        ->name('entreprise.backup.download');
        
    Route::get('/facturations/{facturation}/pdf', [App\Http\Controllers\FacturationController::class, 'downloadPdf'])
        ->name('facturation.pdf.download');
        
    Route::get('/facture/{facturation}/telecharger', [App\Http\Controllers\FacturationController::class, 'telecharger'])
        ->name('facture.telecharger');
        
    // Routes pour les QR codes
    Route::get('/qrcode/download', [App\Http\Controllers\QrCodeController::class, 'downloadQrCode'])
        ->name('qrcode.download');
    Route::get('/qrcode/download/{id}', [App\Http\Controllers\QrCodeController::class, 'downloadQrCodeById'])
        ->name('qrcode.download.id');
    Route::get('/qrcode/show/{id}', [App\Http\Controllers\QrCodeController::class, 'showQrCodeById'])
        ->name('qrcode.show.id');
    Route::get('/qrcode/show', [App\Http\Controllers\QrCodeController::class, 'showQrCode'])
        ->name('qrcode.show');
});

// Route Redirect to Pricing Goto Workflow
Route::post('/workflow/pricing', [WorkflowController::class, 'pricingGotoWorkflow'])->name('pricing.workflow');


Route::post('/contact', function (Illuminate\Http\Request $request) {
    // Ici, vous pouvez ajouter la logique pour traiter le formulaire de contact
    // Par exemple, envoyer un e-mail ou enregistrer dans la base de données
    
    return back()->with('success', 'Votre message a été envoyé avec succès !');
})->name('contact.submit');

// Routes pour les rapports de présence
Route::middleware(['auth'])->prefix('rapports')->name('rapports.')->group(function () {
    // Rapports de présence
    Route::get('/presences', [App\Http\Controllers\RapportPresenceController::class, 'index'])->name('presences.index');
    Route::match(['get', 'post'], '/presences/generer', [App\Http\Controllers\RapportPresenceController::class, 'generer'])->name('presences.generer');
    Route::get('/presences/pdf', [App\Http\Controllers\RapportPresenceController::class, 'exporterPdf'])->name('presences.pdf');
    
    // Rapports d'heures de travail
    Route::get('/heures-travail', [App\Http\Controllers\RapportHeuresTravailController::class, 'index'])->name('heures-travail.index');
    Route::match(['get', 'post'], '/heures-travail/generer', [App\Http\Controllers\RapportHeuresTravailController::class, 'generer'])->name('heures-travail.generer');
    Route::get('/heures-travail/pdf', [App\Http\Controllers\RapportHeuresTravailController::class, 'exporterPdf'])->name('heures-travail.pdf');
});

// Routes pour le Smart Clock (pointage physique)
Route::prefix('gwork')->middleware(['auth'])->group(function () {
    // Page principale du Smart Clock
    Route::get('/smart-clock', [SmartClockController::class, 'index'])
        ->name('smart-clock.index');
    
    // Traitement du pointage par QR code physique
    Route::post('/webclock/process-physical', [SmartClockController::class, 'processPhysicalQrCode'])
        ->name('smart-clock.process-physical');
    
    // Vérification du statut d'une requête de pointage
    Route::get('/webclock/check-status/{requestId}', [SmartClockController::class, 'checkStatus'])
        ->name('smart-clock.check-status');
        
    // Page de transition après un pointage réussi
    Route::get('/smart-clock-transition', [SmartClockController::class, 'showTransition'])
        ->name('smart-clock.transition');
        
    // Récupérer les derniers pointages pour le site actuel
    Route::get('/webclock/recent-logs', [SmartClockController::class, 'getRecentLogs'])
        ->name('smart-clock.recent-logs');
});

// Routes pour le Web Pointage (QR code scanné avec smartphone)
Route::prefix('pointage')->group(function () {
    // Page de pointage web après scan du QR code
    Route::get('/web/{token}', [SmartClockController::class, 'webPointage'])
        ->name('web-pointage.show');
    
    // Traitement du pointage web
    Route::post('/web/process', [SmartClockController::class, 'processWebPointage'])
        ->name('web-pointage.process');
});

// Route pour la vérification des employés via QR code
Route::get('/verification-employe/{code}', [App\Http\Controllers\EmployeurController::class, 'verifierQrCode'])
    ->name('employe.verification');

// Routes pour le simulateur de coût
Route::get('/simulateur-cout', [App\Http\Controllers\SimulateurCoutController::class, 'index'])
    ->name('simulateur.cout');
Route::post('/simulateur-cout/calculer', [App\Http\Controllers\SimulateurCoutController::class, 'calculer'])->name('simulateur.calculer');
Route::post('/generer-devis', [App\Http\Controllers\SimulateurCoutController::class, 'genererDevis'])->name('generer-devis');
Route::get('/simulateur-cout/devis', [App\Http\Controllers\SimulateurCoutController::class, 'genererDevis'])->name('simulateur.devis');

// Routes pour le WebPointage
Route::prefix('webpointage')->name('webPointage.')->middleware(['auth'])->group(function () {
    Route::get('/', [WebPointageController::class, 'index'])->name('index');
    Route::get('/show/{token}', [WebPointageController::class, 'show'])->name('show');
    Route::post('/pointage', [WebPointageController::class, 'pointage'])->name('pointage');
    Route::get('/historique', [WebPointageController::class, 'historique'])->name('historique');
    Route::get('/qrcode/generate', [WebPointageController::class, 'generateQrCode'])->name('qrcode.generate');
    Route::get('/qrcode/download/{token}', [WebPointageController::class, 'downloadQrCode'])->name('qrcode.download');
});

// Routes pour le workflow d'inscription
Route::prefix('workflow')->name('workflow.')->group(function () {
    Route::get('/', [App\Http\Controllers\WorkflowController::class, 'index'])->name('index');
    
    // Étape 1: Création du compte utilisateurmpa
    Route::get('/user-account', [App\Http\Controllers\WorkflowController::class, 'createUserAccount'])->name('user');
    Route::post('/user-account', [App\Http\Controllers\WorkflowController::class, 'storeUserAccount'])->name('user.store');
    
    // Étape 2: Création du compte entreprise
    Route::get('/company-account', [App\Http\Controllers\WorkflowController::class, 'createCompanyAccount'])->name('company');
    Route::post('/company-account', [App\Http\Controllers\WorkflowController::class, 'storeCompanyAccount'])->name('company.store');
    
    // Étape 3: Sélection de l'abonnement
    Route::get('/subscription', [App\Http\Controllers\WorkflowController::class, 'selectSubscription'])->name('subscription');
    Route::post('/subscription', [App\Http\Controllers\WorkflowController::class, 'storeSubscription'])->name('subscription.store');
    
    // Étape 4: Paiement
    Route::get('/payment', [App\Http\Controllers\WorkflowController::class, 'showPayment'])->name('payment');
    Route::post('/payment', [App\Http\Controllers\WorkflowController::class, 'processPayment'])->name('payment.process');
    
    // Callbacks des passerelles de paiement
    Route::get('/payment/callback', [App\Http\Controllers\WorkflowController::class, 'handlePaymentCallback'])->name('payment.callback');
    Route::get('/payment/success', [App\Http\Controllers\WorkflowController::class, 'handlePaymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [App\Http\Controllers\WorkflowController::class, 'handlePaymentCancel'])->name('payment.cancel');
    
    // API pour créer une session Stripe
    Route::post('/api/create-stripe-session', [App\Http\Controllers\WorkflowController::class, 'createStripeSession'])
        ->name('api.create-stripe-session');
    
    // Étape 5: Tableau de bord d'administration
    Route::get('/dashboard', [App\Http\Controllers\WorkflowController::class, 'showDashboard'])->name('dashboard');
    
    // Téléchargement de la facture
    Route::get('/invoice/download', [App\Http\Controllers\WorkflowController::class, 'downloadInvoice'])->name('invoice.download');
    
    // Page de succès après paiement validé
    Route::get('/success/{paiementReference}', [App\Http\Controllers\WorkflowController::class, 'showSuccess'])->name('success');
});

// Routes pour les paiements

    // Routes pour le paiement direct (préfixe 'paiement' au singulier)
    Route::prefix('paiement')->name('paiement.')->group(function () {
        // Initialiser un paiement (format utilisé dans le formulaire)
        Route::post('/{facturationId}/initialiser', [PaiementController::class, 'initialiser'])
            ->name('initialiser');
            
        // Callbacks pour les passerelles de paiement
        Route::get('/paystack/callback', [PaiementController::class, 'paystackCallback'])
            ->name('paystack.callback');
        
        Route::get('/stripe/callback', [PaiementController::class, 'stripeCallback'])
            ->name('stripe.callback');
            
        // Ajout des callbacks pour les autres passerelles
        Route::get('/manuel/callback', [PaiementController::class, 'manuelCallback'])
            ->name('manuel.callback');
            
        // Route générique pour les callbacks de passerelles
        Route::get('/{gateway}/callback', [PaiementController::class, 'gatewayCallback'])
            ->name('gateway.callback')
            ->where('gateway', '[a-z0-9\-]+');
    });

    // Routes principales pour les paiements (préfixe 'paiements' au pluriel)
    Route::prefix('paiements')->name('paiements.')->group(function () {
        // Choisir la méthode de paiement
        Route::get('/{facturationId}/choisir-methode', [PaiementController::class, 'choisirMethode'])
            ->name('choisir-methode');
        
        // Initialiser un paiement (route alternative pour compatibilité)
        Route::post('/{facturationId}/initialiser', [PaiementController::class, 'initialiser'])
            ->name('initialiser');
        
        // Afficher le statut d'un paiement
        Route::get('/statut/{reference}', [PaiementController::class, 'statut'])
            ->name('statut');
        
        // Annuler un paiement
        Route::post('/annuler/{reference}', [PaiementController::class, 'annuler'])
            ->name('annuler');
        
        // Demander un remboursement
        Route::post('/rembourser/{reference}', [PaiementController::class, 'demanderRemboursement'])
            ->name('rembourser');
        
        // Routes pour les paiements manuels
        Route::prefix('manuel')->name('manuel.')->group(function () {
            // Afficher les instructions pour le paiement manuel
            Route::get('/instructions/{reference}', [PaiementManuelController::class, 'instructions'])
                ->name('instructions');
            
            // Télécharger le justificatif de paiement
            Route::post('/justificatif/{reference}', [PaiementManuelController::class, 'telechargerJustificatif'])
                ->name('justificatif');
            
            // Valider un paiement manuel (admin)
            Route::post('/valider/{reference}', [PaiementManuelController::class, 'valider'])
                ->name('valider')->middleware('permission:validate-payments');
            
            // Rejeter un paiement manuel (admin)
            Route::post('/rejeter/{reference}', [PaiementManuelController::class, 'rejeter'])
                ->name('rejeter')->middleware('permission:validate-payments');
        });
    });

// Webhooks pour les passerelles de paiement (pas besoin d'authentification)
Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])
    ->name('webhooks.paystack')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])
    ->name('webhooks.stripe')
    ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);


// Routes pour la facturation
Route::prefix('facturations')->name('facturations.')->group(function () {
    // Page de paiement des factures (accessible même avec des factures impayées)
    Route::get('/paiement', [FacturationController::class, 'paiement'])
        ->name('paiement')
        ->withoutMiddleware('check.unpaid.invoice');
        
    // Payer toutes les factures en attente
    Route::post('/payer-toutes', [FacturationController::class, 'payerToutes'])
        ->name('payer-toutes')
        ->withoutMiddleware('check.unpaid.invoice');
        
    // Dashboard de facturation
    Route::get('/dashboard', [FacturationController::class, 'dashboard'])
        ->name('dashboard');
        
    // Gestion des factures
    Route::get('/', [FacturationController::class, 'index'])
        ->name('index');
    Route::get('/{facturation}', [FacturationController::class, 'show'])
        ->name('show');
    Route::get('/{facturation}/pdf', [FacturationController::class, 'downloadPdf'])
        ->name('pdf');
    Route::get('/{facturation}/telecharger', [FacturationController::class, 'telecharger'])
        ->name('telecharger');
    Route::post('/{facturation}/payer', [FacturationController::class, 'marquerCommePaye'])
        ->name('payer');
    Route::post('/{facturation}/envoyer-email', [FacturationController::class, 'envoyerParEmail'])
        ->name('envoyer-email');
        
    // Génération automatique de factures (admin seulement)
    Route::post('/generer-automatique', [FacturationController::class, 'genererFacturesAutomatiques'])
        ->name('generer-automatique');
});



// Routes pour les frais d'usage
Route::prefix('frais-usages')->name('frais-usages.')->middleware(['auth', 'verified'])->group(function () {
    // Gestion des frais d'usage
    Route::get('/', [FraisUsageController::class, 'index'])
        ->name('index');
    Route::get('/create', [FraisUsageController::class, 'create'])
        ->name('create');
    Route::post('/', [FraisUsageController::class, 'store'])
        ->name('store');
    Route::get('/{fraisUsage}', [FraisUsageController::class, 'show'])
        ->name('show');
    Route::get('/{fraisUsage}/edit', [FraisUsageController::class, 'edit'])
        ->name('edit');
    Route::put('/{fraisUsage}', [FraisUsageController::class, 'update'])
        ->name('update');
    Route::delete('/{fraisUsage}', [FraisUsageController::class, 'destroy'])
        ->name('destroy');
    
    // Facturation des frais d'usage
    Route::post('/facturer', [FraisUsageController::class, 'facturer'])
        ->name('facturer');
        
    // Rapports des frais d'usage
    Route::get('/rapport', [FraisUsageController::class, 'rapport'])
        ->name('rapport');
});


// Routes d'authentification
Route::middleware('guest')->group(function () {
    // Login routes
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::post('/send-otp', [App\Http\Controllers\Auth\LoginController::class, 'sendOtp'])->name('login.send-otp');
    Route::post('/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'verifyOtp'])->name('login.verify-otp');
    
    // Phone Login Routes
    Route::get('/login/phone', [App\Http\Controllers\Auth\LoginController::class, 'showPhoneLoginForm'])
        ->name('login.phone');
    Route::post('/login/phone/send-otp', [App\Http\Controllers\Auth\LoginController::class, 'sendOtp'])
        ->name('login.phone.send-otp');
    Route::post('/login/phone/verify-otp', [App\Http\Controllers\Auth\LoginController::class, 'loginWithOtp'])
        ->name('login.phone.verify-otp');

    // Registration routes
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'store']);
    Route::get('/register-phone', [App\Http\Controllers\Auth\RegisterController::class, 'createWithPhone'])->name('register.phone');
    Route::post('/register-phone/send-otp', [App\Http\Controllers\Auth\RegisterController::class, 'sendRegistrationOtp'])->name('register.send-otp');
    Route::post('/register-phone/verify-otp', [App\Http\Controllers\Auth\RegisterController::class, 'verifyRegistrationOtp'])->name('register.verify-otp');
    
    // Password reset routes
    Route::get('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'store'])->name('password.update');
});


Route::middleware(['auth'])->group(function () {
    // Logout route
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout.web');
    
    // Email verification routes
    Route::get('/email/verify', [App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])
        ->middleware('throttle:6,1')
        ->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    
    // Password confirmation routes
    Route::get('/confirm-password', [App\Http\Controllers\Auth\ConfirmPasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('/confirm-password', [App\Http\Controllers\Auth\ConfirmPasswordController::class, 'store']);
    
    // Notifications routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::post('/api/notifications/mark-read', [NotificationController::class, 'apiMarkAsRead'])->name('api.notifications.mark-read');
    });
});

// Route dashboard

/*
|--------------------------------------------------------------------------
| Routes pour le pointage mobile avec WebAuthn
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->name('mobile.')->group(function () {
    Route::prefix('pointage')->name('pointage.')->group(function () {
        // Page principale de pointage (après scan QR code)
        Route::get('/{siteId}', [App\Http\Controllers\MobilePointageWebController::class, 'index'])
            ->name('index');
        
        // Page de succès après pointage
        Route::get('/success', [App\Http\Controllers\MobilePointageWebController::class, 'success'])
            ->name('success');
        
        // Page d'erreur
        Route::get('/error', [App\Http\Controllers\MobilePointageWebController::class, 'error'])
            ->name('error');
        
        // Scanner de QR codes
        Route::get('/scanner', [App\Http\Controllers\MobilePointageWebController::class, 'scanner'])
            ->name('scanner');
        
        // Page d'aide
        Route::get('/help', [App\Http\Controllers\MobilePointageWebController::class, 'help'])
            ->name('help');
        
        // Authentification pour le pointage mobile
        Route::post('/login', [App\Http\Controllers\MobilePointageWebController::class, 'login'])
            ->name('login');
    });
});

/*
|--------------------------------------------------------------------------
| Routes pour l'impression des affiches QR Code
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('qr-poster')->name('qr-poster.')->group(function () {
    // Imprimer une affiche pour un site spécifique
    Route::get('/print/{site}', [App\Http\Controllers\QRPosterController::class, 'print'])
        ->name('print');
    
    // Télécharger le PDF d'une affiche
    Route::get('/download/{site}', [App\Http\Controllers\QRPosterController::class, 'downloadPdf'])
        ->name('download-pdf');
    
    // Imprimer toutes les affiches
    Route::get('/print-all', [App\Http\Controllers\QRPosterController::class, 'printAll'])
        ->name('print-all');
    
    // Télécharger le PDF de toutes les affiches
    Route::get('/download-all', [App\Http\Controllers\QRPosterController::class, 'downloadAllPdf'])
        ->name('download-all-pdf');
});

// Inclusion des fichiers de routes
require __DIR__.'/auth.php';
require __DIR__.'/workflow.php';
require __DIR__.'/paie.php'; // Routes du module de paie
require __DIR__.'/abonnement.php'; // Routes du module de changement d'abonnement
