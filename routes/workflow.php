<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowController;

// Routes pour le workflow d'inscription
Route::prefix('workflow')->name('workflow.')->group(function () {
    Route::get('/', [WorkflowController::class, 'index'])->name('index');
    
    // Étape 1: Création du compte utilisateur
    Route::get('/user-account', [WorkflowController::class, 'createUserAccount'])->name('user');
    Route::post('/user-account', [WorkflowController::class, 'storeUserAccount'])->name('user.store');
    
    // Étape 2: Création du compte entreprise
    Route::get('/company-account', [WorkflowController::class, 'createCompanyAccount'])->name('company');
    Route::post('/company-account', [WorkflowController::class, 'storeCompanyAccount'])->name('company.store');
    
    // Étape 3: Sélection de l'abonnement
    Route::get('/subscription', [WorkflowController::class, 'selectSubscription'])->name('subscription');
    Route::post('/subscription', [WorkflowController::class, 'storeSubscription'])->name('subscription.store');
    
    // Étape 4: Paiement
    Route::get('/payment', [WorkflowController::class, 'showPayment'])->name('payment');
    Route::post('/payment', [WorkflowController::class, 'processPayment'])->name('payment.process');
    
    // Callbacks des passerelles de paiement
    Route::get('/payment/callback', [WorkflowController::class, 'handlePaymentCallback'])->name('payment.callback');
    Route::get('/payment/success', [WorkflowController::class, 'handlePaymentSuccess'])->name('payment.success');
    Route::get('/payment/cancel', [WorkflowController::class, 'handlePaymentCancel'])->name('payment.cancel');
    
    // API pour créer une session Stripe
    Route::post('/api/create-stripe-session', [WorkflowController::class, 'createStripeSession'])
        ->name('api.create-stripe-session');
    
    // Étape 5: Tableau de bord d'administration
    Route::get('/dashboard', [WorkflowController::class, 'showDashboard'])->name('dashboard');
    
    // Téléchargement de la facture
    Route::get('/invoice/download', [WorkflowController::class, 'downloadInvoice'])->name('invoice.download');
    
    // Page de succès après paiement validé
    Route::get('/success/{paiementReference}', [WorkflowController::class, 'showSuccess'])->name('success');
});
