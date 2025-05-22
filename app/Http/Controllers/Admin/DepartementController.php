<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Departement;
use App\Models\Filiale;
use App\Models\Employeur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DepartementController extends Controller
{
    /**
     * Affiche la liste des départements de l'entreprise.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $entreprise = Auth::user()->entreprise;
        $departements = Departement::whereHas('filiale', function ($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->with('filiale')->orderBy('nom')->paginate(10);
        
        return view('app.admin.departements.index', [
            'entreprise' => $entreprise,
            'departements' => $departements
        ]);
    }

    /**
     * Affiche le formulaire de création d'un département.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $entreprise = Auth::user()->entreprise;
        $filiales = $entreprise->filiales()->where('statut', 'actif')->orderBy('nom')->get();
        $responsables = $entreprise->employeurs()->where('statut', 'actif')->orderBy('nom')->get();
        
        return view('app.admin.departements.create', [
            'entreprise' => $entreprise,
            'filiales' => $filiales,
            'responsables' => $responsables
        ]);
    }

    /**
     * Enregistre un nouveau département.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $entreprise = Auth::user()->entreprise;
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('departements')->where(function ($query) use ($entreprise) {
                    return $query->whereHas('filiale', function ($q) use ($entreprise) {
                        $q->where('entreprise_id', $entreprise->id);
                    });
                })
            ],
            'filiale_id' => [
                'required',
                Rule::exists('filiales', 'id')->where(function ($query) use ($entreprise) {
                    return $query->where('entreprise_id', $entreprise->id);
                })
            ],
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:employeurs,id',
            'departement_parent_id' => [
                'nullable',
                Rule::exists('departements', 'id')->where(function ($query) use ($entreprise) {
                    return $query->whereHas('filiale', function ($q) use ($entreprise) {
                        $q->where('entreprise_id', $entreprise->id);
                    });
                })
            ],
            'budget' => 'nullable|numeric|min:0',
            'objectifs' => 'nullable|string',
            'limite_employes' => 'nullable|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Générer un code si non fourni
        $code = $request->code;
        if (empty($code)) {
            $nom = preg_replace('/[^A-Za-z0-9]/', '', $request->nom);
            $code = strtoupper(substr($nom, 0, 3) . rand(100, 999));
        }
        
        // Préparer la configuration
        $configuration = [
            'budget' => $request->budget,
            'objectifs' => $request->objectifs,
            'limite_employes' => $request->limite_employes
        ];
        
        // Créer le département
        $departement = new Departement([
            'filiale_id' => $request->filiale_id,
            'departement_parent_id' => $request->departement_parent_id,
            'nom' => $request->nom,
            'code' => $code,
            'description' => $request->description,
            'configuration' => $configuration,
            'statut' => 'actif'
        ]);
        
        $departement->save();
        
        // Assigner le responsable si fourni
        if ($request->responsable_id) {
            $responsable = Employeur::findOrFail($request->responsable_id);
            $departement->assignerResponsable($responsable);
        }
        
        return redirect()->route('admin.departements.index')
            ->with('success', 'Le département a été créé avec succès.');
    }

    /**
     * Affiche les détails d'un département.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function show(Departement $departement)
    {
        $this->authorize('view', $departement);
        
        $sousDepartements = $departement->sousDepartements()->withCount('employeurs')->get();
        $employeurs = $departement->employeurs()->paginate(10);
        $responsable = $departement->responsable;
        
        return view('app.admin.departements.show', [
            'departement' => $departement,
            'sousDepartements' => $sousDepartements,
            'employeurs' => $employeurs,
            'responsable' => $responsable
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un département.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function edit(Departement $departement)
    {
        $this->authorize('update', $departement);
        
        $entreprise = Auth::user()->entreprise;
        $filiales = $entreprise->filiales()->where('statut', 'actif')->orderBy('nom')->get();
        $responsables = $entreprise->employeurs()->where('statut', 'actif')->orderBy('nom')->get();
        
        // Récupérer les départements qui peuvent être parents (pas lui-même ni ses enfants)
        $departementsParentsPossibles = Departement::whereHas('filiale', function ($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->where('id', '!=', $departement->id)
          ->whereNotIn('id', $departement->getAllSousDepartementsIds())
          ->orderBy('nom')
          ->get();
        
        return view('app.admin.departements.edit', [
            'departement' => $departement,
            'entreprise' => $entreprise,
            'filiales' => $filiales,
            'responsables' => $responsables,
            'departementsParentsPossibles' => $departementsParentsPossibles
        ]);
    }

    /**
     * Met à jour un département.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Departement $departement)
    {
        $this->authorize('update', $departement);
        
        $entreprise = Auth::user()->entreprise;
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('departements')->where(function ($query) use ($entreprise, $departement) {
                    return $query->whereHas('filiale', function ($q) use ($entreprise) {
                        $q->where('entreprise_id', $entreprise->id);
                    })->where('id', '!=', $departement->id);
                })
            ],
            'filiale_id' => [
                'required',
                Rule::exists('filiales', 'id')->where(function ($query) use ($entreprise) {
                    return $query->where('entreprise_id', $entreprise->id);
                })
            ],
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:employeurs,id',
            'departement_parent_id' => [
                'nullable',
                Rule::exists('departements', 'id')->where(function ($query) use ($entreprise, $departement) {
                    return $query->whereHas('filiale', function ($q) use ($entreprise) {
                        $q->where('entreprise_id', $entreprise->id);
                    })->where('id', '!=', $departement->id)
                      ->whereNotIn('id', $departement->getAllSousDepartementsIds());
                })
            ],
            'budget' => 'nullable|numeric|min:0',
            'objectifs' => 'nullable|string',
            'limite_employes' => 'nullable|integer|min:1',
            'statut' => 'required|in:actif,inactif'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Préparer la configuration
        $configuration = $departement->configuration ?? [];
        $configuration['budget'] = $request->budget;
        $configuration['objectifs'] = $request->objectifs;
        $configuration['limite_employes'] = $request->limite_employes;
        
        // Mettre à jour le département
        $departement->update([
            'filiale_id' => $request->filiale_id,
            'departement_parent_id' => $request->departement_parent_id,
            'nom' => $request->nom,
            'code' => $request->code,
            'description' => $request->description,
            'configuration' => $configuration,
            'statut' => $request->statut
        ]);
        
        // Gérer le responsable
        if ($request->responsable_id) {
            $responsable = Employeur::findOrFail($request->responsable_id);
            
            // Si le responsable a changé, mettre à jour
            if (!$departement->responsable || $departement->responsable->id != $request->responsable_id) {
                $departement->assignerResponsable($responsable);
            }
        } elseif ($departement->responsable && !$request->responsable_id) {
            // Si le responsable a été retiré
            $departement->retirerResponsable();
        }
        
        return redirect()->route('admin.departements.show', $departement)
            ->with('success', 'Le département a été mis à jour avec succès.');
    }

    /**
     * Supprime un département.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Departement $departement)
    {
        $this->authorize('delete', $departement);
        
        // Vérifier si le département a des employés ou des sous-départements
        if ($departement->employeurs()->exists() || $departement->sousDepartements()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce département car il contient des employés ou des sous-départements.');
        }
        
        $departement->delete();
        
        return redirect()->route('admin.departements.index')
            ->with('success', 'Le département a été supprimé avec succès.');
    }

    /**
     * Affiche la structure hiérarchique des départements.
     *
     * @return \Illuminate\View\View
     */
    public function hierarchie()
    {
        $entreprise = Auth::user()->entreprise;
        
        // Récupérer les départements racines (sans parent)
        $departementsRacines = Departement::whereHas('filiale', function ($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->whereNull('departement_parent_id')
          ->with('sousDepartements')
          ->orderBy('nom')
          ->get();
        
        return view('app.admin.departements.hierarchie', [
            'entreprise' => $entreprise,
            'departementsRacines' => $departementsRacines
        ]);
    }

    /**
     * Affiche les statistiques détaillées d'un département.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function statistiques(Departement $departement)
    {
        // Vérifier que le département appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departement);
        
        // Récupérer les statistiques du département
        $tauxOccupation = $departement->calculerTauxOccupation();
        $budgetDisponible = $departement->calculerBudgetDisponible();
        $nombreEmployes = $departement->employes()->count();
        $nombreSousDepartements = $departement->sousDepartements()->count();
        
        // Récupérer les employés du département
        $employes = $departement->employes()->paginate(10);
        
        return view('app.admin.departements.statistiques', [
            'departement' => $departement,
            'tauxOccupation' => $tauxOccupation,
            'budgetDisponible' => $budgetDisponible,
            'nombreEmployes' => $nombreEmployes,
            'nombreSousDepartements' => $nombreSousDepartements,
            'employes' => $employes
        ]);
    }
    
    /**
     * Affiche l'arborescence d'un département et ses sous-départements.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function arborescence(Departement $departement)
    {
        // Vérifier que le département appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departement);
        
        // Récupérer l'arborescence du département
        $arborescence = $departement->getArborescence();
        
        return view('app.admin.departements.arborescence', [
            'departement' => $departement,
            'arborescence' => $arborescence
        ]);
    }
    
    /**
     * Affiche le formulaire de fusion de départements.
     *
     * @return \Illuminate\View\View
     */
    public function fusionForm()
    {
        $entreprise = Auth::user()->entreprise;
        $filiales = $entreprise->filiales()->with('departements')->where('statut', 'actif')->get();
        
        return view('app.admin.departements.fusion', [
            'entreprise' => $entreprise,
            'filiales' => $filiales
        ]);
    }
    
    /**
     * Fusionne deux départements.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fusion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'departement_cible_id' => 'required|exists:departements,id',
            'departement_source_id' => 'required|exists:departements,id|different:departement_cible_id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.departements.fusion.form')
                ->withErrors($validator)
                ->withInput();
        }
        
        $departementCible = Departement::findOrFail($request->departement_cible_id);
        $departementSource = Departement::findOrFail($request->departement_source_id);
        
        // Vérifier que les départements appartiennent à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departementCible);
        $this->checkDepartementAccess($departementSource);
        
        try {
            // Fusionner les départements
            $departementCible->fusionnerAvec($departementSource);
            
            return redirect()->route('admin.departements.show', $departementCible)
                ->with('success', 'Les départements ont été fusionnés avec succès.');
        } catch (DepartementException $e) {
            return redirect()->route('admin.departements.fusion.form')
                ->with('error', 'Une erreur est survenue lors de la fusion : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Affiche le formulaire de déplacement d'un département.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function deplacementForm(Departement $departement)
    {
        // Vérifier que le département appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departement);
        
        $entreprise = Auth::user()->entreprise;
        $filiales = $entreprise->filiales()->with('departements')->where('statut', 'actif')->get();
        
        return view('app.admin.departements.deplacement', [
            'departement' => $departement,
            'filiales' => $filiales
        ]);
    }
    
    /**
     * Déplace un département vers un autre département parent ou une autre filiale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deplacement(Request $request, Departement $departement)
    {
        // Vérifier que le département appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departement);
        
        $validator = Validator::make($request->all(), [
            'filiale_id' => 'required|exists:filiales,id',
            'departement_parent_id' => 'nullable|exists:departements,id|different:id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.departements.deplacement.form', $departement)
                ->withErrors($validator)
                ->withInput();
        }
        
        $filiale = Filiale::findOrFail($request->filiale_id);
        $departementParent = null;
        
        if ($request->departement_parent_id) {
            $departementParent = Departement::findOrFail($request->departement_parent_id);
            
            // Vérifier que le département parent appartient à la filiale sélectionnée
            if ($departementParent->filiale_id != $filiale->id) {
                return redirect()->route('admin.departements.deplacement.form', $departement)
                    ->with('error', 'Le département parent doit appartenir à la filiale sélectionnée.')
                    ->withInput();
            }
        }
        
        try {
            // Déplacer le département
            $departement->deplacer($filiale, $departementParent);
            
            return redirect()->route('admin.departements.show', $departement)
                ->with('success', 'Le département a été déplacé avec succès.');
        } catch (DepartementException $e) {
            return redirect()->route('admin.departements.deplacement.form', $departement)
                ->with('error', 'Une erreur est survenue lors du déplacement : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Affiche le formulaire de transfert d'employés.
     *
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\View\View
     */
    public function transfertEmployesForm(Departement $departement)
    {
        // Vérifier que le département appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departement);
        
        $entreprise = Auth::user()->entreprise;
        $departements = Departement::whereHas('filiale', function ($query) use ($entreprise) {
            $query->where('entreprise_id', $entreprise->id);
        })->where('id', '!=', $departement->id)->get();
        
        $employes = $departement->employes()->get();
        
        return view('app.admin.departements.transfert', [
            'departement' => $departement,
            'departements' => $departements,
            'employes' => $employes
        ]);
    }
    
    /**
     * Transfère des employés vers un autre département.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function transfertEmployes(Request $request, Departement $departement)
    {
        // Vérifier que le département appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departement);
        
        $validator = Validator::make($request->all(), [
            'departement_cible_id' => 'required|exists:departements,id|different:id',
            'employe_ids' => 'required|array',
            'employe_ids.*' => 'exists:employes,id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.departements.transfert.form', $departement)
                ->withErrors($validator)
                ->withInput();
        }
        
        $departementCible = Departement::findOrFail($request->departement_cible_id);
        
        // Vérifier que le département cible appartient à l'entreprise de l'utilisateur
        $this->checkDepartementAccess($departementCible);
        
        try {
            // Transférer les employés
            $departement->transfererEmployes($departementCible, $request->employe_ids);
            
            return redirect()->route('admin.departements.show', $departement)
                ->with('success', 'Les employés ont été transférés avec succès.');
        } catch (DepartementException $e) {
            return redirect()->route('admin.departements.transfert.form', $departement)
                ->with('error', 'Une erreur est survenue lors du transfert : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Vérifie que l'utilisateur a accès au département.
     *
     * @param  \App\Models\Departement  $departement
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function checkDepartementAccess(Departement $departement)
    {
        $entreprise = Auth::user()->entreprise;
        
        if (!$departement->filiale || $departement->filiale->entreprise_id !== $entreprise->id) {
            abort(403, 'Vous n\'avez pas accès à ce département.');
        }
    }
}
