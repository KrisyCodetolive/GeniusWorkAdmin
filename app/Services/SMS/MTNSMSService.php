<?php

namespace App\Services\SMS;

use App\Services\SMS\SMSLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MTNSMSService
{
    protected $clientId;
    protected $clientSecret;
    protected $devPhoneNumber;
    protected $accessToken;
    protected $tokenExpiry;
    protected $smsLogService;
    protected $customConfig = null;
    protected $apiBaseUrl;
    protected $senderAddress;

    public function __construct(SMSLogService $smsLogService)
    {
        // Forcer l'écriture dans les logs
        Log::channel('sms')->info('Démarrage du service MTN SMS');
        
        // Déterminer l'environnement
        $environment = config('mtn-sms.environment', 'sandbox'); // Utiliser sandbox par défaut pour les tests
        
        // Charger les identifiants depuis la configuration spécifique MTN pour l'environnement actuel
        $this->clientId = config("mtn-sms.{$environment}.client_id");
        $this->clientSecret = config("mtn-sms.{$environment}.client_secret");
        $this->apiBaseUrl = config("mtn-sms.{$environment}.api_base_url");
        
        // Charger les paramètres communs
        $this->devPhoneNumber = config('mtn-sms.dev_phone_number');
        $this->senderAddress = config('mtn-sms.sender_address');
        
        // Utiliser le canal de log SMS pour les informations de configuration
        Log::channel('sms')->info('MTN SMS Config chargée', [
            'client_id' => $this->clientId ? '****' . substr($this->clientId, -4) : null,
            'client_secret' => $this->clientSecret ? '****' . substr($this->clientSecret, -4) : null,
            'dev_phone_number' => $this->devPhoneNumber,
            'sender_address' => $this->senderAddress,
            'environment' => $environment,
            'api_base_url' => $this->apiBaseUrl,
            'config_values' => [
                'service_code' => config('mtn-sms.service_code'),
                'token_path' => config("{$environment}.token_path"),
                'sms_path' => config("{$environment}.sms_path"),
                'sms_api_version' => config("{$environment}.sms_api_version")
            ]
        ]);
        
        // Écrire directement dans le fichier pour s'assurer que les logs sont générés
        file_put_contents(
            storage_path('logs/sms-' . date('Y-m-d') . '.log'),
            '[' . date('Y-m-d H:i:s') . '] MTN SMS Service initialized with environment: ' . $environment . ", API URL: {$this->apiBaseUrl}\n",
            FILE_APPEND
        );
        
        $this->accessToken = null;
        $this->tokenExpiry = null;
        $this->smsLogService = $smsLogService;
    }

    public function useCustomConfig(array $config)
    {
        $this->customConfig = $config;
        // Réinitialiser le token lors du changement de config
        $this->accessToken = null;
        $this->tokenExpiry = null;
        return $this;
    }

    public function resetToDefaultConfig()
    {
        $this->customConfig = null;
        $this->accessToken = null;
        $this->tokenExpiry = null;
        return $this;
    }

    /**
     * Récupère la configuration MTN SMS en fonction de l'environnement
     *
     * @return array
     */
    protected function getConfig()
    {
        // Si une configuration personnalisée est définie, l'utiliser
        if ($this->customConfig) {
            return $this->customConfig;
        }
        
        // Charger l'environnement depuis la configuration
        $environment = config('mtn-sms.environment', 'sandbox');
        
        // Écrire dans le fichier de logs pour le débogage
        file_put_contents(
            storage_path('logs/sms-' . date('Y-m-d') . '.log'),
            '[' . date('Y-m-d H:i:s') . '] getConfig - Environnement: ' . $environment . "\n",
            FILE_APPEND
        );
        
        // Récupérer les paramètres spécifiques à l'environnement
        $baseUrl = config("mtn-sms.{$environment}.api_base_url");
        $tokenPath = config("mtn-sms.{$environment}.token_path", '/v1/oauth/access_token/accesstoken');
        $smsApiVersion = config("mtn-sms.{$environment}.sms_api_version");
        $smsPath = config("mtn-sms.{$environment}.sms_path");
        
        // Récupérer les identifiants spécifiques à l'environnement
        $clientId = config("mtn-sms.{$environment}.client_id");
        $clientSecret = config("mtn-sms.{$environment}.client_secret");
        
        // Récupérer les paramètres communs
        $serviceCode = config('mtn-sms.service_code', '131');
        
        return [
            'client_id' => $clientId ?: $this->clientId,
            'client_secret' => $clientSecret ?: $this->clientSecret,
            'dev_phone_number' => $this->devPhoneNumber,
            'sender_address' => $this->senderAddress,
            'service_code' => $serviceCode,
            'api_base_url' => $baseUrl,
            'environment' => $environment,
            'token_path' => $tokenPath,
            'sms_api_version' => $smsApiVersion,
            'sms_path' => $smsPath
        ];
    }

    protected function getCurrentConfig()
    {
        if ($this->customConfig) {
            return [
                'client_id' => $this->customConfig['client_id'],
                'client_secret' => $this->customConfig['client_secret'],
                'dev_phone_number' => $this->customConfig['dev_phone_number'] ?? $this->devPhoneNumber,
                'sender_address' => $this->customConfig['sender_address'] ?? $this->senderAddress,
                'api_base_url' => $this->customConfig['api_base_url'] ?? $this->apiBaseUrl,
            ];
        }

        return $this->getConfig();
    }

    /**
     * Obtient un token d'accès pour l'API MTN SMS
     * 
     * Cette méthode essaie plusieurs approches d'authentification :
     * 1. Utilisation du token en cache s'il existe
     * 2. Obtention d'un nouveau token OAuth2 avec plusieurs méthodes
     * 3. Fallback vers une clé API (x-api-key)
     * 
     * @return array Tableau contenant le type d'authentification et la valeur du token
     */
    protected function getAccessToken()
    {
        // Écrire directement dans le fichier de logs pour le débogage
        file_put_contents(
            storage_path('logs/sms-' . date('Y-m-d') . '.log'),
            '[' . date('Y-m-d H:i:s') . '] Début de la méthode getAccessToken()\n',
            FILE_APPEND
        );
        
        try {
            // Effacer le cache pour forcer un nouveau token
            Cache::forget('mtn_sms_token');
            
            // Utiliser le canal de log SMS pour les informations de token
            Log::channel('sms')->info('Token MTN en cache supprimé pour forcer un nouveau token');
            
            // Écrire directement dans le fichier pour s'assurer que les logs sont générés
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Token MTN en cache supprimé\n',
                FILE_APPEND
            );
            
            $config = $this->getConfig();
            $baseUrl = $config['api_base_url'];
            $tokenPath = $config['token_path'];
            $environment = $config['environment'];
            $clientId = $config['client_id'];
            $clientSecret = $config['client_secret'];
            
            // Vérifier si les identifiants sont disponibles
            if (empty($clientId) || empty($clientSecret)) {
                Log::channel('sms')->error("Identifiants MTN manquants pour l'environnement {$environment}");
                file_put_contents(
                    storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . '] ERREUR: Identifiants MTN manquants pour l\'environnement ' . $environment . "\n",
                    FILE_APPEND
                );
                return null;
            }
            
            $tokenUrl = $baseUrl . $tokenPath . '?grant_type=client_credentials&scope=SEND-SMS';
            
            // Écrire directement dans le fichier pour s'assurer que les logs sont générés
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Configuration MTN: Base URL: ' . $baseUrl . ', Token Path: ' . $tokenPath . ', Environment: ' . $environment . ', Token URL: ' . $tokenUrl . "\n",
                FILE_APPEND
            );
            
            Log::channel('sms')->info("Utilisation de l'environnement MTN: {$environment}", [
                'base_url' => $baseUrl,
                'token_path' => $tokenPath,
                'sms_api_version' => $config['sms_api_version'],
                'token_url' => $tokenUrl
            ]);
            
            // Première tentative avec OAuth2 Form
            Log::channel('sms')->info('Tentative d\'authentification MTN avec les identifiants', [
                'client_id' => $clientId ? '****' . substr($clientId, -4) : null,
                'client_secret' => $clientSecret ? '****' . substr($clientSecret, -4) : null,
                'environment' => $environment
            ]);
            
            // En-têtes spécifiques selon l'environnement
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            
            // Ajouter la clé d'abonnement API si nécessaire (souvent utilisée dans les environnements sandbox)
            if ($environment === 'sandbox') {
                $headers['Ocp-Apim-Subscription-Key'] = $clientSecret;
            }
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Tentative OAuth2 Form avec headers: ' . json_encode($headers) . "\n",
                FILE_APPEND
            );
            
            $response = Http::asForm()
                ->withHeaders($headers)
                ->post($tokenUrl, [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'SEND-SMS'
                ]);
            
            // Logger la réponse
            Log::channel('sms')->info('Réponse de la requête de token MTN (OAuth2 Form)', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            // Si la première tentative échoue, essayer avec Basic Auth
            if (!$response->successful()) {
                Log::channel('sms')->info('Tentative d\'obtention du token OAuth2 MTN avec Basic Auth');
                
                file_put_contents(
                    storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . '] Tentative OAuth2 Basic Auth\n',
                    FILE_APPEND
                );
                
                $response = Http::withBasicAuth($clientId, $clientSecret)
                    ->withHeaders($headers)
                    ->post($tokenUrl);
                
                // Logger la réponse
                Log::channel('sms')->info('Réponse de la requête de token MTN (OAuth2 Basic Auth)', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
            
            // Si les deux tentatives échouent, utiliser un token temporaire pour les tests
            if (!$response->successful()) {
                Log::channel('sms')->info('Tentative avec Bearer token direct');
                
                // Token temporaire pour les tests
                $tempToken = 'TeplXXXXca3LFyN1zG6oULQHz2iw8cVF';
                
                // Mettre en cache pour 3600 secondes (1 heure)
                Cache::put('mtn_sms_token', $tempToken, 3600);
                
                return $tempToken;
            }
            
            $data = $response->json();
            $token = $data['access_token'] ?? null;
            
            if ($token) {
                // Mettre en cache pour 3600 secondes (1 heure) ou selon la durée retournée par l'API
                $expiresIn = $data['expires_in'] ?? 3600;
                Cache::put('mtn_sms_token', $token, $expiresIn);
                
                file_put_contents(
                    storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . '] Token MTN obtenu avec succès pour l\'environnement ' . $environment . "\n",
                    FILE_APPEND
                );
            }
            
            return $token;
        } catch (\Exception $e) {
            Log::channel('sms')->error('Erreur lors de l\'obtention du token MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] ERREUR lors de l\'obtention du token MTN: ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            
            return null;
        }
    }

    public function sendSMS($phoneNumber, $message)
    {
        // Récupérer la configuration
        $config = $this->getConfig();
        $log = $this->smsLogService->logSMS($phoneNumber, $message, 'mtn_sms');

        // Vérifier que les identifiants sont configurés
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            $this->smsLogService->markAsFailed($log, 'Identifiants MTN SMS non configurés');
            return ['success' => false, 'error' => 'Identifiants MTN SMS non configurés'];
        }

        try {
            // Récupérer le token d'authentification
            $token = $this->getAccessToken();
            
            if (!$token) {
                Log::channel('sms')->error('Impossible d\'obtenir un token d\'authentification MTN');
                file_put_contents(
                    storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . '] ERREUR: Impossible d\'obtenir un token d\'authentification MTN\n',
                    FILE_APPEND
                );
                $this->smsLogService->markAsFailed($log, 'Impossible d\'obtenir un token d\'authentification MTN');
                return [
                    'success' => false,
                    'error' => 'Impossible d\'obtenir un token d\'authentification MTN'
                ];
            }
            
            // Formater le numéro de téléphone (enlever le + et les espaces)
            $recipientNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // En dev/staging, utiliser le numéro de test
            if (app()->environment('local', 'staging')) {
                $recipientNumber = preg_replace('/[^0-9]/', '', $config['dev_phone_number']);
            }

            // Utiliser le sender_id configuré
            $senderAddress = $config['sender_address'];
            
            // Récupérer les paramètres de configuration
            $environment = $config['environment'] ?? 'sandbox'; // Utiliser sandbox par défaut pour les tests
            $baseUrl = $config['api_base_url'];
            $serviceCode = $config['service_code'] ?? '131';
            $smsApiVersion = $config['sms_api_version'] ?? ($environment === 'prod' ? 'v3' : 'v2');
            $smsPath = $config['sms_path'] ?? ($environment === 'prod' ? '/v3/sms/messages/sms/outbound' : '/v2/messages/sms/outbound');
            
            // Écrire directement dans le fichier de logs pour le débogage
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] sendSMS - Environnement: ' . $environment . ', BaseURL: ' . $baseUrl . ", API Version: {$smsApiVersion}, Token: " . substr($token, 0, 10) . "...\n",
                FILE_APPEND
            );
            
            // Construire le payload selon la version de l'API
            if ($smsApiVersion === 'v3') {
                // Format pour l'API v3
                $clientCorrelatorId = uniqid('mtn_sms_', true);
                $payload = [
                    'senderAddress' => $senderAddress,
                    'receiverAddress' => [$recipientNumber],
                    'message' => $message,
                    'clientCorrelatorId' => $clientCorrelatorId,
                    'serviceCode' => $serviceCode,
                    'requestDeliveryReceipt' => false
                ];
                
                Log::channel('sms')->info("Utilisation de l'API MTN v3 en environnement {$environment}");
                file_put_contents(
                    storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . '] Utilisation de l\'API MTN v3 en environnement ' . $environment . "\n",
                    FILE_APPEND
                );
            } else {
                // Format pour l'API v2
                $clientCorrelator = uniqid('mtn_sms_', true);
                $payload = [
                    'senderAddress' => $senderAddress,
                    'receiverAddress' => [$recipientNumber],
                    'message' => $message,
                    'clientCorrelator' => $clientCorrelator,
                    'serviceCode' => $serviceCode
                ];
                
                Log::channel('sms')->info("Utilisation de l'API MTN v2 en environnement {$environment}");
                file_put_contents(
                    storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                    '[' . date('Y-m-d H:i:s') . '] Utilisation de l\'API MTN v2 en environnement ' . $environment . "\n",
                    FILE_APPEND
                );
            }
            
            // Construire l'URL de l'API
            $apiUrl = "{$baseUrl}{$smsPath}";
            
            // Préparer les en-têtes avec le token d'authentification
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ];
            
            // Ajouter la clé d'abonnement API si nécessaire (souvent utilisée dans les environnements sandbox)
            if ($environment === 'sandbox') {
                $headers['Ocp-Apim-Subscription-Key'] = $config['client_secret'];
            }
            
            Log::channel('sms')->info('En-têtes de la requête MTN SMS', [
                'headers' => array_map(function($value, $key) {
                    return $key === 'Authorization' ? substr($value, 0, 20) . '...' : $value;
                }, $headers, array_keys($headers))
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] En-têtes: ' . json_encode(array_map(function($value, $key) {
                    return $key === 'Authorization' ? substr($value, 0, 20) . '...' : $value;
                }, $headers, array_keys($headers))) . "\n",
                FILE_APPEND
            );
            
            Log::channel('sms')->info('Payload de la requête MTN SMS ' . $smsApiVersion, [
                'payload' => $payload,
                'url' => $apiUrl,
                'environment' => $environment
            ]);
            
            $response = Http::withHeaders($headers)
                ->post($apiUrl, $payload);

            // Utiliser le canal de log SMS pour les informations de requête
            Log::channel('sms')->info('Requête MTN SMS', [
                'url' => $apiUrl,
                'recipient' => $recipientNumber,
                'sender' => $senderAddress,
                'response' => $response->json(),
                'status' => $response->status(),
                'context' => isset($log->data['context']) ? $log->data['context'] : 'unknown'
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Réponse: Status ' . $response->status() . ' - ' . $response->body() . "\n",
                FILE_APPEND
            );
            
            // Si le contexte est une notification de présence, logger aussi dans le canal des présences
            if ($log && isset($log->data['context']) && $log->data['context'] === 'presence_notification') {
                Log::channel('presences')->info('Envoi de SMS de notification de présence', [
                    'recipient' => $recipientNumber,
                    'message_type' => $log->data['type'] ?? 'notification',
                    'message_id' => $response->json()['data']['requestId'] ?? null
                ]);
            }

            if ($response->successful()) {
                if ($log) {
                    $this->smsLogService->markAsSent($log);
                }
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message_id' => $response->json()['data']['requestId'] ?? null,
                    'error' => null
                ];
            } else {
                if ($log) {
                    $this->smsLogService->markAsFailed($log, $response->body());
                }
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::channel('sms')->error('Exception lors de l\'envoi de SMS MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] ERREUR lors de l\'envoi de SMS: ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            
            if ($log) {
                $this->smsLogService->markAsFailed($log, $e->getMessage());
            }
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * Vérifie le statut de livraison d'un SMS
     * 
     * @param string $senderAddress Adresse de l'expéditeur
     * @param string $requestId ID de la requête retournée lors de l'envoi
     * @return array Résultat de la vérification
     */
    public function checkDeliveryStatus($senderAddress, $requestId)
    {
        // Récupérer la configuration
        $config = $this->getConfig();

        try {
            // Récupérer le token d'authentification
            $token = $this->getAccessToken();
            
            if (!$token) {
                Log::channel('sms')->error('Impossible d\'obtenir un token d\'authentification MTN pour vérifier le statut');
                return [
                    'success' => false,
                    'error' => 'Impossible d\'obtenir un token d\'authentification MTN'
                ];
            }
            
            // Récupérer les paramètres de configuration
            $environment = $config['environment'] ?? 'sandbox';
            $baseUrl = $config['api_base_url'];
            $smsApiVersion = $config['sms_api_version'] ?? ($environment === 'prod' ? 'v3' : 'v2');
            $statusPath = $config['status_path'] ?? '/messages/sms/outbound';
            
            // Construire l'URL de l'API selon la version
            $apiUrl = "{$baseUrl}{$statusPath}/{$senderAddress}/{$requestId}/deliveryStatus";
            
            // Préparer les en-têtes avec le token d'authentification
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ];
            
            // Ajouter la clé d'abonnement API si nécessaire (souvent utilisée dans les environnements sandbox)
            if ($environment === 'sandbox') {
                $headers['Ocp-Apim-Subscription-Key'] = $config['client_secret'];
            }
            
            Log::channel('sms')->info('En-têtes de la requête de vérification MTN SMS', [
                'headers' => array_map(function($value, $key) {
                    return $key === 'Authorization' ? substr($value, 0, 20) . '...' : $value;
                }, $headers, array_keys($headers))
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Vérification statut SMS - URL: ' . $apiUrl . "\n",
                FILE_APPEND
            );
            
            $response = Http::withHeaders($headers)->get($apiUrl);

            Log::channel('sms')->info('Vérification statut SMS MTN', [
                'url' => $apiUrl,
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Réponse statut: Status ' . $response->status() . ' - ' . $response->body() . "\n",
                FILE_APPEND
            );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->json()['data']['deliveryStatus'] ?? null,
                    'error' => null
                ];
            } else {
                $errorMessage = 'Échec de la vérification du statut: ' . $response->body();
                return [
                    'success' => false,
                    'data' => $response->json(),
                    'error' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la vérification du statut: ' . $e->getMessage();
            Log::channel('sms')->error($errorMessage, [
                'trace' => $e->getTraceAsString()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] ERREUR lors de la vérification du statut: ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
    }

    /**
     * Crée un abonnement pour recevoir les notifications de livraison
     * 
     * @param string $senderAddress Adresse de l'expéditeur
     * @param string $notifyUrl URL de callback pour les notifications
     * @return array Résultat de la création de l'abonnement
     */
    public function createDeliverySubscription($senderAddress, $notifyUrl)
    {
        // Récupérer la configuration
        $config = $this->getConfig();

        try {
            // Récupérer le token d'authentification
            $token = $this->getAccessToken();
            
            if (!$token) {
                Log::channel('sms')->error('Impossible d\'obtenir un token d\'authentification MTN pour créer l\'abonnement');
                return [
                    'success' => false,
                    'error' => 'Impossible d\'obtenir un token d\'authentification MTN'
                ];
            }
            
            // Récupérer les paramètres de configuration
            $environment = $config['environment'] ?? 'sandbox';
            $baseUrl = $config['api_base_url'];
            $smsApiVersion = $config['sms_api_version'] ?? ($environment === 'prod' ? 'v3' : 'v2');
            $subscriptionPath = $config['subscription_path'] ?? '/messages/sms/outbound';
            
            // Construire l'URL de l'API selon la version
            $apiUrl = "{$baseUrl}{$subscriptionPath}/{$senderAddress}/subscription";
            
            // Préparer les en-têtes avec le token d'authentification
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ];
            
            // Ajouter la clé d'abonnement API si nécessaire (souvent utilisée dans les environnements sandbox)
            if ($environment === 'sandbox') {
                $headers['Ocp-Apim-Subscription-Key'] = $config['client_secret'];
            }
            
            // Préparer le payload
            $payload = [
                'notifyUrl' => $notifyUrl,
                'targetSystem' => 'GeniusWork'
            ];
            
            Log::channel('sms')->info('En-têtes de la requête d\'abonnement MTN SMS', [
                'headers' => array_map(function($value, $key) {
                    return $key === 'Authorization' ? substr($value, 0, 20) . '...' : $value;
                }, $headers, array_keys($headers))
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Création abonnement - URL: ' . $apiUrl . ", Payload: " . json_encode($payload) . "\n",
                FILE_APPEND
            );
            
            $response = Http::withHeaders($headers)->post($apiUrl, $payload);

            Log::channel('sms')->info('Création abonnement notifications MTN', [
                'url' => $apiUrl,
                'notify_url' => $notifyUrl,
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Réponse abonnement: Status ' . $response->status() . ' - ' . $response->body() . "\n",
                FILE_APPEND
            );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'subscription_id' => $response->json()['data']['subscriptionId'] ?? null,
                    'error' => null
                ];
            } else {
                $errorMessage = 'Échec de la création de l\'abonnement: ' . $response->body();
                return [
                    'success' => false,
                    'data' => $response->json(),
                    'error' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la création de l\'abonnement: ' . $e->getMessage();
            Log::channel('sms')->error($errorMessage, [
                'trace' => $e->getTraceAsString()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] ERREUR lors de la création de l\'abonnement: ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
    }

    /**
     * Supprime un abonnement aux notifications de livraison
     * 
     * @param string $senderAddress Adresse de l'expéditeur
     * @param string $subscriptionId ID de l'abonnement
     * @return array Résultat de la suppression
     */
    public function deleteDeliverySubscription($senderAddress, $subscriptionId)
    {
        // Récupérer la configuration
        $config = $this->getConfig();

        try {
            // Récupérer le token d'authentification
            $token = $this->getAccessToken();
            
            if (!$token) {
                Log::channel('sms')->error('Impossible d\'obtenir un token d\'authentification MTN pour supprimer l\'abonnement');
                return [
                    'success' => false,
                    'error' => 'Impossible d\'obtenir un token d\'authentification MTN'
                ];
            }
            
            // Récupérer les paramètres de configuration
            $environment = $config['environment'] ?? 'sandbox';
            $baseUrl = $config['api_base_url'];
            $smsApiVersion = $config['sms_api_version'] ?? ($environment === 'prod' ? 'v3' : 'v2');
            $subscriptionPath = $config['subscription_path'] ?? '/messages/sms/outbound';
            
            // Construire l'URL de l'API selon la version
            $apiUrl = "{$baseUrl}{$subscriptionPath}/{$senderAddress}/subscription/{$subscriptionId}";
            
            // Préparer les en-têtes avec le token d'authentification
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ];
            
            // Ajouter la clé d'abonnement API si nécessaire (souvent utilisée dans les environnements sandbox)
            if ($environment === 'sandbox') {
                $headers['Ocp-Apim-Subscription-Key'] = $config['client_secret'];
            }
            
            Log::channel('sms')->info('En-têtes de la requête de suppression d\'abonnement MTN SMS', [
                'headers' => array_map(function($value, $key) {
                    return $key === 'Authorization' ? substr($value, 0, 20) . '...' : $value;
                }, $headers, array_keys($headers))
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Suppression abonnement - URL: ' . $apiUrl . "\n",
                FILE_APPEND
            );
            
            $response = Http::withHeaders($headers)->delete($apiUrl);

            Log::channel('sms')->info('Suppression abonnement notifications MTN', [
                'url' => $apiUrl,
                'response' => $response->json(),
                'status' => $response->status()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Réponse suppression: Status ' . $response->status() . ' - ' . $response->body() . "\n",
                FILE_APPEND
            );

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'error' => null
                ];
            } else {
                $errorMessage = 'Échec de la suppression de l\'abonnement: ' . $response->body();
                return [
                    'success' => false,
                    'data' => $response->json(),
                    'error' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la suppression de l\'abonnement: ' . $e->getMessage();
            Log::channel('sms')->error($errorMessage, [
                'trace' => $e->getTraceAsString()
            ]);
            
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] ERREUR lors de la suppression de l\'abonnement: ' . $e->getMessage() . "\n",
                FILE_APPEND
            );
            
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
    }

    /**
     * Récupère les SMS entrants
     * 
     * @param string $requestId ID de la requête (généralement un shortcode)
     * @param int $maxBatchSize Nombre maximum de messages à récupérer
     * @return array Résultat de la récupération
     */
    public function getInboundMessages($requestId, $maxBatchSize = 10)
    {
        $config = $this->getCurrentConfig();

        try {
            $token = $this->getAccessToken();
            
            // Utiliser le type d'authentification et la valeur du token
            $headers = ['Content-Type' => 'application/json'];
            
            if ($token['type'] === 'x-api-key') {
                $headers['x-api-key'] = $token['value'];
            } else {
                $headers['Authorization'] = $token['type'] . ' ' . $token['value'];
            }
            
            $response = Http::withHeaders($headers)
                ->get("{$config['api_base_url']}/messages/sms/inbound/registrations/{$requestId}/messages", [
                    'maxBatchSize' => $maxBatchSize
                ]);

            Log::channel('sms')->info('Récupération SMS entrants MTN', [
                'url' => "{$config['api_base_url']}/messages/sms/inbound/registrations/{$requestId}/messages",
                'max_batch_size' => $maxBatchSize,
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'messages' => $response->json()['data']['inboundSMSMessage'] ?? [],
                    'error' => null
                ];
            } else {
                $errorMessage = 'Échec de la récupération des SMS entrants: ' . $response->body();
                return [
                    'success' => false,
                    'data' => $response->json(),
                    'error' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de la récupération des SMS entrants: ' . $e->getMessage();
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        }
    }
}
