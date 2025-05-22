<?php

namespace App\Http\Controllers\Biometrique;

use App\Http\Controllers\Controller;
use App\Models\AppareilBiometrique;
use App\Services\Biometrique\AppareilBiometriqueService;
use App\Services\Biometrique\AppareilBiometriqueStatsService;
use App\Services\Biometrique\SynchronisationAutomatiqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Contrôleur pour la gestion des appareils biométriques
 */
class AppareilBiometriqueController extends Controller
{
    /**
     * @var AppareilBiometriqueService
     */
    protected $appareilService;
    
    /**
     * @var AppareilBiometriqueStatsService
     */
    protected $statsService;
    
    /**
     * @var SynchronisationAutomatiqueService
     */
    protected $syncService;

    /**
     * Constructeur
     *
     * @param AppareilBiometriqueService $appareilService
     * @param AppareilBiometriqueStatsService $statsService
     * @param SynchronisationAutomatiqueService $syncService
     */
    public function __construct(
        AppareilBiometriqueService $appareilService,
        AppareilBiometriqueStatsService $statsService,
        SynchronisationAutomatiqueService $syncService
    ) {
        $this->appareilService = $appareilService;
        $this->statsService = $statsService;
        $this->syncService = $syncService;
        $this->middleware('auth');
        $this->middleware('permission:gerer_appareils_biometriques');
    }

