<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeniusToolsService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected const CACHE_PREFIX = 'qrcode_';
    protected const CACHE_TTL = 86400; // 24 heures
    protected const DEFAULT_STYLE = [
        'style' => 'square',
        'inner_eye_style' => 'square',
        'outer_eye_style' => 'square',
        'foreground_type' => 'gradient',
        'foreground_gradient_style' => 'diagonal',
        'foreground_gradient_one' => '#3a5faa',
        'foreground_gradient_two' => '#324f88',
        'background_color' => '#FFFFFF',
        'background_color_transparency' => 0,
        'custom_eyes_color' => true,
        'eyes_inner_color' => '#3a5faa',
        'eyes_outer_color' => '#324f88',
        'size' => 500,
        'margin' => 10,
        'ecc' => 'H'
    ];

    public function __construct()
    {
        $this->apiKey = config('services.genius_tools.api_key');
        $this->baseUrl = config('services.genius_tools.base_url', 'https://linkqr.genius.ci/api');
        
        if (empty($this->apiKey)) {
            Log::error('GeniusToolsService - API key is not configured');
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Récupère un QR code existant par son ID
     * 
     * @param int $qrCodeId ID du QR code à récupérer
     * @return array Données du QR code
     * @throws \Exception Si la récupération échoue
     */
    public function getQrCode(int $qrCodeId)
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->get("{$this->baseUrl}/qr-codes/{$qrCodeId}");

            Log::info('GeniusToolsService - Get QR Code Info', [
                'qr_code_id' => $qrCodeId,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to get QR code info: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('GeniusToolsService - Error getting QR code', [
                'qr_code_id' => $qrCodeId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Vérifie si un QR code existe et est accessible
     * 
     * @param int $qrCodeId ID du QR code à vérifier
     * @return bool True si le QR code existe et est accessible
     */
    public function qrCodeExists(int $qrCodeId): bool
    {
        try {
            $this->getQrCode($qrCodeId);
            return true;
        } catch (\Exception $e) {
            Log::info('GeniusToolsService - QR code does not exist or is not accessible', [
                'qr_code_id' => $qrCodeId
            ]);
            return false;
        }
    }

    /**
     * Génère une clé de cache unique pour une URL et un nom donnés
     * 
     * @param string $url URL du QR code
     * @param string $name Nom du QR code
     * @return string Clé de cache
     */
    protected function generateCacheKey(string $url, string $name): string
    {
        return self::CACHE_PREFIX . md5($url . $name);
    }

    /**
     * Formate les données pour l'API Genius Tools
     * 
     * @param array $data Données à formater
     * @return array Données formatées
     */
    protected function formatData(array $data): array
    {
        // Fusionner avec les styles par défaut si style n'est pas fourni
        if (isset($data['style']) && is_array($data['style'])) {
            $data = array_merge($data, $data['style']);
            unset($data['style']);
        }

        // Convertir les valeurs en chaînes pour l'API
        return array_map(function ($value) {
            if (is_bool($value)) {
                return $value ? '1' : '0';
            }
            return (string) $value;
        }, $data);
    }

    /**
     * Crée un QR code et retourne sa réponse
     * 
     * @param array $data Données pour la création du QR code
     * @return array Réponse de l'API
     * @throws \Exception Si la création échoue
     */
    public function createQrCode(array $data)
    {
        try {
            // Formater les données pour l'API
            $formattedData = $this->formatData($data);
            
            Log::info('GeniusToolsService - Creating QR Code', [
                'data' => $formattedData
            ]);

            $response = Http::withToken($this->apiKey)
                ->asMultipart()
                ->post("{$this->baseUrl}/qr-codes", $formattedData);

            Log::info('GeniusToolsService - Create QR Code Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->failed()) {
                throw new \Exception('Failed to generate QR code: ' . $response->body());
            }

            $responseData = $response->json();
            
            // Si l'URL est fournie, on met en cache l'ID du QR code
            if (isset($data['url']) && isset($data['name'])) {
                $qrCodeId = $responseData['data']['id'] ?? null;
                if ($qrCodeId) {
                    Cache::put(
                        $this->generateCacheKey($data['url'], $data['name']),
                        $qrCodeId,
                        self::CACHE_TTL
                    );
                    
                    Log::info('GeniusToolsService - QR Code ID cached', [
                        'url' => $data['url'],
                        'name' => $data['name'],
                        'qr_code_id' => $qrCodeId
                    ]);
                }
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::error('GeniusToolsService - Error creating QR code', [
                'data' => $data,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Télécharge un QR code par son ID
     * 
     * @param int $qrCodeId ID du QR code à télécharger
     * @return string Contenu du QR code
     * @throws \Exception Si le téléchargement échoue
     */
    public function downloadQrCode(int $qrCodeId)
    {
        try {
            // 1. Obtenir les informations du QR code
            $qrCodeData = $this->getQrCode($qrCodeId);

            if (!isset($qrCodeData['data']['qr_code'])) {
                throw new \Exception('QR code URL not found in response');
            }

            // 2. Télécharger le fichier depuis l'URL
            $qrCodeUrl = $qrCodeData['data']['qr_code'];

            Log::info('GeniusToolsService - Downloading QR Code', [
                'url' => $qrCodeUrl
            ]);

            // Utiliser curl pour le téléchargement
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $qrCodeUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
            
            $imageContent = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            
            Log::info('GeniusToolsService - Download Result', [
                'http_code' => $httpCode,
                'content_type' => $contentType,
                'content_length' => strlen($imageContent)
            ]);
            
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new \Exception("Failed to download QR code image. HTTP Code: {$httpCode}");
            }

            return $imageContent;

        } catch (\Exception $e) {
            Log::error('GeniusToolsService - Error downloading QR code', [
                'qr_code_id' => $qrCodeId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Génère un QR code pour une donation et retourne le contenu de l'image
     * 
     * @param string $url URL à encoder dans le QR code
     * @param string $name Nom du QR code
     * @param array $styleOptions Options de style supplémentaires
     * @return string Contenu du QR code
     * @throws \Exception Si la génération échoue
     */
    public function generateDonationQrCode($url, $name, array $styleOptions = [])
    {
        // Vérifier si un QR code existe déjà pour cette URL
        $cacheKey = $this->generateCacheKey($url, $name);
        $existingQrCodeId = Cache::get($cacheKey);

        if ($existingQrCodeId) {
            try {
                // Tenter de récupérer le QR code existant
                return $this->downloadQrCode($existingQrCodeId);
            } catch (\Exception $e) {
                // Si une erreur survient, on supprime la clé du cache
                Cache::forget($cacheKey);
                Log::warning('GeniusToolsService - Failed to retrieve cached QR code', [
                    'id' => $existingQrCodeId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $data = [
            'name' => $name,
            'type' => 'url',
            'url' => $url,
            // Fusionner avec les styles par défaut
            'style' => array_merge(self::DEFAULT_STYLE, $styleOptions)
        ];

        // Créer le QR code
        $response = $this->createQrCode($data);
        
        if (!isset($response['data']['id'])) {
            throw new \Exception('QR code ID not found in response');
        }

        // Télécharger le QR code généré
        return $this->downloadQrCode($response['data']['id']);
    }

    /**
     * Génère et sauvegarde un QR code pour une donation
     * 
     * @param string $url URL à encoder dans le QR code
     * @param string $name Nom du QR code
     * @param string $savePath Chemin où sauvegarder le QR code
     * @param array $styleOptions Options de style supplémentaires
     * @return string Chemin complet du fichier sauvegardé
     * @throws \Exception Si la génération ou la sauvegarde échoue
     */
    public function generateAndSaveDonationQrCode($url, $name, $savePath, array $styleOptions = [])
    {
        $qrCodeContent = $this->generateDonationQrCode($url, $name, $styleOptions);
        
        if (empty($qrCodeContent)) {
            throw new \Exception('QR code content is empty');
        }

        // S'assurer que l'extension est .svg
        $savePath = preg_replace('/\.[^.]+$/', '.svg', $savePath);

        Log::info('GeniusToolsService - Saving QR Code', [
            'path' => $savePath,
            'content_length' => strlen($qrCodeContent)
        ]);
        
        // Sauvegarder le QR code
        Storage::put($savePath, $qrCodeContent);
        
        return Storage::path($savePath);
    }

    /**
     * Génère et retourne une réponse de téléchargement pour le QR code
     * 
     * @param string $url URL à encoder dans le QR code
     * @param string $name Nom du QR code
     * @param array $styleOptions Options de style supplémentaires
     * @return StreamedResponse Réponse de téléchargement
     * @throws \Exception Si la génération échoue
     */
    public function generateDonationQrCodeDownload($url, $name, array $styleOptions = [])
    {
        $qrCodeContent = $this->generateDonationQrCode($url, $name, $styleOptions);
        
        if (empty($qrCodeContent)) {
            throw new \Exception('QR code content is empty');
        }

        $filename = 'qrcode_' . time() . '.svg';

        return response()->streamDownload(function () use ($qrCodeContent) {
            echo $qrCodeContent;
        }, $filename, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
    
    /**
     * Récupère ou crée un QR code pour une URL donnée
     * 
     * @param string $url URL à encoder dans le QR code
     * @param string $name Nom du QR code
     * @param array $styleOptions Options de style supplémentaires
     * @return int ID du QR code
     * @throws \Exception Si la récupération ou la création échoue
     */
    public function getOrCreateQrCode($url, $name, array $styleOptions = []): int
    {
        // Vérifier si un QR code existe déjà pour cette URL
        $cacheKey = $this->generateCacheKey($url, $name);
        $existingQrCodeId = Cache::get($cacheKey);

        if ($existingQrCodeId && $this->qrCodeExists($existingQrCodeId)) {
            return $existingQrCodeId;
        }

        // Si le QR code n'existe pas ou n'est plus valide, on en crée un nouveau
        $data = [
            'name' => $name,
            'type' => 'url',
            'url' => $url,
            'style' => array_merge(self::DEFAULT_STYLE, $styleOptions)
        ];

        // Créer le QR code
        $response = $this->createQrCode($data);
        
        if (!isset($response['data']['id'])) {
            throw new \Exception('QR code ID not found in response');
        }

        return $response['data']['id'];
    }
}
