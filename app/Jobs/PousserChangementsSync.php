<?php

namespace App\Jobs;

use App\Services\Sync\SyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Envoie les changements en attente à l'autre instance juste après une modification.
 * En cas d'échec, la commande planifiée sync:pousser reprend l'envoi.
 */
class PousserChangementsSync implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1;

    public int $uniqueFor = 60;

    public function handle(SyncService $sync): void
    {
        $sync->pousser();
    }
}
