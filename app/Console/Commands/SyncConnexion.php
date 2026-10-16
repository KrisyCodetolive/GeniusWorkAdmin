<?php

namespace App\Console\Commands;

use App\Services\Sync\SyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncConnexion extends Command
{
    protected $signature = 'sync:connexion {email? : Email du compte SuperAdmin du backoffice cloud} {--deconnexion : Oublie le jeton enregistré}';

    protected $description = 'Instance locale : se connecte au cloud avec un compte SuperAdmin pour autoriser la récupération des données';

    public function handle(SyncService $sync): int
    {
        if (config('sync.role') !== 'local' || !config('sync.remote_url')) {
            $this->error('Réservé à l\'instance locale : SYNC_ROLE=local et SYNC_REMOTE_URL requis.');

            return self::FAILURE;
        }

        if ($this->option('deconnexion')) {
            $sync->deconnecter();
            $this->info('Jeton supprimé. La récupération est bloquée jusqu\'à la prochaine connexion.');

            return self::SUCCESS;
        }

        $email = $this->argument('email') ?: $this->ask('Email du compte SuperAdmin');
        $motDePasse = $this->secret('Mot de passe');

        try {
            $sync->connecter($email, (string) $motDePasse);
        } catch (Throwable $e) {
            $this->error('Connexion refusée : ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Connecté. Lancez php artisan sync:recuperer pour copier les données.');

        return self::SUCCESS;
    }
}
