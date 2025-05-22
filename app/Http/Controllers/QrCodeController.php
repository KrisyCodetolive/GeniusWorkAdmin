<?php

namespace App\Http\Controllers;

use App\Services\GeniusToolsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Employeur;

class QrCodeController extends Controller
{
    protected GeniusToolsService $geniusToolsService;

    public function __construct(GeniusToolsService $geniusToolsService)
    {
        $this->geniusToolsService = $geniusToolsService;
    }

    /**
     * Télécharge un QR code
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadQrCode(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'name' => 'required|string|max:255',
        ]);

        $url = $request->input('url');
        $name = $request->input('name');
        $styleOptions = $request->input('style', []);

        try {
            Log::info('QrCodeController - Downloading QR code', [
                'url' => $url,
                'name' => $name
            ]);
            
            return $this->geniusToolsService->generateDonationQrCodeDownload($url, $name, $styleOptions);
        } catch (\Exception $e) {
            Log::error('QrCodeController - Error downloading QR code', [
                'message' => $e->getMessage(),
                'url' => $url,
                'name' => $name
            ]);
            
            return response()->json([
                'message' => 'Erreur lors du téléchargement du QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Télécharge un QR code par son ID
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadQrCodeById($id)
    {
        try {
            Log::info('QrCodeController - Downloading QR code by ID', [
                'id' => $id
            ]);
            
            // Récupérer les informations du QR code
            $qrCode = $this->geniusToolsService->getQrCode($id);
            
            // Télécharger le contenu du QR code
            $qrCodeContent = $this->geniusToolsService->downloadQrCode($id);
            
            // Déterminer le nom du fichier
            $filename = $qrCode['data']['name'] ?? 'qrcode';
            
            // Retourner la réponse de téléchargement
            return response()->streamDownload(
                function () use ($qrCodeContent) {
                    echo $qrCodeContent;
                },
                $filename . '.svg',
                [
                    'Content-Type' => 'image/svg+xml',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '.svg"',
                ]
            );
        } catch (\Exception $e) {
            Log::error('QrCodeController - Error downloading QR code by ID', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);
            
            abort(404, 'QR code non trouvé');
        }
    }

    /**
     * Affiche un QR code par son ID directement dans le navigateur
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showQrCodeById($id)
    {
        try {
            Log::info('QrCodeController - Showing QR code by ID', [
                'id' => $id
            ]);
            
            // Vérifier si le QR code existe
            if (!$this->geniusToolsService->qrCodeExists($id)) {
                throw new \Exception('QR code not found');
            }
            
            // Télécharger le contenu du QR code
            $qrCodeContent = $this->geniusToolsService->downloadQrCode($id);
            
            // Retourner le contenu du QR code pour affichage dans le navigateur
            return response($qrCodeContent)
                ->header('Content-Type', 'image/svg+xml');
        } catch (\Exception $e) {
            Log::error('QrCodeController - Error showing QR code by ID', [
                'message' => $e->getMessage(),
                'id' => $id
            ]);
            
            // Chercher un employeur avec cet ID de QR code
            $employeur = Employeur::where('qr_code_id', $id)->first();
            
            if ($employeur) {
                Log::info('QrCodeController - Redirecting to on-the-fly QR code generation for employee', [
                    'employeur_id' => $employeur->id,
                    'qr_code_id' => $id
                ]);
                
                // Rediriger vers la méthode de génération à la volée
                return redirect()->route('qrcode.show', [
                    'url' => route('employe.verification', ['code' => $employeur->qr_code_secret]),
                    'name' => "QR Code - {$employeur->nom_complet}"
                ]);
            }
            
            abort(404, 'QR code non trouvé');
        }
    }

    /**
     * Affiche un QR code dans le navigateur
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showQrCode(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'name' => 'required|string|max:255',
        ]);

        $url = $request->input('url');
        $name = $request->input('name');
        $styleOptions = $request->input('style', []);

        try {
            Log::info('QrCodeController - Generating QR code for display', [
                'url' => $url,
                'name' => $name
            ]);
            
            // Utiliser le service pour générer le QR code
            $qrCodeContent = $this->geniusToolsService->generateDonationQrCode($url, $name, $styleOptions);
            
            // Retourner le contenu du QR code pour affichage dans le navigateur
            return response($qrCodeContent)
                ->header('Content-Type', 'image/svg+xml');
        } catch (\Exception $e) {
            Log::error('QrCodeController - Error generating QR code for display', [
                'message' => $e->getMessage(),
                'url' => $url,
                'name' => $name
            ]);
            
            return response()->json([
                'message' => 'Erreur lors de la génération du QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
