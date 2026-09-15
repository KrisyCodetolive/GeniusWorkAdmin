<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Site;
use App\Services\Presence\WebPointageService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for payments
Route::prefix('payments')->name('api.payments.')->group(function () {
    Route::post('/verify/{reference}', [App\Http\Controllers\Api\PaymentController::class, 'verify'])
        ->name('verify');
});

// API routes for terminals
Route::prefix('terminals')->name('api.terminals.')->middleware('auth:sanctum')->group(function () {
    // Récupération de la clé de sécurité
    Route::get('/security-key', [App\Http\Controllers\Api\Terminal\SecurityKeyController::class, 'getSecurityKey'])
        ->name('security-key');
    
    // Vérification d'un QR code
    Route::post('/verify-qr', [App\Http\Controllers\Api\Terminal\SecurityKeyController::class, 'verifyQRCode'])
        ->name('verify-qr');
});

// API routes for sites
Route::prefix('sites')->name('api.sites.')->middleware('auth:sanctum')->group(function () {
    // Liste des sites de l'entreprise de l'utilisateur
    Route::get('/', [App\Http\Controllers\Api\SiteController::class, 'index'])
        ->name('index');
    
    // Détails d'un site spécifique
    Route::get('/{id}', [App\Http\Controllers\Api\SiteController::class, 'show'])
        ->name('show');
    
    // Vérifier si l'utilisateur est dans la zone d'un site
    Route::post('/verifier-position', [App\Http\Controllers\Api\SiteController::class, 'verifierPosition'])
        ->name('verifier-position');
    
    // Trouver le site le plus proche
    Route::post('/site-proche', [App\Http\Controllers\Api\SiteController::class, 'siteProche'])
        ->name('site-proche');
    
    // Statistiques des sites
    Route::get('/statistiques', [App\Http\Controllers\Api\SiteController::class, 'statistiques'])
        ->name('statistiques');
});

// API routes for mobile pointage
Route::prefix('pointage')->name('api.pointage.')->group(function () {
    // Enregistrement d'un pointage
    Route::post('/mobile', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'enregistrerPointage'])
        ->middleware('auth:sanctum')
        ->name('mobile');
    
    // Synchronisation des pointages stockés localement
    Route::post('/sync', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'synchroniserPointages'])
        ->name('sync');
    
    // Historique des pointages
    Route::get('/historique', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'getHistorique'])
        ->middleware('auth:sanctum')
        ->name('historique');
    
    // Statistiques de pointage
    Route::get('/statistiques', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'getStatistiques'])
        ->name('statistiques');
    
    // Vérification d'autorisation
    Route::post('/verifier-autorisation', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'verifierAutorisation'])
        ->middleware('auth:sanctum')
        ->name('verifier-autorisation');
    
    // Récupération des coordonnées du site
    Route::post('/site-coordinates', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'getSiteCoordinates'])
        ->middleware('auth:sanctum')
        ->name('site-coordinates');
    
    // Vérification de la position
    Route::post('/verifier-position', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'verifierPosition'])
        ->middleware('auth:sanctum')
        ->name('verifier-position');
    
    // Récupération de l'état de pointage actuel
    Route::get('/etat-pointage', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'getEtatPointage'])
        ->middleware('auth:sanctum')
        ->name('etat-pointage');
});

