<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Departement;
use App\Services\EmployeurService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EmployeurController extends Controller
{
    protected $employeurService;

    /**
     * Constructeur du contrôleur
     *
     * @param EmployeurService $employeurService
     */
    public function __construct(EmployeurService $employeurService)
    {
        $this->employeurService = $employeurService;
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des employeurs
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Récupérer les filtres depuis la requête
        $filters = [
            'entreprise_id' => $request->input('entreprise_id'),
            'departement_id' => $request->input('departement_id'),
            'statut' => $request->input('statut'),
            'type_contrat' => $request->input('type_contrat'),
            'search' => $request->input('search'),
            'sort_field' => $request->input('sort_field', 'nom'),
            'sort_direction' => $request->input('sort_direction', 'asc')
        ];
        
        // Récupérer les employeurs avec pagination
        $employeurs = $this->employeurService->getAllEmployeurs(15, $filters);
        
        // Récupérer les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();
        
        // Récupérer les départements pour le filtre
        $departements = Departement::orderBy('nom')->get();
        
        // Récupérer les statistiques
        $statistiques = $this->employeurService->getStatistiquesEmployeurs();
        
        // Types de contrat pour le filtre
        $typesContrat = [
            'CDI' => 'CDI',
            'CDD' => 'CDD',
            'Intérim' => 'Intérim',
            'Stage' => 'Stage',
            'Apprentissage' => 'Apprentissage',
            'Freelance' => 'Freelance'
        ];
        
        return view('app.admin.employeurs.index', compact(
            'employeurs', 
            'entreprises', 
            'departements', 
            'statistiques', 
            'filters',
            'typesContrat'
        ));
    }

    /**
     * Afficher le formulaire de création d'un employeur
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $entreprises = Entreprise::orderBy('nom')->get();
        $departements = Departement::orderBy('nom')->get();
        
        // Types de contrat disponibles
        $typesContrat = [
            'CDI' => 'CDI',
            'CDD' => 'CDD',
            'Intérim' => 'Intérim',
            'Stage' => 'Stage',
            'Apprentissage' => 'Apprentissage',
            'Freelance' => 'Freelance'
        ];
        
        // Genres disponibles
        $genres = [
            'M' => 'Masculin',
            'F' => 'Féminin'
        ];
        
        return view('app.admin.employeurs.create', compact(
            'entreprises', 
            'departements', 
            'typesContrat', 
            'genres'
        ));
    }

    /**
     * Enregistrer un nouvel employeur
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valider les données
        $validator = Validator::make($request->all(), [
            'entreprise_id' => 'required|exists:entreprises,id',
            'departement_id' => 'required|exists:departements,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employeurs,email',
            'telephone' => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'genre' => ['nullable', Rule::in(['M', 'F', 'Autre'])],
            'photo' => 'nullable|image|max:2048',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:50',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'poste' => 'nullable|string|max:255',
            'salaire_base' => 'nullable|numeric|min:0',
            'matricule' => 'nullable|string|max:50|unique:employeurs,matricule',
            'create_user' => 'boolean',
            'user_password' => 'required_if:create_user,1|nullable|min:8',
            'send_credentials' => 'boolean'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.employeurs.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Créer l'employeur
            $employeur = $this->employeurService->createEmployeur(
                $request->all(), 
                $request->boolean('create_user')
            );
            
            return redirect()->route('admin.employeurs.show', $employeur->id)
                ->with('success', 'L\'employeur a été créé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.employeurs.create')
                ->with('error', 'Une erreur est survenue lors de la création de l\'employeur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un employeur
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $employeur = $this->employeurService->getEmployeurById($id);
        
        if (!$employeur) {
            return redirect()->route('admin.employeurs.index')
                ->with('error', 'Employeur non trouvé.');
        }
        
        // Générer le QR code si actif
        $qrCode = null;
        if ($employeur->qr_code_active && $employeur->qr_code_expires_at && $employeur->qr_code_expires_at->isFuture()) {
            $qrCodeData = $employeur->getQRCodeData();
            if ($qrCodeData) {
                $qrCode = QrCode::size(200)->generate(json_encode($qrCodeData));
            }
        }
        
        return view('app.admin.employeurs.show', compact('employeur', 'qrCode'));
    }

    /**
     * Afficher le formulaire d'édition d'un employeur
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $employeur = $this->employeurService->getEmployeurById($id);
        
        if (!$employeur) {
            return redirect()->route('admin.employeurs.index')
                ->with('error', 'Employeur non trouvé.');
        }
        
        $entreprises = Entreprise::orderBy('nom')->get();
        $departements = Departement::orderBy('nom')->get();
        
        // Types de contrat disponibles
        $typesContrat = [
            'CDI' => 'CDI',
            'CDD' => 'CDD',
            'Intérim' => 'Intérim',
            'Stage' => 'Stage',
            'Apprentissage' => 'Apprentissage',
            'Freelance' => 'Freelance'
        ];
        
        // Genres disponibles
        $genres = [
            'M' => 'Masculin',
            'F' => 'Féminin'
        ];
        
        return view('app.admin.employeurs.edit', compact(
            'employeur', 
            'entreprises', 
            'departements', 
            'typesContrat', 
            'genres'
        ));
    }

    /**
     * Mettre à jour un employeur
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
            'departement_id' => 'required|exists:departements,id',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('employeurs', 'email')->ignore($id)],
            'telephone' => 'nullable|string|max:20',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'genre' => ['nullable', Rule::in(['M', 'F', 'Autre'])],
            'photo' => 'nullable|image|max:2048',
            'date_embauche' => 'nullable|date',
            'type_contrat' => 'nullable|string|max:50',
            'statut' => ['required', Rule::in(['actif', 'inactif'])],
            'poste' => 'nullable|string|max:255',
            'salaire_base' => 'nullable|numeric|min:0',
            'matricule' => ['nullable', 'string', 'max:50', Rule::unique('employeurs', 'matricule')->ignore($id)],
            'create_user' => 'boolean',
            'update_user' => 'boolean',
            'user_password' => 'nullable|min:8'
        ]);
        
        if ($validator->fails()) {
            return redirect()->route('admin.employeurs.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Mettre à jour l'employeur
            $employeur = $this->employeurService->updateEmployeur($id, $request->all());
            
            if (!$employeur) {
                return redirect()->route('admin.employeurs.index')
                    ->with('error', 'Employeur non trouvé.');
            }
            
            return redirect()->route('admin.employeurs.show', $employeur->id)
                ->with('success', 'L\'employeur a été mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.employeurs.edit', $id)
                ->with('error', 'Une erreur est survenue lors de la mise à jour de l\'employeur: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un employeur
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $result = $this->employeurService->deleteEmployeur($id);
            
            if (!$result) {
                return redirect()->route('admin.employeurs.index')
                    ->with('error', 'Employeur non trouvé.');
            }
            
            return redirect()->route('admin.employeurs.index')
                ->with('success', 'L\'employeur a été supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.employeurs.index')
                ->with('error', 'Une erreur est survenue lors de la suppression de l\'employeur: ' . $e->getMessage());
        }
    }
    
    /**
     * Régénérer le QR code d'un employeur
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerateQRCode($id)
    {
        try {
            $qrCodeData = $this->employeurService->regenerateQRCode($id);
            
            if (!$qrCodeData) {
                return redirect()->route('admin.employeurs.show', $id)
                    ->with('error', 'Employeur non trouvé.');
            }
            
            return redirect()->route('admin.employeurs.show', $id)
                ->with('success', 'Le QR code a été régénéré avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.employeurs.show', $id)
                ->with('error', 'Une erreur est survenue lors de la régénération du QR code: ' . $e->getMessage());
        }
    }
    
    /**
     * Désactiver le QR code d'un employeur
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivateQRCode($id)
    {
        try {
            $result = $this->employeurService->deactivateQRCode($id);
            
            if (!$result) {
                return redirect()->route('admin.employeurs.show', $id)
                    ->with('error', 'Employeur non trouvé.');
            }
            
            return redirect()->route('admin.employeurs.show', $id)
                ->with('success', 'Le QR code a été désactivé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.employeurs.show', $id)
                ->with('error', 'Une erreur est survenue lors de la désactivation du QR code: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher les statistiques des employeurs
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function statistiques(Request $request)
    {
        // Récupérer l'entreprise de l'utilisateur connecté
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;

        // Récupérer les statistiques
        $statistiques = $this->employeurService->getStatistiquesEmployeurs($entrepriseId);
        
        // Récupérer les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();
        
        // Récupérer les employeurs pour les graphiques
        $employeurs = Employeur::when($entrepriseId, function($query) use ($entrepriseId) {
            return $query->parEntreprise($entrepriseId);
        })->get();
        
        // Récupérer les départements pour les graphiques
        $departements = Departement::whereIn('id', $employeurs->pluck('departement_id')->unique())->get();
        
        return view('app.admin.employeurs.statistiques', compact(
            'statistiques', 
            'entreprises', 
            'employeurs', 
            'departements', 
            'entrepriseId'
        ));
    }
    
    /**
     * Afficher l'organigramme des employeurs
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function organigramme(Request $request)
    {
        $entrepriseId = $request->input('entreprise_id');
        $departementId = $request->input('departement_id');
        
        // Récupérer les entreprises pour le filtre
        $entreprises = Entreprise::orderBy('nom')->get();
        
        // Récupérer les départements pour le filtre
        $departements = Departement::when($entrepriseId, function($query) use ($entrepriseId) {
            return $query->where('entreprise_id', $entrepriseId);
        })->orderBy('nom')->get();
        
        // Récupérer les employeurs
        $query = Employeur::with(['departement', 'entreprise'])
            ->actif();
        
        if ($entrepriseId) {
            $query->parEntreprise($entrepriseId);
        }
        
        if ($departementId) {
            $query->parDepartement($departementId);
        }
        
        $employeurs = $query->get();
        
        return view('app.admin.employeurs.organigramme', compact(
            'employeurs', 
            'entreprises', 
            'departements', 
            'entrepriseId', 
            'departementId'
        ));
    }
}
