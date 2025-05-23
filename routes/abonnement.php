<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChangeAbonnementController;
use App\Http\Controllers\AbonnementController;

/*
|--------------------------------------------------------------------------
| Routes pour le changement d'abonnement
|--------------------------------------------------------------------------
|
| Routes dédiées à la gestion du changement d'abonnement
|
*/

Route::middleware(['auth'])->prefix('abonnements')->name('abonnements.')->group(function () {
    // Routes statiques d'abord (sans paramètres variables)
    // Page de confirmation
    Route::get('/change/confirm', [ChangeAbonnementController::class, 'showConfirmation'])
        ->name('change.confirm');
    
    // Initialisation du paiement
    Route::get('/change/payment', [ChangeAbonnementController::class, 'initierPaiement'])
        ->name('change.payment');
    
    // Callback après paiement
    Route::get('/change/callback', [ChangeAbonnementController::class, 'handlePaymentCallback'])
        ->name('change.callback');
    
    // Page de succès
    Route::get('/change/success', [ChangeAbonnementController::class, 'showSuccess'])
        ->name('change.success');
    
    // Routes avec paramètres ensuite
    // Formulaire de changement d'abonnement
    Route::get('/change/{abonnementId}', [ChangeAbonnementController::class, 'showChangeForm'])
        ->name('change.form');
    
    // Traitement de la demande de changement
    Route::post('/change/{abonnementId}/process', [ChangeAbonnementController::class, 'processChangeRequest'])
        ->name('change.process');
});


// Routes pour la gestion des abonnements
Route::prefix('abonnements')->name('abonnements.')->middleware(['auth', 'verified'])->group(function () {
    // Tableau de bord des abonnements (admin)
    Route::get('/dashboard', [AbonnementController::class, 'dashboard'])
        ->name('dashboard');
    
    // Routes pour les abonnements d'une entreprise spécifique
    Route::get('/{entreprise}', [AbonnementController::class, 'index'])
        ->name('index');
    Route::get('/{entreprise}/create', [AbonnementController::class, 'create'])
        ->name('create');
    Route::post('/{entreprise}', [AbonnementController::class, 'store'])
        ->name('store');
    Route::get('/{entreprise}/{abonnement}', [AbonnementController::class, 'show'])
        ->name('show');
    Route::get('/{entreprise}/{abonnement}/edit', [AbonnementController::class, 'edit'])
        ->name('edit');
    Route::put('/{entreprise}/{abonnement}', [AbonnementController::class, 'update'])
        ->name('update');
    
    // Activation/désactivation d'un abonnement
    Route::patch('/{entreprise}/{abonnement}/activer', [AbonnementController::class, 'activer'])
        ->name('activer');
    Route::patch('/{entreprise}/{abonnement}/desactiver', [AbonnementController::class, 'desactiver'])
        ->name('desactiver');
    
    // Renouvellement d'un abonnement
    Route::get('/{entreprise}/{abonnement}/renouveler', [AbonnementController::class, 'renouvelerForm'])
        ->name('renouveler.form');
    Route::patch('/{entreprise}/{abonnement}/renouveler', [AbonnementController::class, 'renouveler'])
        ->name('renouveler');
    
    // Changement de plan d'abonnement
    Route::get('/{entreprise}/{abonnement}/changer-plan', [AbonnementController::class, 'changerPlanForm'])
        ->name('changer-plan.form');
    Route::patch('/{entreprise}/{abonnement}/changer-plan', [AbonnementController::class, 'changerPlan'])
        ->name('changer-plan');
});