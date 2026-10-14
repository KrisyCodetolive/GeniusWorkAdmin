<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\DemandeDepense;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SyncController extends Controller
{
    // Tables synchronisées (paie et paiements exclus)
    protected const MODELES = [
        'presences' => Presence::class,
        'conges' => Conge::class,
        'demandes_depense' => DemandeDepense::class,
    ];

    // Nombre maximum de lignes par table et par appel
    protected const LIMITE = 1000;

    /**
     * Renvoie les lignes modifiées depuis la date "since" (y compris supprimées).
     * Le client rappelle l'endpoint avec le plus grand updated_at reçu tant que la réponse est pleine.
     */
    public function changes(Request $request)
    {
        if (!in_array($request->ip(), config('sync.allowed_ips'), true)) {
            abort(403);
        }

        $request->validate([
            'since' => 'nullable|date',
        ]);

        $since = $request->filled('since')
            ? Carbon::parse($request->query('since'))
            : Carbon::createFromTimestamp(0);

        $data = [];
        foreach (self::MODELES as $nom => $modele) {
            $data[$nom] = $modele::withTrashed()
                ->where('updated_at', '>', $since)
                ->orderBy('updated_at')
                ->limit(self::LIMITE)
                ->get();
        }

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'limit' => self::LIMITE,
            'data' => $data,
        ]);
    }
}
