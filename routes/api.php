<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
        ->name('mobile');
    
    // Synchronisation des pointages stockés localement
    Route::post('/sync', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'synchroniserPointages'])
        ->name('sync');
    
    // Historique des pointages
    Route::get('/historique', [App\Http\Controllers\Api\Presence\MobilePointageController::class, 'getHistorique'])
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
});

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

/*
|--------------------------------------------------------------------------
| Routes d'authentification pour les employés
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('verify', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'verifyToken'])->middleware('auth:sanctum');
    
    // Routes pour l'authentification OTP
    Route::post('verify-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [App\Http\Controllers\Api\Auth\EmployeAuthController::class, 'resendOtp']);
});
