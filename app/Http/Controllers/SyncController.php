<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Sync\SyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function __construct(protected SyncService $sync)
    {
    }

    /**
     * Connexion de l'instance locale avec un compte SuperAdmin du backoffice.
     * Renvoie un jeton limité à la lecture des données ; les jetons précédents de l'appareil sont révoqués.
     */
    public function connexion(Request $request)
    {
        $this->verifierCloud($request);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'appareil' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)
            || !$user->isSuperAdmin() || $user->statut !== 'actif') {
            Log::warning('Sync : connexion refusée', ['email' => $validated['email'], 'ip' => $request->ip()]);

            return response()->json(['message' => 'Identifiants invalides ou compte non SuperAdmin.'], 401);
        }

        $nom = 'sync:' . ($validated['appareil'] ?? 'instance-locale');
        $user->tokens()->where('name', $nom)->delete();

        Log::info('Sync : connexion de l\'instance locale', ['user' => $user->id, 'appareil' => $nom, 'ip' => $request->ip()]);

        return response()->json([
            'token' => $user->createToken($nom, [config('sync.ability')])->plainTextToken,
        ]);
    }

    /**
     * Renvoie une page des lignes d'une table modifiées depuis "since" (lignes supprimées comprises).
     * Réservé à un compte SuperAdmin connecté via /api/sync/connexion.
     */
    public function changes(Request $request)
    {
        $this->verifierCloud($request);

        // Seul un jeton obtenu par /api/sync/connexion convient (un jeton "*" d'une autre application est refusé)
        $user = $request->user();
        $abilities = $user?->currentAccessToken()?->abilities ?? [];
        abort_unless(
            $user && in_array(config('sync.ability'), $abilities, true) && $user->isSuperAdmin() && $user->statut === 'actif',
            403
        );

        $validated = $request->validate([
            'table' => ['required', 'string', 'in:' . implode(',', $this->sync->tablesCopiees())],
            'since' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json(
            $this->sync->lirePage($validated['table'], $validated['since'] ?? null, (int) ($validated['page'] ?? 1))
        );
    }

    /**
     * Reçoit les changements de l'autre instance (création, changement de statut, suppression).
     */
    public function recevoir(Request $request)
    {
        $this->verifierIp($request);

        abort_unless($this->sync->actif(), 404);
        abort_unless($this->sync->signatureValide($request), 401, 'Signature invalide');

        $validated = $request->validate([
            'changements' => ['required', 'array', 'max:500'],
            'changements.*.table' => ['required', 'string'],
            'changements.*.id' => ['required', 'string'],
            'changements.*.ligne' => ['nullable', 'array'],
        ]);

        return response()->json([
            'resultats' => $this->sync->recevoir($validated['changements']),
        ]);
    }

    protected function verifierCloud(Request $request): void
    {
        $this->verifierIp($request);

        abort_unless(config('sync.role') === 'cloud', 404);
    }

    protected function verifierIp(Request $request): void
    {
        $ips = config('sync.allowed_ips');

        abort_if($ips && !in_array($request->ip(), $ips, true), 403);
    }
}
