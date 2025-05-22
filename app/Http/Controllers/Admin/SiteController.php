<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Entreprise;
use App\Models\User;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    protected $siteService;

    /**
     * Constructeur du contrôleur
     *
     * @param SiteService $siteService
     */
    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
        $this->middleware('auth');
        $this->middleware('permission:gerer-sites');
    }

    /**
     * Afficher la liste des sites
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Récupérer les filtres depuis la requête
        $filters = [
            'entreprise_id' => $request->input('entreprise_id'),
            'statut' => $request->input('statut'),
            'geofencing' => $request->boolean('geofencing'),
            'search' => $request->input('search'),
            'sort_field' => $request->input('sort_field', 'nom'),
            'sort_direction' => $request->input('sort_direction', 'asc')
        ];
        
        // Récupérer les sites avec pagination
        $sites = $this->siteService->getAllSites(15, $filters);
        
        // Récupérer les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();
        
        // Récupérer les statistiques
        $statistiques = $this->siteService->getStatistiquesSites();
        
        return view('app.admin.sites.index', compact('sites', 'entreprises', 'statistiques', 'filters'));
    }

    /**
     * Afficher le formulaire de création d'un site
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $entreprises = Entreprise::orderBy('nom')->get();
        $employes = User::role('employe')->orderBy('name')->get();
        
        return view('app.admin.sites.create', compact('entreprises', 'employes'));
    }

    /**
     * Enregistrer un nouveau site
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valider les données
        $validator = Validator::make($request->all(), [
            'entreprise_id' => 'required|exists:entreprises,id',
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:20',
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rayon_geofencing' => 'nullable|numeric|min:0',
            'has_geofencing' => 'boolean',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'description' => 'nullable|string',
            'contact_nom' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_telephone' => 'nullable|string|max:20',
            'employes' => 'nullable|array',
            'employes.*' => 'exists:users,id'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.sites.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Créer le site
            $site = $this->siteService->createSite($request->all());
            
            return redirect()->route('admin.sites.show', $site->id)
                ->with('success', 'Le site a été créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.sites.create')
                ->with('error', 'Une erreur est survenue lors de la création du site: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un site
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $site = $this->siteService->getSiteById($id);
        
        if (!$site) {
            return redirect()->route('admin.sites.index')
                ->with('error', 'Site non trouvé.');
        }
        
        return view('app.admin.sites.show', compact('site'));
    }

    /**
     * Afficher le formulaire d'édition d'un site
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $site = $this->siteService->getSiteById($id);
        
        if (!$site) {
            return redirect()->route('admin.sites.index')
                ->with('error', 'Site non trouvé.');
        }
        
        $entreprises = Entreprise::orderBy('nom')->get();
        $employes = User::role('employe')->orderBy('name')->get();
        
        return view('app.admin.sites.edit', compact('site', 'entreprises', 'employes'));
    }

    /**
     * Mettre à jour un site
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Valider les données
        $validator = Validator::make($request->all(), [
            'entreprise_id' => 'required|exists:entreprises,id',
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'code_postal' => 'required|string|max:20',
            'ville' => 'required|string|max:100',
            'pays' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'rayon_geofencing' => 'nullable|numeric|min:0',
            'has_geofencing' => 'boolean',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'description' => 'nullable|string',
            'contact_nom' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_telephone' => 'nullable|string|max:20',
            'employes' => 'nullable|array',
            'employes.*' => 'exists:users,id'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.sites.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Mettre à jour le site
            $site = $this->siteService->updateSite($id, $request->all());
            
            if (!$site) {
                return redirect()->route('admin.sites.index')
                    ->with('error', 'Site non trouvé.');
            }
            
            return redirect()->route('admin.sites.show', $site->id)
                ->with('success', 'Le site a été mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.sites.edit', $id)
                ->with('error', 'Une erreur est survenue lors de la mise à jour du site: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un site
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $result = $this->siteService->deleteSite($id);
            
            if (!$result) {
                return redirect()->route('admin.sites.index')
                    ->with('error', 'Site non trouvé.');
            }
            
            return redirect()->route('admin.sites.index')
                ->with('success', 'Le site a été supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.sites.index')
                ->with('error', 'Une erreur est survenue lors de la suppression du site: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher la carte des sites
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function carte(Request $request)
    {
        $entrepriseId = $request->input('entreprise_id');
        
        // Récupérer les sites avec des coordonnées GPS
        $query = Site::whereNotNull('latitude')->whereNotNull('longitude');
        
        if ($entrepriseId) {
            $query->parEntreprise($entrepriseId);
        }
        
        $sites = $query->get();
        
        // Récupérer les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();
        
        return view('app.admin.sites.carte', compact('sites', 'entreprises', 'entrepriseId'));
    }
    
    /**
     * Afficher les statistiques des sites
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function statistiques(Request $request)
    {
        $entrepriseId = $request->input('entreprise_id');
        
        // Récupérer les statistiques
        $statistiques = $this->siteService->getStatistiquesSites($entrepriseId);
        
        // Récupérer les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();
        
        // Récupérer les sites pour les graphiques
        $sites = Site::when($entrepriseId, function($query) use ($entrepriseId) {
            return $query->parEntreprise($entrepriseId);
        })->get();
        
        return view('app.admin.sites.statistiques', compact('statistiques', 'entreprises', 'sites', 'entrepriseId'));
    }
}
