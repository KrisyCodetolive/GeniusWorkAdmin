<?php

namespace App\Services;

use App\Models\Employeur;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\GeniusToolsService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

class EmployeurCarteService
{
    protected GeniusToolsService $geniusToolsService;

    public function __construct(GeniusToolsService $geniusToolsService)
    {
        $this->geniusToolsService = $geniusToolsService;
    }

    /**
     * Génère une carte d'employé au format PNG et la retourne pour téléchargement direct
     *
     * @param Employeur $employeur
     * @return StreamedResponse Réponse de téléchargement de l'image
     */
     public function genererCarte(Employeur $employeur): StreamedResponse
    {
        // Obtenir l'URL du QR Code
        $qrCodeUrl = $this->getQrCodeUrl($employeur);

        Log::info('EmployeurCarteService - QR code URL generated for employee', [
            'employeur_id' => $employeur->id,
            'qr_code_url' => $qrCodeUrl
        ]);
        
        // Préparer les données pour la vue
        $data = [
            'employeur' => $employeur,
            'qrCodeUrl' => $qrCodeUrl,
            'entreprise' => $employeur->entreprise,
            'logoPath' => $this->getLogoPath($employeur->entreprise),
            'dateGeneration' => now()->format('d/m/Y'),
        ];
        
        // Générer le HTML de la carte avec un template optimisé pour l'impression
        $html = View::make('pdf.carte-employe', $data)->render();
        
        // Générer un nom de fichier unique
        $filename = 'carte-employe-' . Str::slug($employeur->nom_complet) . '-' . time() . '.html';
        
        Log::info('EmployeurCarteService - Carte HTML générée avec succès', [
            'employeur_id' => $employeur->id,
            'filename' => $filename
        ]);
        
        // Retourner le HTML directement
        return response()->streamDownload(
            function () use ($html) {
                echo $html;
            },
            $filename,
            [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'inline; filename="' . $filename . '"',
            ]
        );
    }
    
    /**
     * Récupère le QR Code pour un employé (généré en local avec simple-qrcode)
     * 
     * @param Employeur $employeur
     * @return string QR Code en base64 data URL
     */
    protected function getQrCodeUrl(Employeur $employeur): string
    {
        // S'assurer que l'employeur a un secret de QR code valide
        if (!$employeur->qr_code_secret || !$employeur->qr_code_active || 
            ($employeur->qr_code_expires_at && $employeur->qr_code_expires_at->isPast())) {
            $employeur->generateQRCodeSecret();
            $employeur->save();
        }
        
        try {
            // Générer l'URL de vérification
            $verificationUrl = route('employe.verification', ['code' => $employeur->qr_code_secret]);
            
            // Générer le QR code en local avec simple-qrcode (SVG inline)
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(200)
                ->color(50, 79, 136)
                ->backgroundColor(255, 255, 255)
                ->generate($verificationUrl);
            
            // Convertir en base64 data URL
            $base64 = base64_encode($svg);
            return 'data:image/svg+xml;base64,' . $base64;
            
        } catch (\Exception $e) {
            Log::error('EmployeurCarteService - Failed to generate QR code', [
                'employeur_id' => $employeur->id,
                'error' => $e->getMessage()
            ]);
            
            // Fallback: QR code minimal en base64
            $fallbackSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200" fill="white"/><text x="100" y="100" text-anchor="middle" font-size="12">QR Error</text></svg>';
            return 'data:image/svg+xml;base64,' . base64_encode($fallbackSvg);
        }
    }
    
    /**
     * Récupère le chemin du logo de l'entreprise
     * 
     * @param mixed $entreprise
     * @return string Chemin du logo
     */
    protected function getLogoPath($entreprise): string
    {
        // Vérifier si l'entreprise existe et a un logo
        if ($entreprise && $entreprise->logo) {
            return Storage::disk('public')->url($entreprise->logo);
        }
        
        // Sinon, retourner le logo par défaut
        return asset('images/logo-default.png');
    }
}
