<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Presence;
use App\Services\Presence\WebPointageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KioskController extends Controller
{
    protected $webPointageService;

    public function __construct(WebPointageService $webPointageService)
    {
        $this->webPointageService = $webPointageService;
    }

    /**
     * Affiche la page kiosque (vidéo + scanner caché)
     * Pas d'authentification requise — le token identifie le site
     */
    public function index(string $token)
    {
        $site = Site::with('entreprise')->where('kiosk_token', $token)->first();

        if (!$site) {
            abort(404, 'Kiosk introuvable');
        }

        // S'assurer que le QR token du site est valide pour le pointage
        if (!$site->qr_token) {
            $site->generateQRCode();
        }

        // Marquer l'activation kiosk
        if (!$site->kiosk_activated_at) {
            $site->kiosk_activated_at = now();
            $site->save();
        }

        // Récupérer les présences récentes pour affichage optionnel
        $recentPresences = Presence::where('site_id', $site->id)
            ->with('employeur:id,nom,prenom,code_employe')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('kiosk.index', [
            'site' => $site,
            'token' => $token,
            'recentPresences' => $recentPresences,
        ]);
    }

    /**
     * Traite un scan de pointage depuis le kiosque
     */
    public function scan(Request $request, string $token)
    {
        try {
            $request->validate([
                'idno' => 'required|string',
                'pause' => 'nullable|boolean',
            ]);

            $site = Site::where('kiosk_token', $token)->first();

            if (!$site) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kiosk invalide',
                ], 404);
            }

            // Extraire le code utile depuis l'input de la douchette
            // La douchette peut envoyer : "7dO3Qb21..." ou une URL comme
            // "http://domaine.com/verification-employe/7dO3Qb21..."
            $idno = $request->idno;
            if (filter_var($idno, FILTER_VALIDATE_URL) || str_contains($idno, '/')) {
                $parts = explode('/', rtrim($idno, '/'));
                $idno = end($parts);
                // Retirer les paramètres GET éventuels
                $idno = explode('?', $idno)[0];
            }
            $idno = trim($idno);

            Log::info('Kiosk scan reçu', [
                'site' => $site->id,
                'idno_raw' => substr($request->idno, 0, 50),
                'idno_extracted' => substr($idno, 0, 20) . '...',
            ]);

            // Construire la requête pour le service
            $serviceRequest = new Request([
                'idno' => $idno,
                'token' => $site->qr_token,
                'methode_pointage' => 'kiosk_qrcode',
                'pause' => $request->pause ?? false,
            ]);

            $result = $this->webPointageService->processPointage($serviceRequest);

            Log::info('Kiosk scan résultat', ['status' => $result['status']]);

            // Si succès, stocker en cache et préparer la redirection vers la page de transition
            if ($result['status'] === 'success') {
                $requestId = Str::uuid()->toString();

                Cache::put('pointage_request_' . $requestId, [
                    'status' => 'completed',
                    'data' => $result,
                    'timestamp' => now(),
                ], 600);

                return response()->json([
                    'status' => 'success',
                    'data' => $result['data'],
                    'redirect' => '/kiosk/' . $token . '/transition?requestId=' . $requestId,
                    'requestId' => $requestId,
                ]);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Kiosk scan erreur: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Affiche la page de transition kiosk après un pointage réussi
     */
    public function transition(Request $request, string $token)
    {
        $site = Site::with('entreprise')->where('kiosk_token', $token)->first();

        if (!$site) {
            abort(404, 'Kiosk introuvable');
        }

        $requestId = $request->query('requestId');

        if (!$requestId) {
            return redirect('/kiosk/' . $token);
        }

        $cacheKey = 'pointage_request_' . $requestId;
        $data = Cache::get($cacheKey);

        if (!$data || $data['status'] !== 'completed') {
            return redirect('/kiosk/' . $token);
        }

        $result = $data['data'];

        if ($result['status'] !== 'success') {
            return redirect('/kiosk/' . $token);
        }

        // Convertir le type de pointage pour l'affichage
        $type = $this->getClockType($result['data']['type']);
        $name = $result['data']['employee'];
        $voiceMessage = htmlspecialchars($result['data']['voice'] ?? '', ENT_QUOTES, 'UTF-8');
        $infoSupplementaire = $result['data']['info_supplementaire'] ?? '';

        return view('kiosk.transition', [
            'site' => $site,
            'token' => $token,
            'type' => $type,
            'name' => $name,
            'requestId' => $requestId,
            'message' => $voiceMessage,
            'info_supplementaire' => $infoSupplementaire,
        ]);
    }

    /**
     * Convertit le type de pointage en type pour l'affichage
     */
    protected function getClockType(string $type): string
    {
        return match ($type) {
            'entree' => 'clockin',
            'sortie' => 'clockout',
            'pause_debut' => 'pause',
            'pause_fin' => 'return_clockin',
            default => 'clockin',
        };
    }

    /**
     * Récupère les présences récentes (pour affichage temps réel)
     */
    public function presences(string $token)
    {
        $site = Site::where('kiosk_token', $token)->first();

        if (!$site) {
            return response()->json(['error' => 'Kiosk invalide'], 404);
        }

        $presences = Presence::where('site_id', $site->id)
            ->whereDate('created_at', today())
            ->with('employeur:id,nom,prenom,code_employe')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'employe' => $p->employeur ? $p->employeur->prenom . ' ' . $p->employeur->nom : 'N/A',
                    'code' => $p->employeur->code_employe ?? '',
                    'type' => $p->type ?? 'N/A',
                    'heure' => $p->created_at->format('H:i'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $presences,
            'count' => $presences->count(),
        ]);
    }
}
