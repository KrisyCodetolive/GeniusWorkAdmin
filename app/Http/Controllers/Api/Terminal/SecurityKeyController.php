<?php

namespace App\Http\Controllers\Api\Terminal;

use App\Http\Controllers\Controller;
use App\Models\SecurityKey;
use App\Services\QREncryptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityKeyController extends Controller
{
    /**
     * Récupère ou génère une clé de sécurité pour les terminaux.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSecurityKey(Request $request): JsonResponse
    {
        try {
            // Récupérer l'ID de l'utilisateur authentifié
            $userId = $request->user() ? $request->user()->id : null;
            
            // Vérifier s'il existe une clé active et valide
            $activeKey = SecurityKey::getActiveKey();

            // Si pas de clé active ou sur le point d'expirer (moins de 24h), générer une nouvelle
            if (!$activeKey || $activeKey['expires_at']->diffInHours(now()) < 24) {
                $keyData = SecurityKey::generateNew($userId);
                
                return response()->json([
                    'key' => $keyData['key'],
                    'expires_at' => $keyData['expires_at']->toIso8601String(),
                    'message' => 'Nouvelle clé générée'
                ]);
            }

            // Retourner la clé active existante
            return response()->json([
                'key' => $activeKey['key'],
                'expires_at' => $activeKey['expires_at']->toIso8601String(),
                'message' => 'Clé existante récupérée'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la clé de sécurité : ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Une erreur est survenue lors de la récupération de la clé de sécurité.',
            ], 500);
        }
    }
    
    /**
     * Vérifie la validité d'un QR code.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyQRCode(Request $request): JsonResponse
    {
        try {
            // Valider la requête
            $request->validate([
                'qr_data' => 'required|string',
            ]);
            
            // Vérifier le QR code
            $isValid = QREncryptionService::verifyQRData($request->qr_data);
            
            if ($isValid) {
                // Extraire l'ID de la carte
                $cardId = QREncryptionService::extractCardId($request->qr_data);
                
                return response()->json([
                    'valid' => true,
                    'card_id' => $cardId,
                    'message' => 'QR code valide',
                ]);
            } else {
                return response()->json([
                    'valid' => false,
                    'message' => 'QR code invalide ou expiré',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du QR code : ' . $e->getMessage());
            
            return response()->json([
                'valid' => false,
                'error' => 'Une erreur est survenue lors de la vérification du QR code.',
            ], 500);
        }
    }
}
