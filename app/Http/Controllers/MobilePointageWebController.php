<?php

namespace App\Http\Controllers;

use App\Services\QRCodeService;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
}
