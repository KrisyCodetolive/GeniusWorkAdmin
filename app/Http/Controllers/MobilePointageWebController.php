<?php

namespace App\Http\Controllers;

use App\Services\QRCodeService;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class MobilePointageWebController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Page d'accueil du pointage mobile
     *
     * @param Request $request
     * @param string $siteId
     * @return \Illuminate\View\View
     */
    public function index(Request $request, string $siteId)
    {
        try {
            $token = $request->query('token');
            
            if (!$token) {
                return view('mobile.pointage.error', [
                    'error' => 'Token manquant',
                    'message' => 'Le lien de pointage est invalide. Veuillez scanner à nouveau le QR code.'
                ]);
            }

            // Valider le QR code
            $site = $this->qrCodeService->validateQRCode($token);
            
            if (!$site) {
                return view('mobile.pointage.error', [
                    'error' => 'QR code invalide',
                    'message' => 'Ce QR code est invalide ou a expiré. Veuillez demander un nouveau QR code.'
                ]);
            }

            // Récupérer les informations complètes du site
            $qrInfo = $this->qrCodeService->getQRCodeInfo($token);

            return view('mobile.pointage.index', [
                'site' => $site,
                'token' => $token,
                'qr_info' => $qrInfo,
                'app_name' => config('app.name', 'GeniusWork')
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la page de pointage mobile', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'token' => $request->query('token')
            ]);

            return view('mobile.pointage.error', [
                'error' => 'Erreur interne',
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ]);
        }
    }

    /**
     * Page de succès après pointage
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function success(Request $request)
    {
        $type = $request->query('type', 'entree');
        $employeur = $request->query('employeur');
        $site = $request->query('site');
        $heures = $request->query('heures');

        return view('mobile.pointage.success', [
            'type' => $type,
            'employeur' => $employeur,
            'site' => $site,
            'heures' => $heures,
            'app_name' => config('app.name', 'GeniusWork')
        ]);
    }

    /**
     * Page d'erreur
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function error(Request $request)
    {
        $error = $request->query('error', 'Erreur inconnue');
        $message = $request->query('message', 'Une erreur est survenue.');

        return view('mobile.pointage.error', [
            'error' => $error,
            'message' => $message,
            'app_name' => config('app.name', 'GeniusWork')
        ]);
    }

    /**
     * Page de test pour scanner les QR codes
     *
     * @return \Illuminate\View\View
     */
    public function scanner()
    {
        return view('mobile.pointage.scanner', [
            'app_name' => config('app.name', 'GeniusWork')
        ]);
    }

    /**
     * Page d'aide et instructions
     *
     * @return \Illuminate\View\View
     */
    public function help()
    {
        return view('mobile.pointage.help', [
            'app_name' => config('app.name', 'GeniusWork')
        ]);
    }
    
    /**
     * Authentification pour le pointage mobile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Valider les données de la requête
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);
        
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', false);
        
        // Tentative d'authentification
        if (auth()->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = auth()->user();
            
            // Générer un token Sanctum pour les API
            $token = $user->createToken('mobile-pointage-auth')->plainTextToken;
            
            // Si c'est une requête AJAX, retourner un JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Authentification réussie',
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]);
            }
            
            // Redirection vers la page appropriée
            return redirect()->intended(route('mobile.pointage.scanner'));
        }
        
        // Échec d'authentification
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants invalides',
                'errors' => [
                    'email' => ['Ces identifiants ne correspondent pas à nos enregistrements.']
                ]
            ], 422);
        }
        
        return back()->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ])->withInput($request->except('password'));
    }
}
