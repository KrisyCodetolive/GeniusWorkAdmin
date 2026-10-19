<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Employeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Accès au portail des dépenses, sans connexion : on choisit son département puis son nom.
 * Le compte ouvert ne sert qu'au portail (voir RestreindreSessionDepartement).
 * Regroupe aussi les ressources PWA (manifeste, service worker, page hors ligne).
 */
class DepenseAccesController extends Controller
{
    public const SESSION_DEPARTEMENT = 'depenses_departement_id';

    public const SESSION_DEMANDEUR = 'depenses_demandeur_id';

    public function acces(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('employe.depenses.index');
        }

        // Un département n'est proposé que s'il a au moins un employé actif à nommer.
        $departements = Departement::withoutGlobalScopes()
            ->with('entreprise:id,nom')
            ->actif()
            ->whereHas('employeurs', fn ($employes) => $employes->withoutGlobalScopes()->where('statut', 'actif'))
            ->orderBy('nom')
            ->get(['id', 'nom', 'entreprise_id']);

        $membres = Employeur::withoutGlobalScopes()
            ->whereIn('departement_id', $departements->pluck('id'))
            ->where('statut', 'actif')
            ->orderBy('nom')->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'departement_id'])
            ->groupBy('departement_id')
            ->map(fn ($groupe) => $groupe->map(fn ($employe) => ['id' => $employe->id, 'nom' => trim($employe->prenom.' '.$employe->nom)])->values());

        return view('employe.depenses.acces', [
            'membres' => $membres,
            'departements' => $departements,
            'plusieursEntreprises' => $departements->pluck('entreprise_id')->unique()->count() > 1,
        ]);
    }

    public function verifier(Request $request)
    {
        $data = $request->validate([
            'departement_id' => ['required', 'string'],
            'demandeur_id' => ['required', 'string'],
        ], [
            'demandeur_id.required' => 'Choisissez votre nom.',
        ]);

        $departement = Departement::withoutGlobalScopes()->actif()->find($data['departement_id']);
        $demandeur = $departement?->membresActifs()->whereKey($data['demandeur_id'])->first();

        if (! $demandeur) {
            return back()
                ->withInput($request->only('departement_id'))
                ->withErrors(['demandeur_id' => 'Choisissez votre nom dans la liste du département.']);
        }

        Auth::login($departement->compteDepenses());
        $request->session()->regenerate();
        $request->session()->put([
            self::SESSION_DEPARTEMENT => $departement->id,
            self::SESSION_DEMANDEUR => $demandeur->id,
        ]);

        return redirect()->intended(route('employe.depenses.index'));
    }

    public function quitter(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employe.depenses.acces');
    }

    public function manifeste()
    {
        $icone = fn (int $taille) => [
            'src' => asset("images/depenses/icon-{$taille}.png"),
            'sizes' => "{$taille}x{$taille}",
            'type' => 'image/png',
            'purpose' => 'any',
        ];

        return response()->json([
            'id' => '/depense',
            'name' => 'GeniusWork — Demandes de dépense',
            'short_name' => 'Dépenses',
            'description' => 'Faites une demande d\'argent et suivez son avancement.',
            'lang' => 'fr',
            'start_url' => '/depense',
            'scope' => '/depense',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#f6f7fb',
            'theme_color' => '#3a5faa',
            'icons' => [$icone(192), $icone(512)],
        ])->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Servi sous /depense/ pour pouvoir contrôler ce périmètre ; le header élargit le scope autorisé
     * jusqu'à /depense (sans slash final) afin que la page d'accueil du portail soit couverte.
     */
    public function serviceWorker()
    {
        return response()->file(public_path('depenses-sw.js'), [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/depense',
            'Cache-Control' => 'no-cache',
        ]);
    }

    public function horsLigne()
    {
        return view('employe.depenses.hors-ligne');
    }
}
