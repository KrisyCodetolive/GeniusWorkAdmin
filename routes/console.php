<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Synchronisation cloud <-> instance locale (inactive tant que SYNC_ROLE n'est pas défini)
if (config('sync.role')) {
    // Envoi des changements en attente (reprise après une coupure de connexion)
    Schedule::command('sync:pousser')
        ->everyMinute()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/sync-pousser.log'));

    // Copie des données du cloud vers l'instance locale
    if (config('sync.role') === 'local') {
        Schedule::command('sync:recuperer')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/sync-recuperer.log'));
    }

    // Alerte si des changements attendent depuis plus de 30 minutes
    Schedule::command('sync:statut')
        ->everyThirtyMinutes()
        ->appendOutputTo(storage_path('logs/sync-statut.log'));
}