// API de pointage pour une borne physique ou un mini-serveur externe.
Route::post('v1/kiosk/pointage', function (Request $request, WebPointageService $pointageService) {
    $validated = $request->validate([
        'idno' => ['required', 'string'],
        'site_id' => ['required', 'string', 'exists:sites,id'],
        'pause' => ['sometimes', 'boolean'],
        'lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
        'lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
    ]);

    $site = Site::findOrFail($validated['site_id']);

    $pointageRequest = Request::create('', 'POST', [
        'idno' => $validated['idno'],
        'token' => $site->qr_token,
        'methode_pointage' => 'qrcode_physique',
        'pause' => $validated['pause'] ?? false,
        'lat' => $validated['lat'] ?? null,
        'lng' => $validated['lng'] ?? null,
    ]);

    $result = $pointageService->processPointage($pointageRequest);

    if (($result['status'] ?? null) === 'success') {
        $result['redirect'] = route('mobile.pointage.success', [
            'type' => $result['data']['type'] ?? 'entree',
            'employeur' => $result['data']['employee'] ?? '',
            'site' => $result['data']['site'] ?? $site->nom,
            'heures' => $result['data']['info_supplementaire'] ?? '',
        ]);
    }

    return response()->json($result, ($result['status'] ?? null) === 'success' ? 200 : 422);
})->name('api.v1.kiosk.pointage');

/*
|--------------------------------------------------------------------------
| Routes d'authentification pour les employés (Mobile)
|--------------------------------------------------------------------------
*/
// Route directe sans groupe pour l'authentification mobile
Route::post('mobile/auth/login', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'login'])->name('mobile.auth.login');
Route::post('mobile/auth/logout', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'logout'])->middleware('auth:sanctum')->name('mobile.auth.logout');
Route::get('mobile/auth/verify', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'verifyToken'])->middleware('auth:sanctum')->name('mobile.auth.verify');

