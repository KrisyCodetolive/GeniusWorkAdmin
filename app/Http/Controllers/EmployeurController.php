<?php

namespace App\Http\Controllers;

use App\Models\Employeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeurController extends Controller
{
    /**
     * Vérifie un QR code d'employé
     *
     * @param string $code
     * @return \Illuminate\Http\Response
     */
    public function verifierQrCode(string $code)
    {
        try {
            $employeur = Employeur::where('qr_code_secret', $code)
                ->where('qr_code_active', true)
                ->where('qr_code_expires_at', '>', now())
                ->first();
            
            if (!$employeur) {
                return response()->view('verification.invalid', [
                    'message' => 'QR code invalide ou expiré.'
                ], 404);
            }
            
            // Enregistrer la vérification
            Log::info('Vérification QR code employé', [
                'employeur_id' => $employeur->id,
                'code' => $code,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            // Afficher les informations de l'employé
            return view('verification.success', [
                'employeur' => $employeur
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du QR code', [
                'code' => $code,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->view('verification.error', [
                'message' => 'Une erreur est survenue lors de la vérification.'
            ], 500);
        }
    }
}
