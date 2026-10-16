<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncRecuperer extends Command
{
    protected $signature = 'sync:recuperer {--complet : Ignore les curseurs et recopie toutes les données}';

    protected $description = 'Instance locale : copie toutes les données du cloud (après sync:connexion)';

    public function handle(SyncService $sync): int
    {
        if (config('sync.role') !== 'local' || !config('sync.remote_url')) {
            $this->error('Réservé à l\'instance locale : SYNC_ROLE=local et SYNC_REMOTE_URL requis.');

            return self::FAILURE;
        }

        if ($this->option('complet')) {
            DB::table('sync_curseurs')->delete();
        }

        try {
            $resultat = $sync->recuperer(fn ($ligne) => $this->line($ligne));
        } catch (Throwable $e) {
            $this->error('Récupération interrompue : ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Récupération terminée : ' . array_sum($resultat) . ' ligne(s).');

        return self::SUCCESS;
    }
}
