<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncService;
use Illuminate\Console\Command;

class SyncPousser extends Command
{
    protected $signature = 'sync:pousser';

    protected $description = 'Envoie à l\'autre instance les changements en attente (pointages, demandes, statuts)';

    public function handle(SyncService $sync): int
    {
        if (!$sync->envoiActif()) {
            $this->warn('Envoi désactivé : SYNC_ROLE, SYNC_REMOTE_URL, SYNC_SECRET et SYNC_ENTREPRISE_ID requis.');

            return self::SUCCESS;
        }

        $envoyes = $sync->pousser(fn ($ligne) => $this->warn($ligne));
        $this->info("{$envoyes} changement(s) envoyé(s).");

        return self::SUCCESS;
    }
}
