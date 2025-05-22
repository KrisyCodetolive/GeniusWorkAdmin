<?php

namespace App\Http\Controllers;

use App\Models\PlageHoraire;
use App\Services\HoraireService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlageHoraireController extends Controller
{
    protected $horaireService;

    public function __construct(HoraireService $horaireService)
    {
        $this->horaireService = $horaireService;
    }

    /**
     * Affiche la liste des plages horaires
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $plagesStandard = $this->horaireService->plagesHorairesParType('standard');
        $plagesSpeciales = $this->horaireService->plagesHorairesParType('special');
        $plagesPause = $this->horaireService->plagesHorairesDePause();

        return view('app.admin.horaires.plages.index', compact(
            'plagesStandard',
            'plagesSpeciales',
            'plagesPause'
        ));
    }

    /**
     * Affiche le formulaire de création d'une plage horaire
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('app.admin.horaires.plages.create');
    }

    /**
     * Enregistre une nouvelle plage horaire
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7',
            'type' => 'required|in:standard,special',
            'est_pause' => 'boolean',
            'duree_max_minutes' => 'nullable|integer|min:1',
        ]);

        // Convertir les heures en objets Carbon
        $validated['heure_debut'] = Carbon::createFromFormat('H:i', $validated['heure_debut']);
        $validated['heure_fin'] = Carbon::createFromFormat('H:i', $validated['heure_fin']);

        // Vérifier le chevauchement
        if ($this->horaireService->verifierChevauchement($validated['heure_debut'], $validated['heure_fin'])) {
            return back()
                ->withInput()
                ->withErrors(['heure_debut' => 'Cette plage horaire chevauche une plage existante.']);
        }

        $plageHoraire = $this->horaireService->creerPlageHoraire($validated);

        return redirect()
            ->route('admin.horaires.plages.index')
            ->with('success', 'Plage horaire créée avec succès.');
    }

    /**
     * Affiche une plage horaire spécifique
     *
     * @param  \App\Models\PlageHoraire  $plageHoraire
     * @return \Illuminate\View\View
     */
    public function show(PlageHoraire $plageHoraire)
    {
        $employes = $plageHoraire->jours()
            ->with('employeur')
            ->get()
            ->pluck('employeur')
            ->unique('id');

        return view('app.admin.horaires.plages.show', compact('plageHoraire', 'employes'));
    }

    /**
     * Affiche le formulaire d'édition d'une plage horaire
     *
     * @param  \App\Models\PlageHoraire  $plageHoraire
     * @return \Illuminate\View\View
     */
    public function edit(PlageHoraire $plageHoraire)
    {
        return view('app.admin.horaires.plages.edit', compact('plageHoraire'));
    }

    /**
     * Met à jour une plage horaire
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PlageHoraire  $plageHoraire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PlageHoraire $plageHoraire)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7',
            'type' => 'required|in:standard,special',
            'est_pause' => 'boolean',
            'duree_max_minutes' => 'nullable|integer|min:1',
        ]);

        // Convertir les heures en objets Carbon
        $validated['heure_debut'] = Carbon::createFromFormat('H:i', $validated['heure_debut']);
        $validated['heure_fin'] = Carbon::createFromFormat('H:i', $validated['heure_fin']);

        // Vérifier le chevauchement
        if ($this->horaireService->verifierChevauchement(
            $validated['heure_debut'], 
            $validated['heure_fin'], 
            $plageHoraire->id
        )) {
            return back()
                ->withInput()
                ->withErrors(['heure_debut' => 'Cette plage horaire chevauche une plage existante.']);
        }

        $this->horaireService->mettreAJourPlageHoraire($plageHoraire, $validated);

        return redirect()
            ->route('admin.horaires.plages.index')
            ->with('success', 'Plage horaire mise à jour avec succès.');
    }

    /**
     * Supprime une plage horaire
     *
     * @param  \App\Models\PlageHoraire  $plageHoraire
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PlageHoraire $plageHoraire)
    {
        try {
            $this->horaireService->supprimerPlageHoraire($plageHoraire);
            return redirect()
                ->route('admin.horaires.plages.index')
                ->with('success', 'Plage horaire supprimée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Affiche le formulaire de fusion de plages horaires
     *
     * @return \Illuminate\View\View
     */
    public function fusionForm()
    {
        $plagesHoraires = $this->horaireService->toutesLesPlagesHoraires();
        
        return view('app.admin.horaires.plages.fusion', compact('plagesHoraires'));
    }

    /**
     * Fusionne deux plages horaires
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fusion(Request $request)
    {
        $validated = $request->validate([
            'plage_horaire_1' => 'required|exists:plage_horaires,id',
            'plage_horaire_2' => 'required|exists:plage_horaires,id|different:plage_horaire_1',
        ]);

        $plageHoraire1 = PlageHoraire::findOrFail($validated['plage_horaire_1']);
        $plageHoraire2 = PlageHoraire::findOrFail($validated['plage_horaire_2']);

        $this->horaireService->fusionnerPlagesHoraires($plageHoraire1, $plageHoraire2);

        return redirect()
            ->route('admin.horaires.plages.index')
            ->with('success', 'Plages horaires fusionnées avec succès.');
    }
}
