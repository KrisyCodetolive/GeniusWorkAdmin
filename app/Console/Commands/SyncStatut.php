<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncStatut extends Command
{
    protected $signature = 'sync:statut {--alerte=30 : Minutes d\'attente au-delà desquelles une alerte est journalisée}';

    protected $description = 'Affiche l\'état de la synchronisation et alerte si des changements attendent trop longtemps';

    public function handle(): int
    {
        $enAttente = DB::table('sync_outbox')->whereNull('envoye_at');
        $nombre = (clone $enAttente)->count();
        $plusAncien = (clone $enAttente)->min('created_at');
        $derniereErreur = (clone $enAttente)->whereNotNull('derniere_erreur')->orderByDesc('updated_at')->value('derniere_erreur');

        $this->table(['Indicateur', 'Valeur'], [
            ['Rôle', config('sync.role') ?: 'désactivé'],
            ['Instance distante', config('sync.remote_url') ?: '-'],
            ['Changements en attente', $nombre],
            ['Plus ancien en attente', $plusAncien ?: '-'],
            ['Dernière erreur', $derniereErreur ? mb_substr($derniereErreur, 0, 120) : '-'],
            ['Dernière récupération', DB::table('sync_curseurs')->max('updated_at') ?: '-'],
        ]);

        if ($plusAncien && now()->diffInMinutes($plusAncien, true) > (int) $this->option('alerte')) {
            Log::warning('Sync : des changements attendent depuis trop longtemps', [
                'en_attente' => $nombre,
                'plus_ancien' => $plusAncien,
                'derniere_erreur' => $derniereErreur,
            ]);
        }

        return self::SUCCESS;
    }
}
