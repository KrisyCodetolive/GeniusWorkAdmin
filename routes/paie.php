<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Paie\BulletinPaieController;
use App\Http\Controllers\Paie\ConfigurationPaieController;

/*
|--------------------------------------------------------------------------
| Routes du Module Paie
|--------------------------------------------------------------------------
|
| Ce fichier contient les routes pour le module de paie.
|
*/

// Routes pour les bulletins de paie
Route::prefix('paie/bulletins')->name('paie.bulletins.')->group(function () {
    Route::get('/', [BulletinPaieController::class, 'index'])->name('index');
    Route::get('/create', [BulletinPaieController::class, 'create'])->name('create');
    Route::post('/', [BulletinPaieController::class, 'store'])->name('store');
    Route::get('/{id}', [BulletinPaieController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [BulletinPaieController::class, 'edit'])->name('edit');
    Route::put('/{id}', [BulletinPaieController::class, 'update'])->name('update');
    Route::delete('/{id}', [BulletinPaieController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/pdf', [BulletinPaieController::class, 'pdf'])->name('pdf');
    Route::post('/{id}/valider', [BulletinPaieController::class, 'valider'])->name('valider');
    Route::post('/{id}/annuler', [BulletinPaieController::class, 'annuler'])->name('annuler');
    
    // Routes pour le wizard de génération de bulletins de paie
    Route::get('/wizard/generate', [BulletinPaieController::class, 'generateWizard'])->name('wizard.generate');
    Route::get('/wizard/generate-masse', [BulletinPaieController::class, 'generateMasseWizard'])->name('wizard.generate-masse');
    Route::post('/wizard/process', [BulletinPaieController::class, 'processGenerate'])->name('wizard.process');
    Route::post('/wizard/process-masse', [BulletinPaieController::class, 'processGenerateMasse'])->name('wizard.process-masse');
    
    // Routes AJAX pour le wizard
    Route::get('/ajax/employeur-info', [BulletinPaieController::class, 'getEmployeurInfo'])->name('ajax.employeur-info');
    Route::post('/ajax/calculate-elements', [BulletinPaieController::class, 'calculateElements'])->name('ajax.calculate-elements');
});

// Routes pour les configurations de paie
Route::prefix('paie/configurations')->name('paie.configurations.')->group(function () {
    Route::get('/', [ConfigurationPaieController::class, 'index'])->name('index');
    Route::get('/create', [ConfigurationPaieController::class, 'create'])->name('create');
    Route::post('/', [ConfigurationPaieController::class, 'store'])->name('store');
    Route::get('/{id}', [ConfigurationPaieController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [ConfigurationPaieController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ConfigurationPaieController::class, 'update'])->name('update');
    Route::delete('/{id}', [ConfigurationPaieController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/set-default', [ConfigurationPaieController::class, 'setDefault'])->name('set-default');
});
