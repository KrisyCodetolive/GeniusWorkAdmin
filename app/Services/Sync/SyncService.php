<?php

namespace App\Services\Sync;

use App\Jobs\PousserChangementsSync;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Synchronisation entre l'instance cloud et l'instance locale.
 *
 * - Récupération : après connexion avec un compte SuperAdmin du backoffice, l'instance locale lit
 *   toutes les tables du cloud page par page (GET /api/sync/changes).
 * - Envoi : chaque changement d'une table bidirectionnelle est mis dans sync_outbox,
 *   puis envoyé signé à l'autre instance (POST /api/sync/recevoir) dès que la connexion le permet.
 * - Conflits : la ligne la plus récente (updated_at) gagne.
 *
 * Les lignes reçues sont écrites avec le query builder : aucun événement de modèle n'est déclenché,
 * donc un changement reçu n'est jamais renvoyé (pas de boucle).
 */
class SyncService
{
    // Jeton du compte backoffice, chiffré avec APP_KEY
    protected const FICHIER_JETON = 'sync/jeton';

    protected array $colonnes = [];

    public function actif(): bool
    {
        return in_array(config('sync.role'), ['cloud', 'local'], true)
            && filled(config('sync.secret'))
            && filled(config('sync.entreprise_id'));
    }

    public function envoiActif(): bool
    {
        return $this->actif() && filled(config('sync.remote_url'));
    }

    /* ------------------------------------------------------------------
     | Lecture des tables (côté cloud)
     * ------------------------------------------------------------------ */

