<?php

namespace App\Http\Controllers\Biometrique;

use App\Http\Controllers\Controller;
use App\Models\AppareilBiometrique;
use App\Models\Presence;
use App\Services\Biometrique\PointageBiometriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des pointages biométriques
 */
class PointageBiometriqueController extends Controller
{
    /**
     * @var PointageBiometriqueService
     */
    protected $pointageService;

    /**
     * Constructeur
     *
     * @param PointageBiometriqueService $pointageService
     */
    public function __construct(PointageBiometriqueService $pointageService)
    {
        $this->pointageService = $pointageService;
        $this->middleware('auth');
        $this->middleware('permission:gerer_pointages', ['except' => ['syncAll', 'processLogs']]);
        $this->middleware('permission:gerer_appareils_biometriques', ['only' => ['syncAll', 'processLogs']]);
    }

    /**
     * Affiche la liste des pointages biométriques
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Presence::query()->where('methode_pointage_id', function($q) {
            $q->select('id')->from('methode_pointages')->where('code', 'biometrique');
        });

        // Filtres
        if ($request->has('site_id') && $request->site_id) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('date_heure', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('date_heure', '<=', $request->date_fin);
        }

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'date_heure');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $pointages = $query->with(['user', 'site', 'methodePointage'])->paginate(20);

        return view('biometrique.pointages.index', [
            'pointages' => $pointages,
            'sites' => \App\Models\Site::orderBy('nom')->get(),
            'types' => [
                'entree' => 'Entrée',
                'sortie' => 'Sortie',
                'pause_debut' => 'Début de pause',
                'pause_fin' => 'Fin de pause'
            ],
            'statuts' => Presence::distinct('statut')->pluck('statut')
        ]);
    }

    /**
     * Affiche les détails d'un pointage
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $pointage = Presence::with(['user', 'site', 'methodePointage', 'validateur'])->findOrFail($id);
        
        return view('biometrique.pointages.show', [
            'pointage' => $pointage
        ]);
    }

    /**
     * Valide un pointage
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function validate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'commentaire' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->pointageService->validatePointage($id, auth()->id(), $request->commentaire);
        
        return redirect()->route('biometrique.pointages.show', $id)
            ->with('success', 'Pointage validé avec succès');
    }

    /**
     * Invalide un pointage
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function invalidate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'commentaire' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->pointageService->invalidatePointage($id, auth()->id(), $request->commentaire);
        
        return redirect()->route('biometrique.pointages.show', $id)
            ->with('success', 'Pointage invalidé avec succès');
    }

    /**
     * Crée un pointage manuel
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'site_id' => 'required|exists:sites,id',
            'type' => 'required|in:entree,sortie,pause_debut,pause_fin',
            'date_heure' => 'required|date',
            'commentaire' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $pointage = $this->pointageService->createManualPointage(
            $request->user_id,
            $request->site_id,
            $request->type,
            $request->date_heure,
            auth()->id(),
            $request->commentaire
        );
        
        return redirect()->route('biometrique.pointages.show', $pointage->id)
            ->with('success', 'Pointage créé avec succès');
    }

    /**
     * Affiche le formulaire de création d'un pointage manuel
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('biometrique.pointages.create', [
            'users' => \App\Models\User::orderBy('name')->get(),
            'sites' => \App\Models\Site::orderBy('nom')->get(),
            'types' => [
                'entree' => 'Entrée',
                'sortie' => 'Sortie',
                'pause_debut' => 'Début de pause',
                'pause_fin' => 'Fin de pause'
            ]
        ]);
    }

    /**
     * Supprime un pointage
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->pointageService->deletePointage($id);
        
        return redirect()->route('biometrique.pointages.index')
            ->with('success', 'Pointage supprimé avec succès');
    }

    /**
     * Synchronise tous les appareils biométriques
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAll()
    {
        $result = $this->pointageService->syncAllDevices();
        
        return response()->json($result);
    }

    /**
     * Traite les logs d'un appareil biométrique
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appareil_id' => 'required|exists:appareil_biometriques,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->pointageService->processDeviceLogs($request->appareil_id);
        
        return response()->json($result);
    }

    /**
     * Exporte les pointages en CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Presence::query()->where('methode_pointage_id', function($q) {
            $q->select('id')->from('methode_pointages')->where('code', 'biometrique');
        });

        // Appliquer les mêmes filtres que pour l'index
        if ($request->has('site_id') && $request->site_id) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('date_heure', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('date_heure', '<=', $request->date_fin);
        }

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pointages = $query->with(['user', 'site', 'methodePointage', 'validateur'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pointages_biometriques.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($pointages) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Utilisateur',
                'Site',
                'Type',
                'Date et heure',
                'Méthode',
                'Statut',
                'Validé par',
                'Date de validation',
                'Commentaire'
            ]);
            
            // Données
            foreach ($pointages as $pointage) {
                fputcsv($file, [
                    $pointage->id,
                    $pointage->user ? $pointage->user->name : 'N/A',
                    $pointage->site ? $pointage->site->nom : 'N/A',
                    $pointage->type,
                    $pointage->date_heure->format('Y-m-d H:i:s'),
                    $pointage->methodePointage ? $pointage->methodePointage->nom : 'N/A',
                    $pointage->statut,
                    $pointage->validateur ? $pointage->validateur->name : 'N/A',
                    $pointage->date_validation ? $pointage->date_validation->format('Y-m-d H:i:s') : 'N/A',
                    $pointage->commentaire
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Affiche les statistiques des pointages
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function stats(Request $request)
    {
        // Période
        $dateDebut = $request->get('date_debut', now()->subDays(30)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-d'));
        
        // Filtre par site
        $siteId = $request->get('site_id');
        
        // Statistiques par utilisateur
        $statsByUser = $this->pointageService->getStatsByUser($dateDebut, $dateFin, $siteId);
        
        // Statistiques par jour
        $statsByDay = $this->pointageService->getStatsByDay($dateDebut, $dateFin, $siteId);
        
        // Statistiques par type
        $statsByType = $this->pointageService->getStatsByType($dateDebut, $dateFin, $siteId);
        
        // Statistiques par site
        $statsBySite = $this->pointageService->getStatsBySite($dateDebut, $dateFin);
        
        return view('biometrique.pointages.stats', [
            'statsByUser' => $statsByUser,
            'statsByDay' => $statsByDay,
            'statsByType' => $statsByType,
            'statsBySite' => $statsBySite,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'siteId' => $siteId,
            'sites' => \App\Models\Site::orderBy('nom')->get()
        ]);
    }
}
