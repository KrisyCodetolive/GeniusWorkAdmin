<?php

namespace App\Http\Controllers;

use App\Models\Employeur;
use App\Models\Presence;
use App\Models\Site;
use App\Services\Presence\WebPointageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmartClockController extends Controller
{
    protected $webPointageService;

    public function __construct(WebPointageService $webPointageService)
    {
        $this->webPointageService = $webPointageService;
    }

    /**
     * Affiche la page principale du pointage intelligent
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer le site sélectionné depuis la session
        $siteId = session('selected_site_id');
        
        if (!$siteId) {
            return redirect()->route('filament.admin.resources.presences.index')
                ->with('error', 'Veuillez d\'abord sélectionner un site pour accéder au SmartClock');
        }
        
        // Récupérer le site avec les informations de l'entreprise associée
        $site = Site::with('entreprise')->find($siteId);
        
        if (!$site) {
            return redirect()->route('filament.admin.resources.presences.index')
                ->with('error', 'Le site sélectionné n\'existe pas ou n\'est plus disponible');
        }

        // Générer un QR code pour le site si nécessaire
        if (!$site->qr_token || Carbon::parse($site->qr_token_expires_at)->isPast()) {
            $this->webPointageService->generateQrCodeForSite($site);
        }

        // Récupérer les derniers pointages pour ce site
        $recentLogs = Presence::where('site_id', $site->id)
            ->orderBy('date_heure', 'desc')
            ->limit(10)
            ->get();

        return view('smartclock.smart-clock', [
            'site' => $site,
            'recentLogs' => $recentLogs
        ]);
    }

    /**
     * Traite un pointage par QR code physique
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPhysicalQrCode(Request $request)
    {
        try {
            // Valider la requête
            $request->validate([
                'idno' => 'required|string', // QR code secret de l'employé
                'site_id' => 'required|exists:sites,id',
                'pause' => 'nullable|boolean'
            ]);

            // Récupérer le site
            $site = Site::findOrFail($request->site_id);

            // Créer une nouvelle requête avec les paramètres nécessaires pour le service
            $serviceRequest = new Request([
                'idno' => $request->idno,
                'token' => $site->qr_token,
                'methode_pointage' => 'qrcode_physique',
                'pause' => $request->pause ?? false,
                'lat' => $request->lat,
                'lng' => $request->lng
            ]);

            Log::info('Requête de pointage physique reçue', $serviceRequest->all());

            // Générer un ID de requête unique pour le suivi
            $requestId = Str::uuid()->toString();

            // Traiter le pointage de manière asynchrone pour l'effet visuel
            Cache::put('pointage_request_' . $requestId, [
                'status' => 'processing',
                'timestamp' => now()
            ], 600); // Expire après 10 minutes

            // Traiter le pointage
            $result = $this->webPointageService->processPointage($serviceRequest);

            Log::info('Pointage physique traité', $result);
            Log::info('Pointage physique traité status', ['status' => $result['status']]);
            // Mettre à jour le cache avec le résultat
            Cache::put('pointage_request_' . $requestId, [
                'status' => $result['status'] === 'success' ? 'completed' : 'error',
                'data' => $result,
                'message' => $result['status'] === 'success' ? null : ($result['message'] ?? 'Une erreur est survenue'),
                'timestamp' => now()
            ], 600);

            // Si c'est un succès, préparer les données pour la page de transition
            if ($result['status'] === 'success') {
                
                $type = $this->getClockType($result['data']['type']);
                $name = $result['data']['employee'];
                
                // Générer un message vocal (à implémenter selon vos besoins)
                $voiceMessage = $this->generateVoiceMessage($result['data']['voice']);

                // Au lieu de renvoyer une vue, renvoyer une réponse JSON avec l'URL de redirection
                return response()->json([
                    'status' => 'success',
                    'data' => $result['data'],
                    'redirect' => '/gwork/smart-clock-transition?requestId=' . $requestId,
                    'requestId' => $requestId
                ]);
            }

            // En cas d'erreur, retourner un message
            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Une erreur est survenue lors du traitement du pointage'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du pointage physique: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifie le statut d'une requête de pointage
     *
     * @param string $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus($requestId)
    {
        $cacheKey = 'pointage_request_' . $requestId;
        $data = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requête non trouvée'
            ]);
        }

        $response = [
            'status' => $data['status'],
            'data' => $data['data'] ?? null,
            'timestamp' => $data['timestamp']
        ];
        
        // Ajouter le message d'erreur si présent
        if ($data['status'] === 'error' && isset($data['message'])) {
            $response['message'] = $data['message'];
        }
        
        return response()->json($response);
    }

    /**
     * Redirige vers la page de pointage web pour un QR code scanné
     *
     * @param string $token
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function webPointage($token)
    {
        // Vérifier si le token est valide
        $site = Site::where('qr_token', $token)
          //  ->where('qr_generated_at', '>', now())
            ->first();

        if (!$site) {
            return redirect()->route('dashboard')->with('error', 'QR code invalide ou expiré');
        }

        // Afficher la page de pointage web
        return view('pointage.web-pointage', [
            'site' => $site,
            'token' => $token
        ]);
    }

    /**
     * Traite un pointage web (après saisie de l'identifiant)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processWebPointage(Request $request)
    {
        try {
            // Valider la requête
            $request->validate([
                'idno' => 'required|string',
                'token' => 'required|string',
                'pause' => 'nullable|boolean'
            ]);

            // Traiter le pointage
            $result = $this->webPointageService->processPointage($request);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du pointage web: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Convertit le type de pointage en type pour l'affichage
     *
     * @param string $type
     * @return string
     */
    protected function getClockType($type)
    {
        switch ($type) {
            case 'entree':
                return 'clockin';
            case 'sortie':
                return 'clockout';
            case 'pause_debut':
                return 'pause';
            case 'pause_fin':
                return 'return_clockin';
            default:
                return 'clockin';
        }
    }

    /**
     * Génère un message vocal en utilisant l'API Web Speech
     *
     * @param string $message
     * @return string|null
     */
    protected function generateVoiceMessage($message)
    {
        // Au lieu de retourner null, nous allons encoder le message pour l'utiliser avec l'API Web Speech
        // dans le template blade
        if (empty($message)) {
            return null;
        }
        
        // Encoder le message pour qu'il soit sécurisé dans l'attribut data-
        return htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Affiche la page de transition après un pointage réussi
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showTransition(Request $request)
    {
        $requestId = $request->query('requestId');
        
        if (!$requestId) {
            return redirect()->route('smart-clock.index');
        }
        
        $cacheKey = 'pointage_request_' . $requestId;
        $data = Cache::get($cacheKey);
        
        if (!$data || $data['status'] !== 'completed') {
            return redirect()->route('smart-clock.index')->with('error', 'Données de pointage non trouvées ou traitement non terminé');
        }
        
        $result = $data['data'];
        
        if ($result['status'] !== 'success') {
            return redirect()->route('smart-clock.index')->with('error', $result['message'] ?? 'Une erreur est survenue');
        }
        
        // Convertir le type de pointage pour l'affichage
        $type = $this->getClockType($result['data']['type']);
        $name = $result['data']['employee'];
        $voiceMessage = $this->generateVoiceMessage($result['data']['voice']);
        $infoSupplementaire = $result['data']['info_supplementaire'] ?? '';
        
        Log::info('Voice message generated', ['message' => $result['data']['voice']]);
        
        return view('smartclock.smart-clock-transition', [
            'type' => $type,
            'name' => $name,
            'requestId' => $requestId,
            'message' => $voiceMessage,
            'info_supplementaire' => $infoSupplementaire
        ]);
    }

    /**
     * Récupère les derniers pointages pour le site actuel
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentLogs()
    {
        // Récupérer le site sélectionné depuis la session
        $siteId = session('selected_site_id');
        
        if (!$siteId) {
            return response()->json(['error' => 'Aucun site sélectionné'], 400);
        }
        
        // Récupérer les derniers pointages pour ce site
        $recentLogs = Presence::where('site_id', $siteId)
            ->with('employeur:id,nom,prenom')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($presence) {
                $type = '';
                $color = '';
                
                if ($presence->date_heure_sortie === null) {
                    $type = 'Entrée';
                    $color = 'green';
                } else {
                    $type = 'Sortie';
                    $color = 'red';
                }
                
                if ($presence->pause_debut && !$presence->pause_fin) {
                    $type = 'Pause';
                    $color = 'orange';
                } else if ($presence->pause_fin && $presence->pause_debut) {
                    $type = 'Retour';
                    $color = 'blue';
                }
                
                return [
                    'id' => $presence->id,
                    'name' => $presence->employeur->prenom . ' ' . $presence->employeur->nom,
                    'initials' => strtoupper(substr($presence->employeur->prenom, 0, 1) . substr($presence->employeur->nom, 0, 1)),
                    'type' => $type,
                    'color' => $color,
                    'time' => $presence->created_at->diffForHumans(),
                    'timestamp' => $presence->created_at->timestamp
                ];
            });
        
        return response()->json($recentLogs);
    }
}