// Routes OTP pour l'authentification mobile
Route::post('mobile/auth/verify-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'verifyOtp'])->name('mobile.auth.verify-otp');
Route::post('mobile/auth/resend-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'resendOtp'])->name('mobile.auth.resend-otp');
Route::post('mobile/auth/request-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'requestOtp'])->name('mobile.auth.request-otp');

/*
|--------------------------------------------------------------------------
| Routes d'authentification pour les employés
|--------------------------------------------------------------------------
*/
// API routes for conges
Route::prefix('conges')->name('api.conges.')->middleware('auth:sanctum')->group(function () {
    // Liste des congés de l'utilisateur
    Route::get('/', [App\Http\Controllers\Api\CongeController::class, 'index'])
        ->name('index');
    
    // Créer une nouvelle demande
    Route::post('/', [App\Http\Controllers\Api\CongeController::class, 'store'])
        ->name('store');
    
    // Liste des types de congés
    Route::get('/types', [App\Http\Controllers\Api\CongeController::class, 'types'])
        ->name('types');
    
    // Statistiques des congés
    Route::get('/user/statistics', [App\Http\Controllers\Api\CongeController::class, 'statistics'])
        ->name('statistics');
    
    // Détails d'un congé
    Route::get('/{conge}', [App\Http\Controllers\Api\CongeController::class, 'show'])
        ->name('show');
    
    // Annuler une demande
    Route::post('/{conge}/cancel', [App\Http\Controllers\Api\CongeController::class, 'cancel'])
        ->name('cancel');
    
    // Approuver une demande
    Route::post('/{conge}/approve', [App\Http\Controllers\Api\CongeController::class, 'approve'])
        ->name('approve');
    
    // Rejeter une demande
    Route::post('/{conge}/reject', [App\Http\Controllers\Api\CongeController::class, 'reject'])
        ->name('reject');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('verify', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'verifyToken'])->middleware('auth:sanctum');
    
    // Routes pour l'authentification OTP
    Route::post('verify-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'resendOtp']);
    Route::post('request-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'requestOtp']);
});

/*
|--------------------------------------------------------------------------
| Routes pour les webhooks MTN SMS
|--------------------------------------------------------------------------
*/
Route::prefix('webhooks/mtn-sms')->name('api.webhooks.mtn-sms.')->group(function () {
    // Réception des notifications de livraison
    Route::post('/delivery-receipt', [App\Http\Controllers\API\MTNSMSWebhookController::class, 'deliveryReceipt'])
        ->name('delivery-receipt');
    
    // Réception des SMS entrants
    Route::post('/inbound-message', [App\Http\Controllers\API\MTNSMSWebhookController::class, 'inboundMessage'])
        ->name('inbound-message');
});

/*
|--------------------------------------------------------------------------
| Routes pour tester les services SMS
|--------------------------------------------------------------------------
*/
Route::prefix('sms-test')->name('api.sms-test.')->group(function () {
    // Test d'envoi SMS avec le fournisseur par défaut ou spécifié
    Route::match(['get', 'post'], '/send', [App\Http\Controllers\API\SMSTestController::class, 'sendTestSMS'])
        ->name('send');
    
    // Test d'envoi SMS avec Orange
    Route::match(['get', 'post'], '/send-orange', [App\Http\Controllers\API\SMSTestController::class, 'sendOrangeSMS'])
        ->name('send-orange');
    
    // Test d'envoi SMS avec MTN
    Route::match(['get', 'post'], '/send-mtn', [App\Http\Controllers\API\SMSTestController::class, 'sendMTNSMS'])
        ->name('send-mtn');
    
    // Vérification du statut de livraison MTN
    Route::post('/mtn-delivery-status', [App\Http\Controllers\API\SMSTestController::class, 'checkMTNDeliveryStatus'])
        ->name('mtn-delivery-status');
    
    // Création d'un abonnement aux notifications MTN
    Route::post('/mtn-create-subscription', [App\Http\Controllers\API\SMSTestController::class, 'createMTNDeliverySubscription'])
        ->name('mtn-create-subscription');
    
    // Suppression d'un abonnement aux notifications MTN
    Route::post('/mtn-delete-subscription', [App\Http\Controllers\API\SMSTestController::class, 'deleteMTNDeliverySubscription'])
        ->name('mtn-delete-subscription');
    
    // Récupération des SMS entrants MTN
    Route::post('/mtn-inbound-messages', [App\Http\Controllers\API\SMSTestController::class, 'getMTNInboundMessages'])
        ->name('mtn-inbound-messages');
    
    // Statistiques des SMS
    Route::get('/stats', [App\Http\Controllers\API\SMSTestController::class, 'getSMSStats'])
        ->name('stats');
});

/*
|--------------------------------------------------------------------------
| Routes pour le pointage mobile avec WebAuthn
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->name('api.mobile.')->group(function () {
    // Route de test pour vérifier si le contrôleur fonctionne
    Route::get('/test', function() {
        return response()->json(['success' => true, 'message' => 'API mobile fonctionne correctement']);
    });
    
    // Routes publiques (sans authentification)
    Route::prefix('pointage')->name('pointage.')->group(function () {
        // Validation du QR code
        Route::post('/qr/validate', [App\Http\Controllers\Api\MobilePointageController::class, 'validateQRCode'])
            ->name('qr.validate');
        
        // Génération des options d'authentification WebAuthn
        Route::post('/webauthn/auth-options', [App\Http\Controllers\Api\MobilePointageController::class, 'getWebAuthnAuthOptions'])
            ->name('webauthn.auth-options');
        
        // Vérification de l'authentification WebAuthn
        Route::post('/webauthn/verify-auth', [App\Http\Controllers\Api\MobilePointageController::class, 'verifyWebAuthnAuth'])
            ->name('webauthn.verify-auth');
        
        // Validation de la géolocalisation
        Route::post('/location/validate', [App\Http\Controllers\Api\MobilePointageController::class, 'validateLocation'])
            ->name('location.validate');
        
        // Enregistrement du pointage
        Route::post('/record', [App\Http\Controllers\Api\MobilePointageController::class, 'recordPointage'])
            ->name('record');
        
        // Récupération de l'historique des pointages
        Route::post('/history', [App\Http\Controllers\Api\MobilePointageController::class, 'getPresenceHistory'])
            ->name('history');
        
        // Récupération du statut actuel
        Route::post('/status', [App\Http\Controllers\Api\MobilePointageController::class, 'getCurrentStatus'])
            ->name('status');
        
        // Calcul des heures de travail
        Route::post('/working-hours', [App\Http\Controllers\Api\MobilePointageController::class, 'getWorkingHours'])
            ->name('working-hours');
    });
});

/*
|--------------------------------------------------------------------------
| Routes pour WebAuthn
|--------------------------------------------------------------------------
*/
Route::prefix('webauthn')->name('api.webauthn.')->group(function () {
    // Handle OPTIONS requests for CORS preflight
    Route::options('/{any}', function() {
        return response()->json([], 200);
    })->where('any', '.*');
    
    // Vérifier si l'utilisateur a des informations d'identification WebAuthn
    Route::get('/credentials/check', [App\Http\Controllers\WebAuthnController::class, 'checkCredentials'])
        ->withoutMiddleware('auth:sanctum') // Temporarily remove auth middleware for testing
        ->name('credentials.check');
    
    // Options d'enregistrement WebAuthn
    Route::post('/register/options', [App\Http\Controllers\WebAuthnController::class, 'generateRegistrationOptions'])
        ->withoutMiddleware('auth:sanctum') // Temporarily remove auth middleware for testing
        ->name('register.options');
    
    // Vérification de l'enregistrement WebAuthn
    Route::post('/register/verify', [App\Http\Controllers\WebAuthnController::class, 'register'])
        ->withoutMiddleware('auth:sanctum') // Temporarily remove auth middleware for testing
        ->name('register.verify');
    
    // Options d'authentification WebAuthn
    Route::post('/login/options', [App\Http\Controllers\WebAuthnController::class, 'generateAuthenticationOptions'])
        ->name('login.options');
    
    // Vérification de l'authentification WebAuthn
    Route::post('/login/verify', [App\Http\Controllers\WebAuthnController::class, 'authenticate'])
        ->name('login.verify');
});

/*
|--------------------------------------------------------------------------
| Routes pour la gestion des QR codes (authentifiées)
|--------------------------------------------------------------------------
*/
Route::prefix('qr-codes')->name('api.qr-codes.')->middleware('auth:sanctum')->group(function () {
    // Génération d'un QR code pour un site
    Route::post('/sites/{siteId}/generate', [App\Http\Controllers\API\QRCodeController::class, 'generateQRCode'])
        ->name('sites.generate');
    
    // Rafraîchissement d'un QR code
    Route::post('/sites/{siteId}/refresh', [App\Http\Controllers\API\QRCodeController::class, 'refreshQRCode'])
        ->name('sites.refresh');
    
    // Invalidation d'un QR code
    Route::delete('/sites/{siteId}/invalidate', [App\Http\Controllers\API\QRCodeController::class, 'invalidateQRCode'])
        ->name('sites.invalidate');
    
    // Informations d'un QR code (route publique pour validation)
    Route::post('/info', [App\Http\Controllers\API\QRCodeController::class, 'getQRCodeInfo'])
        ->withoutMiddleware('auth:sanctum')
        ->name('info');
    
    // Génération de QR codes pour tous les sites de l'entreprise
    Route::post('/entreprise/generate-all', [App\Http\Controllers\API\QRCodeController::class, 'generateQRCodesForEntreprise'])
        ->name('entreprise.generate-all');
    
    // Statistiques d'utilisation des QR codes
    Route::post('/stats', [App\Http\Controllers\API\QRCodeController::class, 'getQRCodeStats'])
        ->name('stats');
    
    // Liste des sites avec leur statut de QR code
    Route::get('/sites/status', [App\Http\Controllers\API\QRCodeController::class, 'listSitesWithQRStatus'])
        ->name('sites.status');
    
    // Nettoyage des QR codes expirés
    Route::post('/cleanup', [App\Http\Controllers\API\QRCodeController::class, 'cleanupExpiredQRCodes'])
        ->name('cleanup');
});
