<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Site;
use App\Models\User;
use App\Services\Presence\WebPointageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class WebPointageController extends Controller
{
    protected $webPointageService;

    public function __construct(WebPointageService $webPointageService)
    {
        $this->webPointageService = $webPointageService;
        $this->middleware('auth');
    }

    /**
     * Affiche le tableau de bord du Web Pointage
     */
    public function index()
    {
        // Vérifier les permissions
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport() && !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
        }

        // Récupérer les sites accessibles pour l'utilisateur
        $sites = Site::query()
            ->when(!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport(), function ($query) {
                return $query->where('entreprise_id', Auth::user()->entreprise_id);
            })
            ->orderBy('nom')
            ->get();

        return view('app.webPointage.dashboard', compact('sites'));
    }

    /**
     * Génère un QR code pour un site spécifique
     */
    public function generate(Request $request)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        $site = Site::findOrFail($request->site_id);

        // Vérifier que l'utilisateur a accès à ce site
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport() && $site->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('webPointage.index')->with('error', 'Vous n\'avez pas accès à ce site.');
        }

        // Générer un token unique
        $token = Str::uuid()->toString();
        
        // Mettre à jour le site avec le nouveau token et l'horodatage
        $site->qr_token = $token;
        $site->qr_generated_at = now();
        $site->save();

        return redirect()->route('webPointage.qrcode', ['token' => $token]);
    }

    /**
     * Affiche le QR code généré
     */
    public function qrcode($token)
    {
        $site = Site::where('qr_token', $token)->firstOrFail();

        // Vérifier que le QR code n'est pas expiré (24h)
        if (Carbon::parse($site->qr_generated_at)->addDay()->isPast()) {
            return redirect()->route('webPointage.index')->with('error', 'Ce QR code a expiré. Veuillez en générer un nouveau.');
        }

        // Vérifier que l'utilisateur a accès à ce site
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport() && $site->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('webPointage.index')->with('error', 'Vous n\'avez pas accès à ce site.');
        }

        // Générer l'URL pour le scanner
        $scannerUrl = route('webPointage.scanner', ['token' => $token]);
        
        // Générer le QR code
        $qrCode = QrCode::size(300)->generate($scannerUrl);

        // Date d'expiration
        $expirationDate = Carbon::parse($site->qr_generated_at)->addDay();

        return view('app.webPointage.qrcode', compact('site', 'qrCode', 'scannerUrl', 'expirationDate'));
    }

    /**
     * Affiche la page de scan du QR code
     */
    public function scanner($token)
    {
        $site = Site::where('qr_token', $token)->firstOrFail();

        // Vérifier que le QR code n'est pas expiré (24h)
        if (Carbon::parse($site->qr_generated_at)->addDay()->isPast()) {
            abort(403, 'Ce QR code a expiré.');
        }

        return view('app.webPointage.scanner', compact('site', 'token'));
    }

    /**
     * Traite le pointage d'un employé
     */
    public function process(Request $request)
    {
        try {
            $request->validate([
                'badge' => 'required|string',
                'token' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            // Récupérer le site associé au token
            $site = Site::where('qr_token', $request->token)->first();
            if (!$site) {
                return response()->json(['success' => false, 'message' => 'QR code invalide ou expiré.']);
            }

            // Vérifier que le QR code n'est pas expiré (24h)
            if (Carbon::parse($site->qr_generated_at)->addDay()->isPast()) {
                return response()->json(['success' => false, 'message' => 'Ce QR code a expiré.']);
            }

            // Récupérer l'utilisateur par son badge
            $user = User::where('badge_id', $request->badge)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Badge non reconnu.']);
            }

            // Vérifier que l'utilisateur appartient à la même entreprise que le site
            if ($user->entreprise_id !== $site->entreprise_id) {
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas autorisé à pointer sur ce site.']);
            }

            // Traiter le pointage via le service
            $result = $this->webPointageService->processPointage(
                $user->id,
                $site->id,
                $request->latitude,
                $request->longitude
            );

            if (!$result['success']) {
                return response()->json(['success' => false, 'message' => $result['message']]);
            }

            // Stocker les données de transition en session
            $requestId = Str::uuid()->toString();
            session(["transition_data_{$requestId}" => [
                'user' => $user->name,
                'site' => $site->nom,
                'type' => $result['type'],
                'time' => Carbon::parse($result['time'])->format('H:i:s'),
                'date' => Carbon::parse($result['time'])->format('d/m/Y'),
            ]]);

            return response()->json([
                'success' => true,
                'redirect' => route('webPointage.transition', ['id' => $requestId])
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du pointage: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors du traitement de votre pointage.']);
        }
    }

    /**
     * Affiche la page de transition après un pointage réussi
     */
    public function transition(Request $request)
    {
        $requestId = $request->query('id');
        $sessionKey = "transition_data_{$requestId}";
        
        if (!session()->has($sessionKey)) {
            return redirect()->route('dashboard');
        }
        
        $data = session($sessionKey);
        session()->forget($sessionKey);
        
        return view('app.webPointage.transition', compact('data'));
    }

    /**
     * Affiche l'historique des pointages
     */
    public function historique(Request $request)
    {
        // Vérifier les permissions
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport() && !Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
        }

        // Paramètres de filtrage
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $userId = $request->input('user_id');
        $siteId = $request->input('site_id');

        // Récupérer les utilisateurs pour le filtre
        $users = User::query()
            ->when(!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport(), function ($query) {
                return $query->where('entreprise_id', Auth::user()->entreprise_id);
            })
            ->orderBy('name')
            ->get();

        // Récupérer les sites pour le filtre
        $sites = Site::query()
            ->when(!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport(), function ($query) {
                return $query->where('entreprise_id', Auth::user()->entreprise_id);
            })
            ->orderBy('nom')
            ->get();

        // Récupérer les pointages
        $presences = Presence::with(['user', 'site'])
            ->when(!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport(), function ($query) {
                return $query->whereHas('user', function ($q) {
                    $q->where('entreprise_id', Auth::user()->entreprise_id);
                });
            })
            ->when($startDate, function ($query, $startDate) {
                return $query->whereDate('date_heure', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                return $query->whereDate('date_heure', '<=', $endDate);
            })
            ->when($userId, function ($query, $userId) {
                return $query->where('user_id', $userId);
            })
            ->when($siteId, function ($query, $siteId) {
                return $query->where('site_id', $siteId);
            })
            ->orderBy('date_heure', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Calculer les statistiques si un utilisateur est sélectionné
        $statistics = null;
        if ($userId) {
            $statistics = $this->webPointageService->calculateStatistics(
                $userId,
                Carbon::parse($startDate),
                Carbon::parse($endDate)
            );
        }

        return view('app.webPointage.historique', compact(
            'presences',
            'users',
            'sites',
            'startDate',
            'endDate',
            'userId',
            'siteId',
            'statistics'
        ));
    }

    /**
     * Télécharge le QR code au format PNG
     */
    public function downloadQrCode($token)
    {
        $site = Site::where('qr_token', $token)->firstOrFail();

        // Vérifier que le QR code n'est pas expiré (24h)
        if (Carbon::parse($site->qr_generated_at)->addDay()->isPast()) {
            return redirect()->route('webPointage.index')->with('error', 'Ce QR code a expiré. Veuillez en générer un nouveau.');
        }

        // Vérifier que l'utilisateur a accès à ce site
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isSupport() && $site->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('webPointage.index')->with('error', 'Vous n\'avez pas accès à ce site.');
        }

        // Générer l'URL pour le scanner
        $scannerUrl = route('webPointage.scanner', ['token' => $token]);
        
        // Générer le QR code
        $qrCode = QrCode::format('png')->size(300)->generate($scannerUrl);

        $filename = Str::slug($site->nom) . '-qrcode.png';

        return response($qrCode)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