    /**
     * Tables de la base copiées vers l'instance locale (toutes sauf config('sync.tables_exclues')).
     */
    public function tablesCopiees(): array
    {
        $tables = Schema::getTableListing(Schema::getCurrentSchemaListing(), false);

        return array_values(array_filter($tables, function ($table) {
            foreach (config('sync.tables_exclues') as $exclue) {
                if ($table === $exclue || (str_ends_with($exclue, '*') && str_starts_with($table, rtrim($exclue, '*')))) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Une page de lignes d'une table modifiées depuis $since (inclus), lignes supprimées comprises.
     */
    public function lirePage(string $table, ?string $since, int $page): array
    {
        if (!in_array($table, $this->tablesCopiees(), true)) {
            throw new RuntimeException("Table non copiable : {$table}");
        }

        $limite = (int) config('sync.limite');
        $requete = DB::table($table);

        // Les tables sans id ou sans updated_at sont toujours renvoyées en entier
        if ($this->incrementale($table)) {
            if ($since) {
                $requete->where('updated_at', '>=', $since);
            }
            $requete->orderBy('updated_at')->orderBy('id');
        } else {
            foreach ($this->colonnes($table) as $colonne) {
                $requete->orderBy($colonne);
            }
        }

        $lignes = $requete->forPage($page, $limite)->get()->map(fn ($l) => (array) $l)->all();

        return [
            'table' => $table,
            'lignes' => $lignes,
            'suite' => count($lignes) === $limite,
        ];
    }

    /* ------------------------------------------------------------------
     | Récupération (côté local)
     * ------------------------------------------------------------------ */

    /**
     * Connexion au cloud avec un compte SuperAdmin du backoffice : le jeton obtenu est gardé chiffré.
     */
    public function connecter(string $email, string $motDePasse): void
    {
        $reponse = $this->appeler('POST', '/api/sync/connexion', [], [
            'email' => $email,
            'password' => $motDePasse,
            'appareil' => gethostname() ?: 'instance-locale',
        ]);

        if (!$reponse->successful()) {
            throw new RuntimeException($reponse->json('message') ?: 'Connexion refusée (HTTP ' . $reponse->status() . ')');
        }

        Storage::disk('local')->put(self::FICHIER_JETON, Crypt::encryptString($reponse->json('token')));
    }

    public function deconnecter(): void
    {
        Storage::disk('local')->delete(self::FICHIER_JETON);
    }

    protected function jeton(): string
    {
        if (!Storage::disk('local')->exists(self::FICHIER_JETON)) {
            throw new RuntimeException('Aucune connexion au cloud : lancez php artisan sync:connexion avec un compte SuperAdmin.');
        }

        return Crypt::decryptString(Storage::disk('local')->get(self::FICHIER_JETON));
    }

    /**
     * Copie dans la base locale toutes les données du cloud modifiées depuis la dernière récupération.
     * Renvoie le nombre de lignes reçues par table.
     */
    public function recuperer(?callable $journal = null): array
    {
        $resultat = [];
        $jeton = $this->jeton();

        Schema::withoutForeignKeyConstraints(function () use (&$resultat, $journal, $jeton) {
            foreach ($this->tablesCopiees() as $table) {
                $incrementale = $this->incrementale($table);
                $since = $incrementale ? DB::table('sync_curseurs')->where('table_name', $table)->value('since') : null;
                $max = $since;
                $recues = [];
                $total = 0;
                $page = 1;

                do {
                    $reponse = $this->appeler('GET', '/api/sync/changes', [
                        'table' => $table,
                        'since' => $since,
                        'page' => $page,
                    ], null, $jeton);

                    if ($reponse->status() === 401) {
                        throw new RuntimeException('Jeton refusé par le cloud : reconnectez-vous avec php artisan sync:connexion.');
                    }

                    $reponse = $reponse->throw()->json();

                    $lignes = $reponse['lignes'] ?? [];

                    if ($incrementale) {
                        // Pas de notification lors de la toute première copie (historique)
                        $this->appliquerLignes($table, $lignes, $since !== null);
                    } else {
                        array_push($recues, ...$lignes);
                    }

                    foreach ($lignes as $ligne) {
                        if (!empty($ligne['updated_at']) && $ligne['updated_at'] > (string) $max) {
                            $max = $ligne['updated_at'];
                        }
                    }

                    $total += count($lignes);
                    $page++;
                } while ($reponse['suite'] ?? false);

                // Table sans id ou sans updated_at : remplacement complet, pour refléter aussi les lignes retirées
                if (!$incrementale) {
                    DB::transaction(function () use ($table, $recues) {
                        DB::table($table)->delete();
                        $this->appliquerLignes($table, $recues);
                    });
                }

                DB::table('sync_curseurs')->updateOrInsert(
                    ['table_name' => $table],
                    ['since' => $max, 'updated_at' => now(), 'created_at' => now()]
                );

                $resultat[$table] = $total;

                if ($journal && $total) {
                    $journal("{$table} : {$total} ligne(s)");
                }
            }
        });

        return $resultat;
    }

    /* ------------------------------------------------------------------
     | Envoi des changements (les deux instances)
     * ------------------------------------------------------------------ */

    /**
     * Note un changement à envoyer à l'autre instance, si la table et l'entreprise sont synchronisées.
     */
    public function enregistrerChangement(string $table, string $id, array $attributs): void
    {
        if (!$this->envoiActif() || !in_array($table, config('sync.tables_bidirectionnelles'), true)) {
            return;
        }

        try {
            if ($this->entrepriseDeLigne($table, $attributs) !== config('sync.entreprise_id')) {
                return;
            }

            // Une seule entrée en attente par ligne : son contenu est lu au moment de l'envoi
            $enAttente = DB::table('sync_outbox')
                ->where('table_name', $table)
                ->where('record_id', $id)
                ->whereNull('envoye_at')
                ->exists();

            if (!$enAttente) {
                DB::table('sync_outbox')->insert([
                    'table_name' => $table,
                    'record_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Envoi immédiat par la file d'attente ; sinon la tâche planifiée s'en charge
            if (config('queue.default') !== 'sync') {
                PousserChangementsSync::dispatch()->afterCommit();
            }
        } catch (Throwable $e) {
            // Ne jamais bloquer un pointage ou une demande à cause de la synchronisation
            Log::error('Sync : changement non enregistré', ['table' => $table, 'id' => $id, 'erreur' => $e->getMessage()]);
        }
    }

    /**
     * Envoie les changements en attente à l'autre instance. Renvoie le nombre de changements acceptés.
     */
    public function pousser(?callable $journal = null): int
    {
        if (!$this->envoiActif()) {
            return 0;
        }

        $verrou = Cache::lock('sync:pousser', 600);

        if (!$verrou->get()) {
            return 0;
        }

        try {
            $envoyes = 0;
            $dernierId = 0;

            while (true) {
                $entrees = DB::table('sync_outbox')
                    ->whereNull('envoye_at')
                    ->where('id', '>', $dernierId)
                    ->orderBy('id')
                    ->limit(100)
                    ->get();

                if ($entrees->isEmpty()) {
                    break;
                }

                $dernierId = $entrees->last()->id;

                $changements = $entrees->map(fn ($entree) => [
                    'table' => $entree->table_name,
                    'id' => $entree->record_id,
                    'ligne' => (array) DB::table($entree->table_name)->where('id', $entree->record_id)->first() ?: null,
                ])->all();

                try {
                    $resultats = $this->appeler('POST', '/api/sync/recevoir', [], ['changements' => $changements])
                        ->throw()
                        ->json('resultats', []);
                } catch (Throwable $e) {
                    // Pas de connexion ou instance distante en erreur : on réessaiera plus tard
                    DB::table('sync_outbox')->whereIn('id', $entrees->pluck('id'))->update([
                        'tentatives' => DB::raw('tentatives + 1'),
                        'derniere_erreur' => mb_substr($e->getMessage(), 0, 1000),
                        'updated_at' => now(),
                    ]);

                    $journal && $journal('Envoi impossible : ' . $e->getMessage());
                    break;
                }

                foreach ($entrees->values() as $index => $entree) {
                    $erreur = $resultats[$index]['erreur'] ?? 'Réponse absente';

                    if ($resultats[$index]['ok'] ?? false) {
                        DB::table('sync_outbox')->where('id', $entree->id)->update(['envoye_at' => now(), 'derniere_erreur' => null, 'updated_at' => now()]);
                        $envoyes++;
                    } else {
                        DB::table('sync_outbox')->where('id', $entree->id)->update([
                            'tentatives' => DB::raw('tentatives + 1'),
                            'derniere_erreur' => mb_substr($erreur, 0, 1000),
                            'updated_at' => now(),
                        ]);
                        Log::warning('Sync : changement refusé', ['table' => $entree->table_name, 'id' => $entree->record_id, 'erreur' => $erreur]);
                    }
                }
            }

            // Purge des envois confirmés depuis plus de 30 jours
            DB::table('sync_outbox')->whereNotNull('envoye_at')->where('envoye_at', '<', now()->subDays(30))->delete();

            return $envoyes;
        } finally {
            $verrou->release();
        }
    }

    /**
     * Applique les changements reçus de l'autre instance. Un résultat par changement, dans le même ordre.
     */
    public function recevoir(array $changements): array
    {
        $resultats = [];

        foreach ($changements as $changement) {
            try {
                $table = $changement['table'] ?? null;
                $id = (string) ($changement['id'] ?? '');
                $ligne = $changement['ligne'] ?? null;

                if (!in_array($table, config('sync.tables_bidirectionnelles'), true) || $id === '') {
                    throw new RuntimeException('Table non autorisée ou identifiant manquant');
                }

                if ($ligne !== null) {
                    if ((string) ($ligne['id'] ?? '') !== $id) {
                        throw new RuntimeException('Identifiant incohérent');
                    }
                    if ($this->entrepriseDeLigne($table, $ligne) !== config('sync.entreprise_id')) {
                        throw new RuntimeException('Ligne hors de l\'entreprise synchronisée');
                    }
                    $this->appliquerLignes($table, [$ligne], true);
                } else {
                    $existante = DB::table($table)->where('id', $id)->first();
                    if ($existante) {
                        if ($this->entrepriseDeLigne($table, (array) $existante) !== config('sync.entreprise_id')) {
                            throw new RuntimeException('Ligne hors de l\'entreprise synchronisée');
                        }
                        DB::table($table)->where('id', $id)->delete();
                    }
                }

                $resultats[] = ['ok' => true];
            } catch (Throwable $e) {
                $resultats[] = ['ok' => false, 'erreur' => $e->getMessage()];
            }
        }

        return $resultats;
    }

    /* ------------------------------------------------------------------
     | Écriture des lignes reçues
     * ------------------------------------------------------------------ */

    /**
     * Insère ou met à jour des lignes sans déclencher d'événement de modèle.
     * Une ligne locale plus récente que la ligne reçue est conservée.
     * Avec $notifier, les notifications d'une nouvelle demande ou d'un changement de statut sont envoyées.
     */
    public function appliquerLignes(string $table, array $lignes, bool $notifier = false): void
    {
        if (empty($lignes)) {
            return;
        }

        $colonnes = array_flip($this->colonnes($table));
        $lignes = array_map(fn ($ligne) => array_intersect_key((array) $ligne, $colonnes), $lignes);
        $existantes = collect();

        // La plus récente gagne
        if (isset($colonnes['updated_at'], $colonnes['id'])) {
            $existantes = DB::table($table)
                ->whereIn('id', array_column($lignes, 'id'))
                ->get()
                ->keyBy('id');

            $lignes = array_values(array_filter($lignes, function ($ligne) use ($existantes) {
                $locale = $existantes[$ligne['id']]->updated_at ?? null;

                return $locale === null || empty($ligne['updated_at']) || (string) $ligne['updated_at'] >= (string) $locale;
            }));
        }

        if (empty($lignes)) {
            return;
        }

        foreach (array_chunk($lignes, 200) as $paquet) {
            if (isset($colonnes['id'])) {
                $miseAJour = array_values(array_diff(array_keys($paquet[0]), ['id']));
                DB::table($table)->upsert($paquet, ['id'], $miseAJour ?: ['id']);
            } else {
                // Table sans id : vidée juste avant par recuperer()
                DB::table($table)->insert($paquet);
            }
        }

        if ($notifier) {
            foreach ($lignes as $ligne) {
                $ancienne = isset($ligne['id'], $existantes[$ligne['id']]) ? (array) $existantes[$ligne['id']] : null;
                app(NotificationsSync::class)->apresReception($table, $ancienne, $ligne);
            }
        }
    }

    /**
     * Entreprise d'une ligne, déduite de config('sync.rattachements').
     */
    public function entrepriseDeLigne(string $table, array $ligne): ?string
    {
        $filtre = config("sync.rattachements.{$table}");

        if (is_string($filtre)) {
            return isset($ligne[$filtre]) ? (string) $ligne[$filtre] : null;
        }

        if (is_array($filtre)) {
            foreach ($filtre as $colonne => $parent) {
                $ligneParente = isset($ligne[$colonne])
                    ? DB::table($parent)->where('id', $ligne[$colonne])->first()
                    : null;

                return $ligneParente ? $this->entrepriseDeLigne($parent, (array) $ligneParente) : null;
            }
        }

        return null;
    }

    /* ------------------------------------------------------------------
     | Signature des échanges
     * ------------------------------------------------------------------ */

    public function signer(string $timestamp, string $methode, array $query, string $corps): string
    {
        $query = array_filter($query, fn ($valeur) => $valeur !== null && $valeur !== '');
        ksort($query);

        return hash_hmac(
            'sha256',
            implode("\n", [$timestamp, strtoupper($methode), http_build_query($query), $corps]),
            (string) config('sync.secret')
        );
    }

    public function signatureValide(Request $request): bool
    {
        $timestamp = (string) $request->header('X-Sync-Timestamp');
        $signature = (string) $request->header('X-Sync-Signature');

        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > (int) config('sync.tolerance')) {
            return false;
        }

        $attendue = $this->signer($timestamp, $request->method(), $request->query(), $request->getContent());

        return hash_equals($attendue, $signature);
    }

    protected function appeler(string $methode, string $chemin, array $query = [], ?array $corps = null, ?string $jeton = null): Response
    {
        $query = array_filter($query, fn ($valeur) => $valeur !== null && $valeur !== '');
        $json = $corps === null ? '' : json_encode($corps, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $timestamp = (string) time();

        $client = Http::withHeaders([
            'Accept' => 'application/json',
            'X-Sync-Timestamp' => $timestamp,
            'X-Sync-Signature' => $this->signer($timestamp, $methode, $query, $json),
        ])->connectTimeout(5)->timeout(60);

        if ($jeton) {
            $client->withToken($jeton);
        }

        $url = config('sync.remote_url') . $chemin . ($query ? '?' . http_build_query($query) : '');

        return $methode === 'GET'
            ? $client->get($url)
            : $client->withBody($json, 'application/json')->post($url);
    }

    /* ------------------------------------------------------------------
     | Outils
     * ------------------------------------------------------------------ */

    protected function colonnes(string $table): array
    {
        return $this->colonnes[$table] ??= Schema::getColumnListing($table);
    }

    /**
     * Une table se lit par différence (depuis le dernier updated_at) si elle a un id et un updated_at.
     */
    protected function incrementale(string $table): bool
    {
        $colonnes = $this->colonnes($table);

        return in_array('id', $colonnes, true) && in_array('updated_at', $colonnes, true);
    }
}
