<?php

namespace Tests\Feature;

use App\Models\Conge;
use App\Models\Permutation;
use App\Models\Presence;
use App\Models\User;
use App\Notifications\CongeStatusNotification;
use App\Notifications\DepenseNotification;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use App\Services\Sync\SyncService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Synchronisation cloud <-> instance locale, sur un schéma réduit (seules les tables utiles sont créées).
 */
class SyncTest extends TestCase
{
    protected const ENTREPRISE = 'ent-a';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sync.role' => 'cloud',
            'sync.remote_url' => 'https://local.test',
            'sync.secret' => 'secret-de-test',
            'sync.entreprise_id' => self::ENTREPRISE,
            'sync.allowed_ips' => [],
            'sync.limite' => 2,
            'queue.default' => 'sync',
        ]);

        foreach (['employeurs', 'presences', 'permutations', 'conges', 'demandes_depense', 'validations_depense', 'sync_outbox', 'sync_curseurs'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('employeurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('presences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employeur_id');
            $table->string('type')->nullable();
            $table->string('statut')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        (include database_path('migrations/2026_10_15_100000_create_sync_tables.php'))->up();

        DB::table('employeurs')->insert([
            ['id' => 'emp-a', 'entreprise_id' => self::ENTREPRISE],
            ['id' => 'emp-b', 'entreprise_id' => 'ent-b'],
        ]);
    }

    protected function presence(string $id, string $employeur, string $updatedAt, array $autres = []): array
    {
        return array_merge([
            'id' => $id,
            'employeur_id' => $employeur,
            'type' => 'entree',
            'statut' => 'present',
            'created_at' => $updatedAt,
            'updated_at' => $updatedAt,
            'deleted_at' => null,
        ], $autres);
    }

    protected function enTetesSignes(string $methode, array $query, string $corps): array
    {
        $timestamp = (string) time();

        return [
            'X-Sync-Timestamp' => $timestamp,
            'X-Sync-Signature' => app(SyncService::class)->signer($timestamp, $methode, $query, $corps),
            'Accept' => 'application/json',
        ];
    }

    protected function creerTablesComptes(): void
    {
        foreach (['users', 'personal_access_tokens'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id')->nullable();
            $table->uuid('employeur_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->string('statut')->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        foreach (['super_admin' => 'admin@test.ci', 'entreprise' => 'rh@test.ci'] as $role => $email) {
            DB::table('users')->insert([
                'id' => (string) Str::uuid(),
                'email' => $email,
                'password' => Hash::make('motdepasse'),
                'role' => $role,
                'statut' => 'actif',
            ]);
        }
    }

    public function test_lecture_de_toute_la_base_par_date_avec_pagination(): void
    {
        DB::table('presences')->insert([
            $this->presence('p1', 'emp-a', '2026-10-01 08:00:00'),
            $this->presence('p2', 'emp-a', '2026-10-02 08:00:00', ['deleted_at' => '2026-10-02 09:00:00']),
            $this->presence('p3', 'emp-b', '2026-10-03 08:00:00'),
        ]);

        $sync = app(SyncService::class);

        // Toutes les entreprises, lignes supprimées comprises
        $page1 = $sync->lirePage('presences', '2026-10-02 08:00:00', 1);
        $this->assertSame(['p2', 'p3'], array_column($page1['lignes'], 'id'));
        $this->assertTrue($page1['suite']);
        $this->assertSame([], $sync->lirePage('presences', '2026-10-02 08:00:00', 2)['lignes']);

        // Tables techniques jamais copiées
        $this->assertContains('presences', $sync->tablesCopiees());
        $this->assertNotContains('sync_outbox', $sync->tablesCopiees());
    }

    public function test_lecture_reservee_a_un_superadmin_connecte(): void
    {
        $this->creerTablesComptes();
        $url = '/api/sync/changes?table=presences';

        $this->getJson($url)->assertStatus(401);

        // Mauvais mot de passe ou compte non SuperAdmin : refusé
        $this->postJson('/api/sync/connexion', ['email' => 'admin@test.ci', 'password' => 'faux'])->assertStatus(401);
        $this->postJson('/api/sync/connexion', ['email' => 'rh@test.ci', 'password' => 'motdepasse'])->assertStatus(401);

        // Jeton SuperAdmin sans la capacité de lecture : refusé
        $autreJeton = User::where('email', 'admin@test.ci')->first()->createToken('autre')->plainTextToken;
        $this->withToken($autreJeton)->getJson($url)->assertStatus(403);
        $this->app['auth']->forgetGuards();

        $jeton = $this->postJson('/api/sync/connexion', ['email' => 'admin@test.ci', 'password' => 'motdepasse'])
            ->assertOk()
            ->json('token');

        $this->app['auth']->forgetGuards();
        $this->withToken($jeton)->getJson($url)->assertOk()->assertJsonPath('table', 'presences');
        $this->withToken($jeton)->getJson('/api/sync/changes?table=personal_access_tokens')->assertStatus(422);
    }

    public function test_recuperation_locale_avec_le_jeton_du_backoffice(): void
    {
        config(['sync.role' => 'local']);
        Storage::fake('local');
        $sync = app(SyncService::class);

        // Sans connexion préalable, la récupération est bloquée
        try {
            $sync->recuperer();
            $this->fail('La récupération aurait dû être refusée sans connexion.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('sync:connexion', $e->getMessage());
        }

        Http::fake([
            'local.test/api/sync/connexion' => Http::response(['token' => 'jeton-test']),
            'local.test/api/sync/changes?*' => function (ClientRequest $requete) {
                $lignes = str_contains($requete->url(), 'table=presences')
                    ? [$this->presence('p1', 'emp-b', '2026-10-01 08:00:00')]
                    : [];

                return Http::response(['lignes' => $lignes, 'suite' => false]);
            },
        ]);

        $sync->connecter('admin@test.ci', 'motdepasse');
        $resultat = $sync->recuperer();

        $this->assertSame(1, $resultat['presences']);
        $this->assertTrue(DB::table('presences')->where('id', 'p1')->exists());
        $this->assertSame('2026-10-01 08:00:00', DB::table('sync_curseurs')->where('table_name', 'presences')->value('since'));
        Http::assertSent(fn (ClientRequest $requete) => $requete->hasHeader('Authorization', 'Bearer jeton-test'));
        // Une ligne reçue n'est jamais renvoyée
        $this->assertSame(0, DB::table('sync_outbox')->count());
    }

    public function test_reception_applique_la_plus_recente_et_refuse_une_autre_entreprise(): void
    {
        DB::table('presences')->insert($this->presence('p1', 'emp-a', '2026-10-05 10:00:00', ['type' => 'sortie']));

        $changements = [
            // Plus ancienne que la ligne existante : ignorée
            ['table' => 'presences', 'id' => 'p1', 'ligne' => $this->presence('p1', 'emp-a', '2026-10-05 09:00:00', ['type' => 'entree'])],
            // Nouvelle ligne de l'entreprise : insérée
            ['table' => 'presences', 'id' => 'p2', 'ligne' => $this->presence('p2', 'emp-a', '2026-10-05 09:00:00')],
            // Autre entreprise : refusée
            ['table' => 'presences', 'id' => 'p3', 'ligne' => $this->presence('p3', 'emp-b', '2026-10-05 09:00:00')],
            // Table non bidirectionnelle : refusée
            ['table' => 'employeurs', 'id' => 'emp-c', 'ligne' => ['id' => 'emp-c', 'entreprise_id' => self::ENTREPRISE]],
        ];
        $corps = json_encode(['changements' => $changements]);

        $reponse = $this->call('POST', '/api/sync/recevoir', [], [], [], $this->serveur($this->enTetesSignes('POST', [], $corps)), $corps);

        $reponse->assertOk();
        $this->assertSame([true, true, false, false], array_column($reponse->json('resultats'), 'ok'));
        $this->assertSame('sortie', DB::table('presences')->where('id', 'p1')->value('type'));
        $this->assertTrue(DB::table('presences')->where('id', 'p2')->exists());
        $this->assertFalse(DB::table('presences')->where('id', 'p3')->exists());
        $this->assertFalse(DB::table('employeurs')->where('id', 'emp-c')->exists());
    }

    public function test_un_pointage_est_mis_en_file_puis_envoye_signe_a_l_autre_instance(): void
    {
        $presence = Presence::create(['employeur_id' => 'emp-a', 'type' => 'entree', 'statut' => 'present']);
        $presence->update(['type' => 'sortie']);
        Presence::create(['employeur_id' => 'emp-b', 'type' => 'entree']);

        // Une seule entrée pour p1 malgré deux enregistrements, rien pour l'autre entreprise
        $this->assertSame(1, DB::table('sync_outbox')->count());

        $sync = app(SyncService::class);
        Http::fake(['local.test/*' => Http::response(['resultats' => [['ok' => true]]])]);

        $this->assertSame(1, $sync->pousser());

        Http::assertSent(function (ClientRequest $requete) use ($sync, $presence) {
            $serveur = Request::create($requete->url(), 'POST', [], [], [], $this->serveur($requete->headers()), $requete->body());
            $changement = $requete->data()['changements'][0];

            return $requete->url() === 'https://local.test/api/sync/recevoir'
                && $sync->signatureValide($serveur)
                && $changement['id'] === $presence->id
                && $changement['ligne']['type'] === 'sortie';
        });

        $this->assertNotNull(DB::table('sync_outbox')->value('envoye_at'));
    }

    public function test_sans_connexion_le_changement_reste_en_attente(): void
    {
        Presence::create(['employeur_id' => 'emp-a', 'type' => 'entree']);

        Http::fake(fn () => throw new ConnectionException('Connexion impossible'));

        $this->assertSame(0, app(SyncService::class)->pousser());

        $entree = DB::table('sync_outbox')->first();
        $this->assertNull($entree->envoye_at);
        $this->assertSame(1, (int) $entree->tentatives);
        $this->assertStringContainsString('Connexion impossible', $entree->derniere_erreur);
    }

    public function test_une_suppression_definitive_est_transmise(): void
    {
        $presence = Presence::create(['employeur_id' => 'emp-a', 'type' => 'entree']);
        DB::table('sync_outbox')->update(['envoye_at' => now()]);

        $presence->forceDelete();

        Http::fake(['local.test/*' => Http::response(['resultats' => [['ok' => true]]])]);
        app(SyncService::class)->pousser();

        Http::assertSent(fn (ClientRequest $requete) => $requete->data()['changements'][0]['ligne'] === null);

        // Côté récepteur : la ligne est supprimée
        DB::table('presences')->insert($this->presence('p9', 'emp-a', '2026-10-05 09:00:00'));
        app(SyncService::class)->recevoir([['table' => 'presences', 'id' => 'p9', 'ligne' => null]]);
        $this->assertFalse(DB::table('presences')->where('id', 'p9')->exists());
    }

    public function test_une_permutation_est_envoyee_a_l_autre_instance(): void
    {
        Schema::create('permutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employeur_1_id');
            $table->uuid('employeur_2_id');
            $table->string('statut')->default('en_attente');
            $table->timestamps();
            $table->softDeletes();
        });

        $permutation = Permutation::create(['employeur_1_id' => 'emp-a', 'employeur_2_id' => 'emp-a']);
        $permutation->update(['statut' => 'approuve']);
        Permutation::create(['employeur_1_id' => 'emp-b', 'employeur_2_id' => 'emp-b']);

        $this->assertSame(
            [['permutations', $permutation->id]],
            DB::table('sync_outbox')->get(['table_name', 'record_id'])->map(fn ($e) => [$e->table_name, $e->record_id])->all()
        );
    }

    public function test_un_changement_de_statut_de_conge_recu_notifie_l_employe_une_seule_fois(): void
    {
        $this->creerTablesComptes();
        Schema::create('conges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employeur_id');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('statut');
            $table->timestamps();
            $table->softDeletes();
        });
        DB::table('users')->where('email', 'rh@test.ci')->update(['employeur_id' => 'emp-a']);
        Notification::fake();

        $conge = fn (string $statut, string $date) => [
            'id' => 'c1', 'employeur_id' => 'emp-a', 'statut' => $statut,
            'date_debut' => '2026-11-02', 'date_fin' => '2026-11-06',
            'created_at' => $date, 'updated_at' => $date, 'deleted_at' => null,
        ];
        $sync = app(SyncService::class);

        // Nouvelle demande en attente : aucune notification (comme sur place)
        $sync->recevoir([['table' => 'conges', 'id' => 'c1', 'ligne' => $conge('en_attente', '2026-10-16 08:00:00')]]);
        Notification::assertNothingSent();

        // Approuvée : l'employé est prévenu, une seule fois même si la ligne arrive deux fois (webhook + récupération)
        $sync->recevoir([['table' => 'conges', 'id' => 'c1', 'ligne' => $conge('approuve', '2026-10-16 09:00:00')]]);
        $sync->recevoir([['table' => 'conges', 'id' => 'c1', 'ligne' => $conge('approuve', '2026-10-16 09:00:00')]]);

        $employe = User::where('email', 'rh@test.ci')->first();
        Notification::assertSentToTimes($employe, CongeStatusNotification::class, 1);
    }

    public function test_une_etape_de_depense_recue_notifie_les_memes_destinataires(): void
    {
        $this->creerTablesComptes();
        Schema::create('demandes_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->string('reference')->nullable();
            $table->decimal('montant', 15, 2)->default(0);
            $table->uuid('demandeur_id')->nullable();
            $table->uuid('cree_par_user_id');
            $table->string('statut');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('validations_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('demande_depense_id');
            $table->string('etape');
            $table->string('decision');
            $table->uuid('user_id');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });

        $demandeur = User::where('email', 'rh@test.ci')->first();
        $comptable = User::create(['email' => 'compta@test.ci', 'password' => 'x', 'role' => 'comptable', 'statut' => 'actif', 'entreprise_id' => self::ENTREPRISE]);
        DB::table('demandes_depense')->insert([
            'id' => 'd1', 'entreprise_id' => self::ENTREPRISE, 'reference' => 'DEP-1', 'montant' => 1000,
            'cree_par_user_id' => $demandeur->id, 'statut' => 'en_attente_comptable',
            'created_at' => '2026-10-16 08:00:00', 'updated_at' => '2026-10-16 08:00:00',
        ]);
        Notification::fake();

        $validation = fn (string $id, string $etape, string $decision) => [
            'id' => $id, 'demande_depense_id' => 'd1', 'etape' => $etape, 'decision' => $decision,
            'user_id' => $demandeur->id, 'commentaire' => 'Pièce manquante',
            'created_at' => '2026-10-16 08:00:00', 'updated_at' => '2026-10-16 08:00:00',
        ];
        $sync = app(SyncService::class);

        // Soumission : la comptabilité doit valider
        $sync->recevoir([['table' => 'validations_depense', 'id' => 'v1', 'ligne' => $validation('v1', 'soumission', 'effectue')]]);
        Notification::assertSentTo($comptable, DepenseNotification::class);
        Notification::assertNotSentTo($demandeur, DepenseNotification::class);

        // Rejet : le demandeur est prévenu ; une validation déjà reçue ne renotifie pas
        $sync->recevoir([['table' => 'validations_depense', 'id' => 'v2', 'ligne' => $validation('v2', 'comptabilite', 'rejete')]]);
        $sync->recevoir([['table' => 'validations_depense', 'id' => 'v2', 'ligne' => $validation('v2', 'comptabilite', 'rejete')]]);
        Notification::assertSentToTimes($demandeur, DepenseNotification::class, 1);
    }

    public function test_l_instance_locale_garde_seulement_le_canal_interne_des_notifications_de_demande(): void
    {
        $user = new User();
        $notification = new CongeStatusNotification(new Conge());

        config(['sync.role' => 'local']);
        $this->assertFalse(Event::until(new NotificationSending($user, $notification, 'mail')));
        $this->assertNull(Event::until(new NotificationSending($user, $notification, 'database')));

        // Le cloud envoie les e-mails
        config(['sync.role' => 'cloud']);
        $this->assertNull(Event::until(new NotificationSending($user, $notification, 'mail')));
    }

    /**
     * En-têtes HTTP au format $_SERVER.
     */
    protected function serveur(array $entetes): array
    {
        $serveur = ['CONTENT_TYPE' => 'application/json'];

        foreach ($entetes as $nom => $valeur) {
            $serveur['HTTP_' . strtoupper(str_replace('-', '_', $nom))] = is_array($valeur) ? $valeur[0] : $valeur;
        }

        return $serveur;
    }
}
