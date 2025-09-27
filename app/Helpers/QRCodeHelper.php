<?php

namespace App\Helpers;

/**
 * Classe utilitaire pour générer des QR codes sans dépendance externe
 * Cette classe utilise un service web pour générer des QR codes
 * mais peut être remplacée par une implémentation locale si nécessaire
 */
class QRCodeHelper
{
    /**
     * Génère une URL pour un QR code via un service web
     *
     * @param string $data Données à encoder dans le QR code
     * @param int $size Taille du QR code en pixels
     * @param string $errorCorrection Niveau de correction d'erreur (L, M, Q, H)
     * @return string URL du QR code
     */
    public static function getQRCodeUrl(string $data, int $size = 250, string $errorCorrection = 'H'): string
    {
        // Utiliser le service QR Server API (gratuit et sans clé API)
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . 
               '&data=' . urlencode($data) . 
               '&ecc=' . $errorCorrection;
    }
    
    /**
     * Génère une balise img HTML pour un QR code
     *
     * @param string $data Données à encoder dans le QR code
     * @param int $size Taille du QR code en pixels
     * @param string $alt Texte alternatif pour l'image
     * @param string $errorCorrection Niveau de correction d'erreur (L, M, Q, H)
     * @return string Balise img HTML
     */
    public static function getQRCodeImgTag(string $data, int $size = 250, string $alt = 'QR Code', string $errorCorrection = 'H'): string
    {
        $url = self::getQRCodeUrl($data, $size, $errorCorrection);
        return '<img src="' . $url . '" alt="' . htmlspecialchars($alt) . '" width="' . $size . '" height="' . $size . '">';
    }
}
