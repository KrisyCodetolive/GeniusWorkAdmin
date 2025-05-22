<?php

namespace App\Http\Controllers;

use App\Models\Employeur;
use App\Models\PlageHoraire;
use App\Models\JourTravail;
use App\Models\Jour;
use App\Models\Departement;
use App\Services\HoraireService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PlanningController extends Controller
{
    protected $horaireService;

    public function __construct(HoraireService $horaireService)
    {
        $this->horaireService = $horaireService;
    }

    /**
     * Affiche le planning d'un employé
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\View\View
     */
    public function show(Request $request, Employeur $employeur)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $debutSemaine = $date->copy()->startOfWeek();
        
        $planning = $this->horaireService->genererPlanningHebdomadaire($employeur, $debutSemaine);
        $plagesHoraires = PlageHoraire::orderBy('heure_debut')->get();
        
        $semainePrec = $debutSemaine->copy()->subWeek()->format('Y-m-d');
        $semaineSuiv = $debutSemaine->copy()->addWeek()->format('Y-m-d');
        
        return view('app.admin.horaires.planning.show', compact(
            'employeur',
            'planning',
            'plagesHoraires',
            'debutSemaine',
            'semainePrec',
            'semaineSuiv'
        ));
    }

    /**
     * Met à jour le planning d'un employé
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Employeur $employeur)
    {
        $validated = $request->validate([
            'jours' => 'required|array',
            'jours.*.jour_travail_id' => 'required|exists:jour_travails,id',
            'jours.*.plage_horaire_id' => 'nullable|exists:plage_horaires,id',
            'jours.*.est_travaille' => 'boolean',
            'jours.*.commentaire' => 'nullable|string',
        ]);

        foreach ($validated['jours'] as $jourData) {
            $jourTravail = JourTravail::find($jourData['jour_travail_id']);
            
            if (!$jourTravail) {
                continue;
            }
            
            $estTravaille = $jourData['est_travaille'] ?? false;
            $plageHoraireId = $jourData['plage_horaire_id'] ?? null;
            $commentaire = $jourData['commentaire'] ?? null;
            
            // Si le jour n'est pas travaillé ou pas de plage horaire, supprimer l'entrée existante
            if (!$estTravaille || !$plageHoraireId) {
                Jour::where('employeur_id', $employeur->id)
                    ->where('jour_travail_id', $jourTravail->id)
                    ->delete();
                continue;
            }
            
            $plageHoraire = PlageHoraire::find($plageHoraireId);
            
            if (!$plageHoraire) {
                continue;
            }
            
            $this->horaireService->attribuerPlageHoraire(
                $employeur,
                $jourTravail,
                $plageHoraire,
                $estTravaille,
                $commentaire
            );
        }

        return back()->with('success', 'Planning mis à jour avec succès.');
    }

    /**
     * Affiche le planning de tous les employés d'un département
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function departement(Request $request, Departement $departement)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::now();
        $debutSemaine = $date->copy()->startOfWeek();
        
        $employes = $departement->employeurs()->orderBy('nom')->get();
        $plannings = [];
        
        foreach ($employes as $employe) {
            $plannings[$employe->id] = $this->horaireService->genererPlanningHebdomadaire($employe, $debutSemaine);
        }
        
        $semainePrec = $debutSemaine->copy()->subWeek()->format('Y-m-d');
        $semaineSuiv = $debutSemaine->copy()->addWeek()->format('Y-m-d');
        
        return view('app.admin.horaires.planning.departement', compact(
            'departement',
            'employes',
            'plannings',
            'debutSemaine',
            'semainePrec',
            'semaineSuiv'
        ));
    }

    /**
     * Affiche le formulaire pour copier un planning
     *
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\View\View
     */
    public function copieForm(Employeur $employeur)
    {
        $departements = Departement::with('employeurs')->get();
        
        return view('app.admin.horaires.planning.copie', compact('employeur', 'departements'));
    }

    /**
     * Copie le planning d'un employé vers d'autres employés
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Http\RedirectResponse
     */
    public function copier(Request $request, Employeur $employeur)
    {
        $validated = $request->validate([
            'employes' => 'required|array',
            'employes.*' => 'exists:employeurs,id',
        ]);

        $count = 0;
        
        foreach ($validated['employes'] as $employeId) {
            $employe = Employeur::find($employeId);
            
            if ($employe && $employe->id !== $employeur->id) {
                $this->horaireService->copierPlanning($employeur, $employe);
                $count++;
            }
        }

        return redirect()
            ->route('admin.horaires.planning.show', $employeur)
            ->with('success', "Planning copié vers {$count} employé(s) avec succès.");
    }

    /**
     * Affiche le formulaire pour créer un modèle de planning
     *
     * @return \Illuminate\View\View
     */
    public function modeleForm()
    {
        $joursTravailes = JourTravail::all();
        $plagesHoraires = PlageHoraire::orderBy('heure_debut')->get();
        $departements = Departement::with('employeurs')->get();
        
        return view('app.admin.horaires.planning.modele', compact(
            'joursTravailes',
            'plagesHoraires',
            'departements'
        ));
    }

    /**
     * Applique un modèle de planning à plusieurs employés
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function appliquerModele(Request $request)
    {
        $validated = $request->validate([
            'plages' => 'required|array',
            'plages.*' => 'nullable|exists:plage_horaires,id',
            'employes' => 'required|array',
            'employes.*' => 'exists:employeurs,id',
        ]);

        $count = $this->horaireService->appliquerModele(
            $validated['plages'],
            $validated['employes']
        );

        return redirect()
            ->route('admin.horaires.planning.modele')
            ->with('success', "Modèle appliqué à {$count} employé(s) avec succès.");
    }
}