    /**
     * Affiche la liste des appareils biométriques
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = AppareilBiometrique::query();

        // Filtres
        if ($request->has('site_id') && $request->site_id) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->has('fabricant') && $request->fabricant) {
            $query->where('fabricant', $request->fabricant);
        }

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('adresse_ip', 'like', "%{$search}%")
                  ->orWhere('numero_serie', 'like', "%{$search}%")
                  ->orWhere('modele', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $appareils = $query->with('site')->paginate(15);

        // Récupérer les statistiques pour chaque appareil
        $appareilsWithStats = $appareils->map(function ($appareil) {
            $stats = $this->statsService->getAppareilStats($appareil->id);
            $appareil->stats = $stats;
            return $appareil;
        });

        return view('biometrique.appareils.index', [
            'appareils' => $appareils,
            'fabricants' => AppareilBiometrique::distinct('fabricant')->pluck('fabricant'),
            'statuts' => AppareilBiometrique::distinct('statut')->pluck('statut'),
            'sites' => \App\Models\Site::orderBy('nom')->get()
        ]);
    }

    /**
     * Affiche le formulaire de création d'un appareil
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('biometrique.appareils.create', [
            'sites' => \App\Models\Site::orderBy('nom')->get(),
            'fabricants' => [
                'ZKTeco' => 'ZKTeco',
                'HikVision' => 'HikVision',
                'Anviz' => 'Anviz',
                'Generic_HTTP' => 'API HTTP Générique'
            ],
            'protocoles' => [
                'TCP/IP' => 'TCP/IP',
                'HTTP' => 'HTTP',
                'HTTPS' => 'HTTPS'
            ]
        ]);
    }

    /**
     * Enregistre un nouvel appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
            'adresse_ip' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'fabricant' => 'required|string|max:50',
            'modele' => 'required|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'protocole' => 'required|string|max:20',
            'configuration' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Créer l'appareil
        $appareil = $this->appareilService->creerAppareil($request->all());

        // Tester la connexion si demandé
        if ($request->has('test_connexion') && $request->test_connexion) {
            $testResult = $this->appareilService->testerConnexion($appareil);
            
            if (!$testResult) {
                return redirect()->route('biometrique.appareils.edit', $appareil->id)
                    ->with('warning', 'Appareil créé mais la connexion a échoué');
            }
        }

        return redirect()->route('biometrique.appareils.index')
            ->with('success', 'Appareil biométrique créé avec succès');
    }

    /**
     * Affiche les détails d'un appareil
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $stats = $this->statsService->getAppareilStats($id);
        $deviceInfo = $this->statsService->getDeviceInfo($id);
        $lastLogs = $appareil->logs()->latest()->take(10)->get();
        
        return view('biometrique.appareils.show', [
            'appareil' => $appareil,
            'stats' => $stats,
            'deviceInfo' => $deviceInfo,
            'lastLogs' => $lastLogs
        ]);
    }

    /**
     * Affiche le formulaire d'édition d'un appareil
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        
        return view('biometrique.appareils.edit', [
            'appareil' => $appareil,
            'sites' => \App\Models\Site::orderBy('nom')->get(),
            'fabricants' => [
                'ZKTeco' => 'ZKTeco',
                'HikVision' => 'HikVision',
                'Anviz' => 'Anviz',
                'Generic_HTTP' => 'API HTTP Générique'
            ],
            'protocoles' => [
                'TCP/IP' => 'TCP/IP',
                'HTTP' => 'HTTP',
                'HTTPS' => 'HTTPS'
            ]
        ]);
    }

    /**
     * Met à jour un appareil
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'site_id' => 'required|exists:sites,id',
            'adresse_ip' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'fabricant' => 'required|string|max:50',
            'modele' => 'required|string|max:100',
            'numero_serie' => 'nullable|string|max:100',
            'protocole' => 'required|string|max:20',
            'configuration' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Mettre à jour l'appareil
        $appareil = AppareilBiometrique::findOrFail($id);
        $appareil = $this->appareilService->mettreAJourAppareil($appareil, $request->all());

        // Tester la connexion si demandé
        if ($request->has('test_connexion') && $request->test_connexion) {
            $testResult = $this->appareilService->testerConnexion($appareil);
            
            if (!$testResult) {
                return redirect()->route('biometrique.appareils.edit', $id)
                    ->with('warning', 'Appareil mis à jour mais la connexion a échoué');
            }
        }

        return redirect()->route('biometrique.appareils.index')
            ->with('success', 'Appareil biométrique mis à jour avec succès');
    }

    /**
     * Supprime un appareil
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $this->appareilService->supprimerAppareil($appareil);
        
        return redirect()->route('biometrique.appareils.index')
            ->with('success', 'Appareil biométrique supprimé avec succès');
    }

    /**
     * Teste la connexion à un appareil
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $result = $this->appareilService->testerConnexion($appareil);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Connexion réussie' : 'Échec de connexion'
        ]);
    }

    /**
     * Synchronise les utilisateurs avec un appareil
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncUsers($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $result = $this->statsService->synchroniserUtilisateurs($appareil);
        
        return response()->json($result);
    }

    /**
     * Synchronise les logs avec un appareil
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncLogs($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $result = $this->statsService->synchroniserLogs($appareil);
        
        return response()->json($result);
    }

    /**
     * Redémarre un appareil
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reboot($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $result = $this->appareilService->redemarrerAppareil($appareil);
        
        return response()->json($result);
    }

    /**
     * Synchronise l'heure d'un appareil
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncTime($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        $result = $this->statsService->synchroniserHeure($appareil);
        
        return response()->json($result);
    }
    
    /**
     * Affiche la page de configuration de la synchronisation automatique
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function showSyncConfig($id)
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        
        return view('biometrique.appareils.sync-config', [
            'appareil' => $appareil
        ]);
    }
    
    /**
     * Met à jour la configuration de synchronisation automatique
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSyncConfig(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sync_auto_enabled' => 'boolean',
            'sync_logs_interval' => 'required_if:sync_auto_enabled,1|integer|min:5|max:10080',
            'sync_users_interval' => 'required_if:sync_auto_enabled,1|integer|min:5|max:10080',
            'sync_time_interval' => 'required_if:sync_auto_enabled,1|integer|min:5|max:10080',
            'sync_options' => 'nullable|json'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $appareil = AppareilBiometrique::findOrFail($id);
        
        // Mettre à jour les paramètres de synchronisation
        $appareil->sync_auto_enabled = $request->has('sync_auto_enabled') ? (bool)$request->sync_auto_enabled : false;
        $appareil->sync_logs_interval = $request->sync_logs_interval ?? 60;
        $appareil->sync_users_interval = $request->sync_users_interval ?? 1440;
        $appareil->sync_time_interval = $request->sync_time_interval ?? 1440;
        $appareil->sync_options = $request->sync_options ?? null;
        $appareil->save();
        
        return redirect()->route('biometrique.appareils.show', $id)
            ->with('success', 'Configuration de synchronisation mise à jour avec succès');
    }
    
    /**
     * Exécute une synchronisation manuelle
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function runSync(Request $request, $id)
    {
        $type = $request->get('type', 'all');
        $appareil = AppareilBiometrique::findOrFail($id);
        
        $result = $this->syncService->synchroniserAppareil($id, $type);
        
        return response()->json($result);
    }
}
