<?php

namespace App\Services;

use App\Models\SecurityKey;
use Illuminate\Support\Facades\Log;

class QREncryptionService
{
    /**
     * Génère une signature HMAC-SHA256 pour les données du QR code.
     *
     * @param string $cardId ID de la carte
     * @param int $timestamp Horodatage en minutes
     * @param string $key Clé de sécurité
     * @return string Signature tronquée (8 premiers caractères)
     */
    public static function generateSignature(string $cardId, int $timestamp, string $key): string
    {
        $dataToSign = "{$cardId}|{$timestamp}";
        $signature = hash_hmac('sha256', $dataToSign, $key);
        
        // Retourne les 8 premiers caractères de la signature
        return substr($signature, 0, 8);
    }

    /**
     * Vérifie la validité d'un QR code chiffré.
     *
     * @param string $encryptedData Données chiffrées en Base64
     * @return bool True si le QR code est valide, false sinon
     */
    public static function verifyQRData(string $encryptedData): bool
    {
        try {
            // Décode les données Base64
            $decodedString = base64_decode($encryptedData);
            if ($decodedString === false) {
                return false;
            }
            
            // Sépare les composants
            $parts = explode('|', $decodedString);
            if (count($parts) !== 3) {
                return false;
            }
            
            $cardId = $parts[0];
            $timestamp = (int) $parts[1];
            $receivedSignature = $parts[2];
            
            // Vérifie si le code a expiré (validité de 1 minute)
            $currentTimestamp = floor(time() / 60);
            if ($currentTimestamp - $timestamp > 1) {
                return false;
            }
            
            // Récupère la clé de sécurité active
            $activeKey = SecurityKey::getActiveKey();
            if (!$activeKey) {
                Log::error('Aucune clé de sécurité active disponible');
                return false;
            }
            
            // Recalcule la signature
            $calculatedSignature = self::generateSignature($cardId, $timestamp, $activeKey['key']);
            
            // Compare les signatures
            return $receivedSignature === $calculatedSignature;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du QR code : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extrait l'ID de la carte à partir des données chiffrées.
     *
     * @param string $encryptedData Données chiffrées en Base64
     * @return string|null ID de la carte ou null en cas d'erreur
     */
    public static function extractCardId(string $encryptedData): ?string
    {
        try {
            // Décode les données Base64
            $decodedString = base64_decode($encryptedData);
            if ($decodedString === false) {
                Log::warning('Impossible de décoder les données Base64');
                return null;
            }
            
            // Sépare les composants
            $parts = explode('|', $decodedString);
            if (count($parts) !== 3) {
                Log::warning('Format de données QR invalide');
                return null;
            }
            
            // Vérifie si le code a expiré (validité de 1 minute)
            $timestamp = (int) $parts[1];
            $currentTimestamp = floor(time() / 60);
            if ($currentTimestamp - $timestamp > 1) {
                Log::warning('QR code expiré');
                return null;
            }
            
            // Vérifie la signature
            $cardId = $parts[0];
            $receivedSignature = $parts[2];
            
            // Récupère la clé de sécurité active
            $activeKey = SecurityKey::getActiveKey();
            if (!$activeKey) {
                Log::error('Aucune clé de sécurité active disponible');
                return null;
            }
            
            // Recalcule la signature
            $calculatedSignature = self::generateSignature($cardId, $timestamp, $activeKey['key']);
            
            // Compare les signatures
            if ($receivedSignature !== $calculatedSignature) {
                Log::warning('Signature QR invalide');
                return null;
            }
            
            return $cardId;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'extraction de l\'ID de la carte : ' . $e->getMessage());
            return null;
        }
    }
}
