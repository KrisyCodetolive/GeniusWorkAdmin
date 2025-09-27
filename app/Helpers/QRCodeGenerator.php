<?php

namespace App\Helpers;

/**
 * Classe utilitaire pour générer des QR codes sans dépendance externe
 * Cette classe utilise la bibliothèque JavaScript qrcode.js côté client
 * et un service web sécurisé pour les PDF
 */
class QRCodeGenerator
{
    /**
     * Génère une URL pour un QR code via un service web sécurisé
     *
     * @param string $data Données à encoder dans le QR code
     * @param int $size Taille du QR code en pixels
     * @param string $errorCorrection Niveau de correction d'erreur (L, M, Q, H)
     * @return string URL du QR code
     */
    public static function getQRCodeUrl(string $data, int $size = 250, string $errorCorrection = 'H'): string
    {
        // Utiliser un service sécurisé pour générer les QR codes
        // Nous utilisons QRServer.com qui est un service fiable et sans tracking
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . 
               '&data=' . urlencode($data) . 
               '&ecc=' . $errorCorrection;
    }
    
    /**
     * Génère un QR code pour l'affichage dans un PDF
     * 
     * @param string $data Données à encoder dans le QR code
     * @param int $size Taille du QR code en pixels
     * @return string HTML pour afficher le QR code
     */
    public static function getQRCodeForPDF(string $data, int $size = 250): string
    {
        $url = self::getQRCodeUrl($data, $size);
        return '<img src="' . $url . '" width="' . $size . '" height="' . $size . '" alt="QR Code" />';
    }
    
    /**
     * Génère le code JavaScript pour créer un QR code côté client
     * 
     * @param string $containerId ID du conteneur HTML
     * @param string $data Données à encoder dans le QR code
     * @param int $size Taille du QR code en pixels
     * @return string Code JavaScript
     */
    public static function getQRCodeJavaScript(string $containerId, string $data, int $size = 250): string
    {
        $js = "
            var container = document.getElementById('" . $containerId . "');
            var qr = qrcode(0, 'H');
            qr.addData('" . addslashes($data) . "');
            qr.make();
            
            var svgTag = qr.createSvgTag(8, 0);
            container.innerHTML = svgTag;
            
            var svgElement = container.querySelector('svg');
            if (svgElement) {
                svgElement.setAttribute('width', '" . $size . "');
                svgElement.setAttribute('height', '" . $size . "');
                svgElement.style.display = 'block';
                svgElement.style.margin = '0 auto';
            }
        ";
        
        return $js;
    }
    
    /**
     * Génère un QR code et retourne le HTML pour l'afficher
     * 
     * @param string $data Données à encoder dans le QR code
     * @param string $containerId ID du conteneur HTML (optionnel)
     * @param int $size Taille du QR code en pixels
     * @return string HTML pour afficher le QR code
     */
    public static function generateQRCodeHTML(string $data, string $containerId = null, int $size = 250): string
    {
        $id = $containerId ?: 'qrcode-' . md5($data . time());
        
        $html = '<div id="' . $id . '" class="qrcode-container"></div>';
        $html .= '<script>';
        $html .= 'document.addEventListener("DOMContentLoaded", function() {';
        $html .= self::getQRCodeJavaScript($id, $data, $size);
        $html .= '});';
        $html .= '</script>';
        
        return $html;
    }
}
