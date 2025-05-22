<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Filiale;
use App\Models\Entreprise;
use App\Models\Employeur;
use App\Models\MethodePointage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class FilialeController extends Controller
{
    /**
     * Affiche la liste des filiales de l'entreprise.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $entreprise = Auth::user()->entreprise;
        $filiales = $entreprise->filiales()->orderBy('nom')->paginate(10);
        
        return view('app.admin.filiales.index', [
            'entreprise' => $entreprise,
            'filiales' => $filiales
        ]);
    }

    /**
     * Affiche le formulaire de création d'une filiale.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $entreprise = Auth::user()->entreprise;
        $responsables = $entreprise->employeurs()
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();
        
        $methodePointages = $entreprise->methodePointages()
            ->where('statut', 'actif')
            ->get();
        
        return view('app.admin.filiales.create', [
            'entreprise' => $entreprise,
            'responsables' => $responsables,
            'methodePointages' => $methodePointages
        ]);
    }

    /**
     * Enregistre une nouvelle filiale.
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
                Rule::unique('filiales')->where(function ($query) use ($entreprise) {
                    return $query->where('entreprise_id', $entreprise->id);
                })
            ],
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:employeurs,id',
            'methode_pointage_ids' => 'nullable|array',
            'methode_pointage_ids.*' => 'exists:methode_pointages,id',
            'horaire_debut' => 'nullable|date_format:H:i',
            'horaire_fin' => 'nullable|date_format:H:i',
            'jours_travail' => 'nullable|array',
            'jours_travail.*' => 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'limite_employes' => 'nullable|integer|min:1',
            'est_siege_social' => 'nullable|boolean'
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
            'horaires' => [
                'debut' => $request->horaire_debut,
                'fin' => $request->horaire_fin,
                'jours_travail' => $request->jours_travail ?? []
            ],
            'methodes_pointage' => $request->methode_pointage_ids ?? [],
            'limite_employes' => $request->limite_employes,
            'type' => $request->est_siege_social ? 'siege_social' : 'filiale'
        ];
        
        // Créer la filiale
        $filiale = new Filiale([
            'entreprise_id' => $entreprise->id,
            'nom' => $request->nom,
            'code' => $code,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'pays' => $request->pays,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'site_web' => $request->site_web,
            'description' => $request->description,
            'configuration' => $configuration,
            'statut' => 'actif'
        ]);
        
        $filiale->save();
        
        // Assigner le responsable si fourni
        if ($request->responsable_id) {
            $responsable = Employeur::findOrFail($request->responsable_id);
            $filiale->assignerResponsable($responsable, Filiale::ROLE_RESPONSABLE_PRINCIPAL);
        }
        
        return redirect()->route('admin.filiales.index')
            ->with('success', 'La filiale a été créée avec succès.');
    }

    /**
     * Affiche les détails d'une filiale.
     *
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\View\View
     */
    public function show(Filiale $filiale)
    {
        $this->authorize('view', $filiale);
        
        $departements = $filiale->departements()
            ->withCount('employeurs')
            ->orderBy('nom')
            ->get();
        
        $responsables = $filiale->getResponsablesActifs();
        $historiqueResponsables = $filiale->getHistoriqueResponsables();
        
        return view('app.admin.filiales.show', [
            'filiale' => $filiale,
            'departements' => $departements,
            'responsables' => $responsables,
            'historiqueResponsables' => $historiqueResponsables
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'une filiale.
     *
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\View\View
     */
    public function edit(Filiale $filiale)
    {
        $this->authorize('update', $filiale);
        
        $entreprise = Auth::user()->entreprise;
        $responsables = $entreprise->employeurs()
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();
        
        $methodePointages = $entreprise->methodePointages()
            ->where('statut', 'actif')
            ->get();
        
        $responsablePrincipal = $filiale->getResponsablePrincipalActuel();
        
        return view('app.admin.filiales.edit', [
            'filiale' => $filiale,
            'entreprise' => $entreprise,
            'responsables' => $responsables,
            'methodePointages' => $methodePointages,
            'responsablePrincipal' => $responsablePrincipal
        ]);
    }

    /**
     * Met à jour une filiale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Filiale $filiale)
    {
        $this->authorize('update', $filiale);
        
        $entreprise = Auth::user()->entreprise;
        
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('filiales')->where(function ($query) use ($entreprise, $filiale) {
                    return $query->where('entreprise_id', $entreprise->id)
                                ->where('id', '!=', $filiale->id);
                })
            ],
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'telephone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'responsable_id' => 'nullable|exists:employeurs,id',
            'methode_pointage_ids' => 'nullable|array',
            'methode_pointage_ids.*' => 'exists:methode_pointages,id',
            'horaire_debut' => 'nullable|date_format:H:i',
            'horaire_fin' => 'nullable|date_format:H:i',
            'jours_travail' => 'nullable|array',
            'jours_travail.*' => 'in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
            'limite_employes' => 'nullable|integer|min:1',
            'est_siege_social' => 'nullable|boolean',
            'statut' => 'required|in:actif,inactif'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Préparer la configuration
        $configuration = $filiale->configuration ?? [];
        $configuration['horaires'] = [
            'debut' => $request->horaire_debut,
            'fin' => $request->horaire_fin,
            'jours_travail' => $request->jours_travail ?? []
        ];
        $configuration['methodes_pointage'] = $request->methode_pointage_ids ?? [];
        $configuration['limite_employes'] = $request->limite_employes;
        $configuration['type'] = $request->est_siege_social ? 'siege_social' : 'filiale';
        
        // Mettre à jour la filiale
        $filiale->update([
            'nom' => $request->nom,
            'code' => $request->code,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'pays' => $request->pays,
            'telephone' => $request->telephone,
            'email' => $request->email,
            'site_web' => $request->site_web,
            'description' => $request->description,
            'configuration' => $configuration,
            'statut' => $request->statut
        ]);
        
        // Gérer le responsable principal
        if ($request->responsable_id) {
            $responsablePrincipal = $filiale->getResponsablePrincipalActuel();
            
            // Si le responsable a changé, mettre à jour
            if (!$responsablePrincipal || $responsablePrincipal->id != $request->responsable_id) {
                $responsable = Employeur::findOrFail($request->responsable_id);
                $filiale->assignerResponsable($responsable, Filiale::ROLE_RESPONSABLE_PRINCIPAL);
            }
        }
        
        return redirect()->route('admin.filiales.show', $filiale)
            ->with('success', 'La filiale a été mise à jour avec succès.');
    }

    /**
     * Supprime une filiale.
     *
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Filiale $filiale)
    {
        $this->authorize('delete', $filiale);
        
        // Vérifier si la filiale a des employés ou des départements
        if ($filiale->employeurs()->exists() || $filiale->departements()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette filiale car elle contient des employés ou des départements.');
        }
        
        $filiale->delete();
        
        return redirect()->route('admin.filiales.index')
            ->with('success', 'La filiale a été supprimée avec succès.');
    }

    /**
     * Assigne un responsable à une filiale.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignerResponsable(Request $request, Filiale $filiale)
    {
        $this->authorize('update', $filiale);
        
        $validator = Validator::make($request->all(), [
            'employeur_id' => 'required|exists:employeurs,id',
            'role' => 'required|in:' . Filiale::ROLE_RESPONSABLE_PRINCIPAL . ',' . Filiale::ROLE_RESPONSABLE_ADJOINT,
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            $employeur = Employeur::findOrFail($request->employeur_id);
            $filiale->assignerResponsable($employeur, $request->role);
            
            return redirect()->back()
                ->with('success', 'Le responsable a été assigné avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'assignation du responsable: ' . $e->getMessage());
        }
    }

    /**
     * Termine le mandat d'un responsable.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Filiale  $filiale
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Http\RedirectResponse
     */
    public function terminerMandat(Request $request, Filiale $filiale, Employeur $employeur)
    {
        $this->authorize('update', $filiale);
        
        try {
            $filiale->terminerMandat($employeur);
            
            return redirect()->back()
                ->with('success', 'Le mandat du responsable a été terminé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la terminaison du mandat: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les statistiques détaillées d'une filiale.
     *
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\View\View
     */
    public function statistiques(Filiale $filiale)
    {
        // Vérifier que la filiale appartient à l'entreprise de l'utilisateur
        $this->checkFilialeAccess($filiale);
        
        // Récupérer les statistiques de la filiale
        $statistiques = $filiale->getStatistiques();
        
        // Récupérer les départements avec leur nombre d'employés
        $departements = $filiale->getDepartementsAvecEmployes();
        
        return view('app.admin.filiales.statistiques', [
            'filiale' => $filiale,
            'statistiques' => $statistiques,
            'departements' => $departements
        ]);
    }
    
    /**
     * Affiche la structure hiérarchique des départements d'une filiale.
     *
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\View\View
     */
    public function structure(Filiale $filiale)
    {
        // Vérifier que la filiale appartient à l'entreprise de l'utilisateur
        $this->checkFilialeAccess($filiale);
        
        // Récupérer la structure hiérarchique des départements
        $structure = $filiale->getStructureDepartements();
        
        return view('app.admin.filiales.structure', [
            'filiale' => $filiale,
            'structure' => $structure
        ]);
    }
    
    /**
     * Affiche le formulaire de fusion de filiales.
     *
     * @return \Illuminate\View\View
     */
    public function fusionForm()
    {
        $entreprise = Auth::user()->entreprise;
        $filiales = $entreprise->filiales()->where('statut', 'actif')->orderBy('nom')->get();
        
        return view('app.admin.filiales.fusion', [
            'entreprise' => $entreprise,
            'filiales' => $filiales
        ]);
    }
    
    /**
     * Fusionne deux filiales.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function fusion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filiale_cible_id' => 'required|exists:filiales,id',
            'filiale_source_id' => 'required|exists:filiales,id|different:filiale_cible_id',
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.filiales.fusion.form')
                ->withErrors($validator)
                ->withInput();
        }
        
        $filialeCible = Filiale::findOrFail($request->filiale_cible_id);
        $filialeSource = Filiale::findOrFail($request->filiale_source_id);
        
        // Vérifier que les filiales appartiennent à l'entreprise de l'utilisateur
        $this->checkFilialeAccess($filialeCible);
        $this->checkFilialeAccess($filialeSource);
        
        try {
            // Vérifier si les filiales peuvent être fusionnées
            if (!$filialeCible->peutEtreFusionneeAvec($filialeSource)) {
                return redirect()->route('admin.filiales.fusion.form')
                    ->with('error', 'Ces filiales ne peuvent pas être fusionnées.')
                    ->withInput();
            }
            
            // Fusionner les filiales
            $filialeCible->fusionnerAvec($filialeSource);
            
            return redirect()->route('admin.filiales.show', $filialeCible)
                ->with('success', 'Les filiales ont été fusionnées avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.filiales.fusion.form')
                ->with('error', 'Une erreur est survenue lors de la fusion : ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Vérifie que l'utilisateur a accès à la filiale.
     *
     * @param  \App\Models\Filiale  $filiale
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function checkFilialeAccess(Filiale $filiale)
    {
        $entreprise = Auth::user()->entreprise;
        
        if ($filiale->entreprise_id !== $entreprise->id) {
            abort(403, 'Vous n\'avez pas accès à cette filiale.');
        }
    }
}
