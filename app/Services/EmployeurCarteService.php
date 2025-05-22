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
     * Récupère l'URL du QR Code pour un employé
     * 
     * @param Employeur $employeur
     * @return string URL du QR Code
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
            // Générer l'URL de vérification et le nom du QR code
            $verificationUrl = route('employe.verification', ['code' => $employeur->qr_code_secret]);
            $qrName = "QR Code - {$employeur->nom_complet}";
            
            // Définir les options de style pour le QR code
            $styleOptions = [
                'foreground_gradient_one' => '#3a5faa',
                'foreground_gradient_two' => '#324f88',
                'eyes_inner_color' => '#3a5faa',
                'eyes_outer_color' => '#324f88',
            ];
            
            // Utiliser la nouvelle méthode pour récupérer ou créer un QR code
            $qrCodeId = $this->geniusToolsService->getOrCreateQrCode($verificationUrl, $qrName, $styleOptions);
            
            // Mettre à jour l'ID du QR code dans le modèle si nécessaire
            if ($employeur->qr_code_id !== $qrCodeId) {
                $employeur->qr_code_id = $qrCodeId;
                $employeur->save();
                
                Log::info('EmployeurCarteService - QR code ID updated for employee', [
                    'employeur_id' => $employeur->id,
                    'qr_code_id' => $qrCodeId
                ]);
            }
            
            // Récupérer les informations complètes du QR code pour obtenir l'URL directe de l'image SVG
            $qrCodeInfo = $this->geniusToolsService->getQrCode($qrCodeId);
            
            // Vérifier si la réponse contient l'URL de l'image SVG
            if (isset($qrCodeInfo['data']['qr_code']) && !empty($qrCodeInfo['data']['qr_code'])) {
                $svgUrl = $qrCodeInfo['data']['qr_code'];
                
                Log::info('EmployeurCarteService - Using direct SVG URL for QR code', [
                    'employeur_id' => $employeur->id,
                    'qr_code_svg_url' => $svgUrl
                ]);
                
                // Télécharger le contenu SVG et le convertir en base64 afin de l'intégrer directement dans l'image
                try {
                    // Télécharger le contenu SVG
                    $svgContent = file_get_contents($svgUrl);
                    
                    if ($svgContent !== false) {
                        // Convertir en base64 et créer une URL data
                        $base64 = base64_encode($svgContent);
                        $dataUrl = 'data:image/svg+xml;base64,' . $base64;
                        
                        Log::info('EmployeurCarteService - Converted SVG to base64', [
                            'employeur_id' => $employeur->id,
                            'content_length' => strlen($dataUrl)
                        ]);
                        
                        // Retourner l'URL data
                        return $dataUrl;
                    }
                } catch (\Exception $e) {
                    Log::warning('EmployeurCarteService - Failed to convert SVG to base64', [
                        'employeur_id' => $employeur->id,
                        'message' => $e->getMessage()
                    ]);
                    // Continuer avec l'URL directe en cas d'échec
                }
                
                // Retourner l'URL directe de l'image SVG si la conversion a échoué
                return $svgUrl;
            }
            
            // Fallback: Retourner l'URL d'affichage du QR code
            return route('qr-code.display', ['id' => $qrCodeId]);
            
        } catch (\Exception $e) {
            Log::error('EmployeurCarteService - Failed to generate QR code URL', [
                'employeur_id' => $employeur->id,
                'error' => $e->getMessage()
            ]);
            
            // En cas d'erreur, retourner une URL de QR code générique
            return asset('images/qr-code-placeholder.png');
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
