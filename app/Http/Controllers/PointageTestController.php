<?php

namespace App\Http\Controllers;

use App\Models\Employeur;
use App\Models\Site;
use App\Services\Presence\WebPointageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PointageTestController extends Controller
{
    protected $webPointageService;

    public function __construct(WebPointageService $webPointageService)
    {
        $this->webPointageService = $webPointageService;
    }

    /**
     * Page de test du pointage avec douchette
     */
    public function index(Request $request)
    {
        // Récupérer ou créer le QR token du site TechCorp
        $site = Site::where('entreprise_id', '01a0a32d-d6ae-7178-a092-0aa6a0287e28')->first();

        if (!$site) {
            return redirect()->back()->with('error', 'Aucun site trouvé');
        }

        // Générer le QR token si nécessaire
        if (!$site->qr_token) {
            $site->generateQRCode();
        }

        // Stocker en session pour le SmartClock
        session(['selected_site_id' => $site->id]);

        // Récupérer tous les employeurs de l'entreprise
        $employeurs = Employeur::where('entreprise_id', $site->entreprise_id)
            ->orderBy('nom')
            ->get();

        // Générer les QR codes pour chaque employeur
        $badges = $employeurs->map(function ($employeur) {
            // Le QR code contient directement le qr_code_secret
            // C'est ce que la douchette lira et enverra comme "idno"
            $qrSvg = QrCode::size(200)
                ->margin(2)
                ->generate($employeur->qr_code_secret);

            return [
                'employeur' => $employeur,
                'qr_code' => $qrSvg,
                'secret' => $employeur->qr_code_secret,
            ];
        });

        return view('pointage.test-douchette', [
            'site' => $site,
            'badges' => $badges,
        ]);
    }

    /**
     * Traite un pointage de test (sans authentification requise)
     */
    public function processTest(Request $request)
    {
        try {
            $request->validate([
                'idno' => 'required|string',
                'site_id' => 'required|exists:sites,id',
                'pause' => 'nullable|boolean',
            ]);

            $site = Site::findOrFail($request->site_id);

            // Créer la requête pour le service
            $serviceRequest = new Request([
                'idno' => $request->idno,
                'token' => $site->qr_token,
                'methode_pointage' => 'qrcode_physique',
                'pause' => $request->pause ?? false,
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);

            Log::info('Test pointage - Requête reçue', $serviceRequest->all());

            $result = $this->webPointageService->processPointage($serviceRequest);

            Log::info('Test pointage - Résultat', $result);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Test pointage - Erreur: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Réinitialise les présences du jour pour les tests
     */
    public function resetTest(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        \App\Models\Presence::where('site_id', $request->site_id)
            ->whereDate('created_at', today())
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Présences du jour supprimées pour le site',
        ]);
    }

    /**
     * Récupère les présences du jour pour affichage
     */
    public function presencesDuJour(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        $presences = \App\Models\Presence::where('site_id', $request->site_id)
            ->whereDate('created_at', today())
            ->with('employeur:id,nom,prenom,code_employe')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'employe' => $p->employeur ? $p->employeur->prenom . ' ' . $p->employeur->nom : 'N/A',
                    'code' => $p->employeur->code_employe ?? 'N/A',
                    'type' => $p->type ?? 'N/A',
                    'date_heure_entree' => $p->date_heure_entree ? $p->date_heure_entree->format('H:i:s') : null,
                    'date_heure_sortie' => $p->date_heure_sortie ? $p->date_heure_sortie->format('H:i:s') : null,
                    'created_at' => $p->created_at->format('H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $presences,
            'count' => $presences->count(),
        ]);
    }
}
