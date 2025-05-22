<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImpersonateController;
use App\Filament\Widgets\ImpersonateWidget;

/*
|--------------------------------------------------------------------------
| Impersonate Routes
|--------------------------------------------------------------------------
|
| Routes pour la fonctionnalité "Se connecter en tant que" permettant aux
| SuperAdmin et Support de se connecter temporairement avec le compte
| d'un autre utilisateur sans connaître ses identifiants.
|
*/

Route::middleware(['auth'])->group(function () {
    // Se connecter en tant qu'un autre utilisateur (uniquement SuperAdmin et Support)
    Route::get('/impersonate/{userId}', [ImpersonateController::class, 'impersonate'])
        ->name('impersonate');
    
    // Revenir à son compte original
    Route::get('/impersonate-stop', [ImpersonateController::class, 'stopImpersonating'])
        ->name('impersonate.stop');
        
    // Route pour Filament (arrêter l'impersonation)
    Route::get('/filament-impersonate-stop', function() {
        return ImpersonateWidget::stopImpersonating();
    })->name('filament.impersonate.stop');
});
