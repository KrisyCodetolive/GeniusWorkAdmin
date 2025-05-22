<?php

namespace App\Http\Controllers\Biometrique;

use App\Http\Controllers\Controller;
use App\Models\AppareilBiometrique;
use App\Models\Presence;
use App\Services\Biometrique\AppareilBiometriqueService;
use App\Services\Biometrique\PointageBiometriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur pour le tableau de bord des appareils biométriques
 */
class DashboardBiometriqueController extends Controller
{
    /**
     * @var AppareilBiometriqueService
     */
    protected $appareilService;

    /**
     * @var PointageBiometriqueService
     */
    protected $pointageService;

    /**
     * Constructeur
     *
     * @param AppareilBiometriqueService $appareilService
     * @param PointageBiometriqueService $pointageService
     */
    public function __construct(
        AppareilBiometriqueService $appareilService,
        PointageBiometriqueService $pointageService
    ) {
        $this->appareilService = $appareilService;
        $this->pointageService = $pointageService;
        $this->middleware('auth');
        $this->middleware('permission:gerer_appareils_biometriques');
    }

    /**
     * Affiche le tableau de bord principal
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Statistiques des appareils
        $deviceStats = $this->getDeviceStats();
        
        // Statistiques des pointages
        $pointageStats = $this->getPointageStats();
        
        // Appareils avec problèmes
        $problematicDevices = $this->getProblematicDevices();
        
        // Derniers pointages
        $latestPointages = Presence::where('methode_pointage_id', function($q) {
                $q->select('id')->from('methode_pointages')->where('code', 'biometrique');
            })
            ->with(['user', 'site'])
            ->latest('date_heure')
            ->take(10)
            ->get();
        
        // Statistiques par site
        $statsBySite = $this->getStatsBySite();
        
        return view('biometrique.dashboard.index', [
            'deviceStats' => $deviceStats,
            'pointageStats' => $pointageStats,
            'problematicDevices' => $problematicDevices,
            'latestPointages' => $latestPointages,
            'statsBySite' => $statsBySite
        ]);
    }

    /**
     * Affiche la carte des appareils et des présences
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function map(Request $request)
    {
        // Récupérer tous les appareils avec leurs sites
        $appareils = AppareilBiometrique::with('site')->get();
        
        // Récupérer les présences actives
        $presencesActives = $this->pointageService->getActivePresences();
        
        return view('biometrique.dashboard.map', [
            'appareils' => $appareils,
            'presencesActives' => $presencesActives
        ]);
    }

    /**
     * Récupère les statistiques des appareils biométriques
     *
     * @return array
     */
    protected function getDeviceStats(): array
    {
        $totalDevices = AppareilBiometrique::count();
        $onlineDevices = AppareilBiometrique::where('statut', 'actif')->count();
        $offlineDevices = AppareilBiometrique::where('statut', 'inactif')->count();
        $maintenanceDevices = AppareilBiometrique::where('statut', 'maintenance')->count();
        
        $devicesByManufacturer = AppareilBiometrique::select('fabricant', DB::raw('count(*) as total'))
            ->groupBy('fabricant')
            ->get()
            ->pluck('total', 'fabricant')
            ->toArray();
        
        return [
            'total' => $totalDevices,
            'online' => $onlineDevices,
            'offline' => $offlineDevices,
            'maintenance' => $maintenanceDevices,
            'by_manufacturer' => $devicesByManufacturer
        ];
    }

