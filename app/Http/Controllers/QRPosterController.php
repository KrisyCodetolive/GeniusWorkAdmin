<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\QRCodeService;
use App\Helpers\QRCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QRPosterController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->middleware('auth');
    }

    /**
     * Affiche la page d'impression de l'affiche QR Code
     */
    public function print(Site $site)
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isSupport()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que l'utilisateur a accès à ce site
        if (!$user->isSuperAdmin() && !$user->isSupport() && $site->entreprise_id !== $user->entreprise_id) {
            abort(403, 'Accès non autorisé à ce site');
        }

        // Vérifier que le site a un QR code valide
        if (!$site->qr_token || !$site->qr_generated_at) {
            return redirect()->back()->with('error', 'Ce site n\'a pas de QR code généré.');
        }

        // Vérifier que le QR code n'est pas expiré
        if ($site->qr_generated_at->addHours(24)->isPast()) {
            return redirect()->back()->with('error', 'Le QR code de ce site a expiré. Veuillez en générer un nouveau.');
        }

        // Générer l'URL de pointage
        $pointageUrl = route('mobile.pointage.index', [
            'siteId' => $site->id,
            'token' => $site->qr_token
        ]);

        // Données pour la vue
        $data = [
            'site' => $site,
            'entreprise' => $site->entreprise,
            'pointageUrl' => $pointageUrl,
            'expiresAt' => $site->qr_generated_at->addHours(24),
            'generatedAt' => $site->qr_generated_at,
        ];

        return view('qr-poster.print', $data);
    }

    /**
     * Génère et télécharge l'affiche en PDF
     */
    public function downloadPdf(Site $site)
    {
        // Vérifier les permissions
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isSupport()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que l'utilisateur a accès à ce site
        if (!$user->isSuperAdmin() && !$user->isSupport() && $site->entreprise_id !== $user->entreprise_id) {
            abort(403, 'Accès non autorisé à ce site');
        }

        // Vérifier que le site a un QR code valide
        if (!$site->qr_token || !$site->qr_generated_at) {
            return redirect()->back()->with('error', 'Ce site n\'a pas de QR code généré.');
        }

        // Générer l'URL de pointage
        $pointageUrl = route('mobile.pointage.index', [
            'siteId' => $site->id,
            'token' => $site->qr_token
        ]);

        // Données pour le PDF
        $data = [
            'site' => $site,
            'entreprise' => $site->entreprise,
            'pointageUrl' => $pointageUrl,
            'expiresAt' => $site->qr_generated_at->addHours(24),
            'generatedAt' => $site->qr_generated_at,
        ];

        // Générer le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qr-poster.pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', true);

        $filename = 'QR_Code_' . str_replace(' ', '_', $site->nom) . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Génère une affiche pour tous les sites avec QR codes
     */
    public function printAll()
    {
        $user = Auth::user();
        
        // Vérifier les permissions
        if (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isSupport()) {
            abort(403, 'Accès non autorisé');
        }

        // Récupérer tous les sites avec QR codes valides
        $query = Site::whereNotNull('qr_token')
            ->whereNotNull('qr_generated_at')
            ->where('qr_generated_at', '>', now()->subHours(24))
            ->with('entreprise');

        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $sites = $query->get();

        if ($sites->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun site avec QR code valide trouvé.');
        }

        // Préparer les données pour chaque site
        $sitesData = $sites->map(function ($site) {
            $pointageUrl = route('mobile.pointage.index', [
                'siteId' => $site->id,
                'token' => $site->qr_token
            ]);

            return [
                'site' => $site,
                'pointageUrl' => $pointageUrl,
                'expiresAt' => $site->qr_generated_at->addHours(24),
            ];
        });

        return view('qr-poster.print-all', [
            'sitesData' => $sitesData,
            'entreprise' => $user->entreprise,
        ]);
    }

    /**
     * Télécharge un PDF avec toutes les affiches
     */
    public function downloadAllPdf()
    {
        $user = Auth::user();
        
        // Vérifier les permissions
        if (!$user->isAdmin() && !$user->isSuperAdmin() && !$user->isSupport()) {
            abort(403, 'Accès non autorisé');
        }

        // Récupérer tous les sites avec QR codes valides
        $query = Site::whereNotNull('qr_token')
            ->whereNotNull('qr_generated_at')
            ->where('qr_generated_at', '>', now()->subHours(24))
            ->with('entreprise');

        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $query->where('entreprise_id', $user->entreprise_id);
        }

        $sites = $query->get();

        if ($sites->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun site avec QR code valide trouvé.');
        }

        // Préparer les données pour chaque site
        $sitesData = $sites->map(function ($site) {
            $pointageUrl = route('mobile.pointage.index', [
                'siteId' => $site->id,
                'token' => $site->qr_token
            ]);

            return [
                'site' => $site,
                'pointageUrl' => $pointageUrl,
                'expiresAt' => $site->qr_generated_at->addHours(24),
            ];
        });

        // Générer le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('qr-poster.pdf-all', [
            'sitesData' => $sitesData,
            'entreprise' => $user->entreprise,
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('isFontSubsettingEnabled', true);

        $filename = 'QR_Codes_Tous_Sites_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