    /**
     * Récupère les statistiques des pointages biométriques
     *
     * @return array
     */
    protected function getPointageStats(): array
    {
        $methodeBiometriqueId = DB::table('methode_pointages')
            ->where('code', 'biometrique')
            ->value('id');
        
        if (!$methodeBiometriqueId) {
            return [
                'today' => 0,
                'week' => 0,
                'month' => 0,
                'by_type' => []
            ];
        }
        
        $today = Presence::where('methode_pointage_id', $methodeBiometriqueId)
            ->whereDate('date_heure', now()->toDateString())
            ->count();
        
        $week = Presence::where('methode_pointage_id', $methodeBiometriqueId)
            ->whereBetween('date_heure', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
        
        $month = Presence::where('methode_pointage_id', $methodeBiometriqueId)
            ->whereMonth('date_heure', now()->month)
            ->whereYear('date_heure', now()->year)
            ->count();
        
        $byType = Presence::where('methode_pointage_id', $methodeBiometriqueId)
            ->whereDate('date_heure', '>=', now()->subDays(30))
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->get()
            ->pluck('total', 'type')
            ->toArray();
        
        return [
            'today' => $today,
            'week' => $week,
            'month' => $month,
            'by_type' => $byType
        ];
    }

    /**
     * Récupère les appareils avec des problèmes
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getProblematicDevices()
    {
        return AppareilBiometrique::where('statut', '!=', 'actif')
            ->orWhere(function($query) {
                $query->whereDate('derniere_connexion', '<', now()->subHours(24))
                    ->orWhereNull('derniere_connexion');
            })
            ->with('site')
            ->get();
    }

    /**
     * Récupère les statistiques par site
     *
     * @return array
     */
    protected function getStatsBySite(): array
    {
        $methodeBiometriqueId = DB::table('methode_pointages')
            ->where('code', 'biometrique')
            ->value('id');
        
        if (!$methodeBiometriqueId) {
            return [];
        }
        
        $stats = DB::table('presences')
            ->join('sites', 'presences.site_id', '=', 'sites.id')
            ->where('presences.methode_pointage_id', $methodeBiometriqueId)
            ->whereDate('presences.date_heure', '>=', now()->subDays(30))
            ->select(
                'sites.id as site_id',
                'sites.nom as site_nom',
                DB::raw('count(*) as total_pointages'),
                DB::raw('count(distinct presences.user_id) as total_users')
            )
            ->groupBy('sites.id', 'sites.nom')
            ->get();
        
        return $stats->toArray();
    }

    /**
     * Récupère les données en temps réel pour le tableau de bord
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function realTimeData(Request $request)
    {
        // Statistiques des appareils
        $deviceStats = $this->getDeviceStats();
        
        // Statistiques des pointages d'aujourd'hui
        $methodeBiometriqueId = DB::table('methode_pointages')
            ->where('code', 'biometrique')
            ->value('id');
        
        $todayPointages = [];
        
        if ($methodeBiometriqueId) {
            $todayPointages = Presence::where('methode_pointage_id', $methodeBiometriqueId)
                ->whereDate('date_heure', now()->toDateString())
                ->select(DB::raw('HOUR(date_heure) as hour'), DB::raw('count(*) as total'))
                ->groupBy(DB::raw('HOUR(date_heure)'))
                ->get()
                ->pluck('total', 'hour')
                ->toArray();
        }
        
        // Derniers pointages
        $latestPointages = Presence::where('methode_pointage_id', $methodeBiometriqueId)
            ->with(['user', 'site'])
            ->latest('date_heure')
            ->take(5)
            ->get()
            ->map(function ($pointage) {
                return [
                    'id' => $pointage->id,
                    'user_name' => $pointage->user ? $pointage->user->name : 'N/A',
                    'site_name' => $pointage->site ? $pointage->site->nom : 'N/A',
                    'type' => $pointage->type,
                    'date_heure' => $pointage->date_heure->format('Y-m-d H:i:s'),
                    'statut' => $pointage->statut
                ];
            });
        
        // Appareils en ligne/hors ligne
        $deviceStatus = AppareilBiometrique::select('id', 'nom', 'statut', 'derniere_connexion')
            ->get()
            ->map(function ($device) {
                return [
                    'id' => $device->id,
                    'nom' => $device->nom,
                    'statut' => $device->statut,
                    'derniere_connexion' => $device->derniere_connexion ? $device->derniere_connexion->format('Y-m-d H:i:s') : null,
                    'is_online' => $device->statut === 'actif' && 
                                  ($device->derniere_connexion && $device->derniere_connexion->gt(now()->subHours(1)))
                ];
            });
        
        return response()->json([
            'deviceStats' => $deviceStats,
            'todayPointages' => $todayPointages,
            'latestPointages' => $latestPointages,
            'deviceStatus' => $deviceStatus
        ]);
    }

    /**
     * Récupère les données pour la carte en temps réel
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mapData(Request $request)
    {
        // Récupérer tous les appareils avec leurs sites
        $appareils = AppareilBiometrique::with('site')
            ->get()
            ->map(function ($appareil) {
                $site = $appareil->site;
                return [
                    'id' => $appareil->id,
                    'nom' => $appareil->nom,
                    'statut' => $appareil->statut,
                    'fabricant' => $appareil->fabricant,
                    'modele' => $appareil->modele,
                    'site_id' => $site ? $site->id : null,
                    'site_nom' => $site ? $site->nom : 'N/A',
                    'latitude' => $site ? $site->latitude : null,
                    'longitude' => $site ? $site->longitude : null,
                    'is_online' => $appareil->statut === 'actif' && 
                                  ($appareil->derniere_connexion && $appareil->derniere_connexion->gt(now()->subHours(1)))
                ];
            })
            ->filter(function ($appareil) {
                return $appareil['latitude'] && $appareil['longitude'];
            });
        
        // Récupérer les présences actives
        $presencesActives = $this->pointageService->getActivePresences()
            ->map(function ($presence) {
                return [
                    'id' => $presence->id,
                    'user_id' => $presence->user_id,
                    'user_name' => $presence->user ? $presence->user->name : 'N/A',
                    'site_id' => $presence->site_id,
                    'site_nom' => $presence->site ? $presence->site->nom : 'N/A',
                    'date_heure' => $presence->date_heure->format('Y-m-d H:i:s'),
                    'latitude' => $presence->latitude,
                    'longitude' => $presence->longitude,
                    'type' => $presence->type
                ];
            });
        
        return response()->json([
            'appareils' => $appareils,
            'presencesActives' => $presencesActives
        ]);
    }
}
