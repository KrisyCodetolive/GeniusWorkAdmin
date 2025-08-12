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
        // Configuration par défaut
        $this->clientId = Config::get('sms.mtn.client_id');
        $this->clientSecret = Config::get('sms.mtn.client_secret');
        $this->devPhoneNumber = Config::get('sms.mtn.dev_phone_number');
        $this->apiBaseUrl = Config::get('sms.mtn.api_base_url', 'https://api.mtn.com/v2');
        $this->senderAddress = Config::get('sms.mtn.sender_address');
        
        // Utiliser le canal de log SMS pour les informations de configuration
        Log::channel('sms')->info('MTN SMS Config par défaut chargée', [
            'client_id' => $this->clientId ? '****' . substr($this->clientId, -4) : null,
            'client_secret' => $this->clientSecret ? '****' . substr($this->clientSecret, -4) : null,
            'dev_phone_number' => $this->devPhoneNumber,
            'sender_address' => $this->senderAddress
        ]);
        
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

        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'dev_phone_number' => $this->devPhoneNumber,
            'sender_address' => $this->senderAddress,
            'api_base_url' => $this->apiBaseUrl,
        ];
    }

    protected function getAccessToken()
    {
        $config = $this->getCurrentConfig();
        $cacheKey = "mtn_sms_token_" . md5($config['client_id'] . $config['client_secret']);

        // Vérifier le cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $tokenUrl = "https://api.mtn.com/v1/oauth/access_token/accesstoken?grant_type=client_credentials";
        
        $response = Http::withBasicAuth($config['client_id'], $config['client_secret'])
            ->post($tokenUrl);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['access_token'];
            $expiresIn = $data['expires_in'] - 60; // 60 secondes de marge
            
            Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));
            
            return $token;
        }

        throw new \Exception('Échec de l\'obtention du token MTN SMS: ' . $response->body());
    }

    public function sendSMS($phoneNumber, $message)
    {
        $config = $this->getCurrentConfig();
        $log = $this->smsLogService->logSMS($phoneNumber, $message, 'mtn_sms');

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            $this->smsLogService->markAsFailed($log, 'Identifiants MTN SMS non configurés');
            return ['success' => false, 'error' => 'Identifiants MTN SMS non configurés'];
        }

        try {
            $token = $this->getAccessToken();
            
            // Formater le numéro de téléphone (enlever le + et les espaces)
            $recipientNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // En dev/staging, utiliser le numéro de test
            if (app()->environment('local', 'staging')) {
                $recipientNumber = preg_replace('/[^0-9]/', '', $config['dev_phone_number']);
            }

            // Utiliser le sender_id configuré
            $senderAddress = $config['sender_address'];
            
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$config['api_base_url']}/messages/sms/outbound", [
                    'senderAddress' => $senderAddress,
                    'receiverAddress' => [$recipientNumber],
                    'message' => $message,
                    'clientCorrelator' => uniqid('mtn_sms_')
                ]);

            // Utiliser le canal de log SMS pour les informations de requête
            Log::channel('sms')->info('Requête MTN SMS', [
                'url' => "{$config['api_base_url']}/messages/sms/outbound",
                'recipient' => $recipientNumber,
                'sender' => $senderAddress,
                'response' => $response->json(),
                'context' => 'presence_notification'
            ]);
            
            // Si le contexte est une notification de présence, logger aussi dans le canal des présences
            if (isset($log->data['context']) && $log->data['context'] === 'presence_notification') {
                Log::channel('presences')->info('Envoi de SMS de notification de présence', [
                    'recipient' => $recipientNumber,
                    'message_type' => $log->data['type'] ?? 'notification',
                    'message_id' => $response->json()['data']['requestId'] ?? null
                ]);
            }

            if ($response->successful()) {
                $this->smsLogService->markAsSent($log);
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message_id' => $response->json()['data']['requestId'] ?? null,
                    'error' => null
                ];
            } else {
                $errorMessage = 'Échec de l\'envoi du SMS: ' . $response->body();
                $this->smsLogService->markAsFailed($log, $errorMessage);
                return [
                    'success' => false,
                    'data' => $response->json(),
                    'error' => $errorMessage
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage();
            $this->smsLogService->markAsFailed($log, $errorMessage);
            return [
                'success' => false,
                'error' => $errorMessage
            ];
        } finally {
            // Réinitialiser la config personnalisée après l'envoi
            $this->resetToDefaultConfig();
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
        $config = $this->getCurrentConfig();

        try {
            $token = $this->getAccessToken();
            
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->get("{$config['api_base_url']}/messages/sms/outbound/{$senderAddress}/{$requestId}/deliveryStatus");

            Log::channel('sms')->info('Vérification statut SMS MTN', [
                'url' => "{$config['api_base_url']}/messages/sms/outbound/{$senderAddress}/{$requestId}/deliveryStatus",
                'response' => $response->json()
            ]);

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
        $config = $this->getCurrentConfig();

        try {
            $token = $this->getAccessToken();
            
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$config['api_base_url']}/messages/sms/outbound/{$senderAddress}/subscription", [
                    'notifyUrl' => $notifyUrl,
                    'targetSystem' => 'GeniusWork'
                ]);

            Log::channel('sms')->info('Création abonnement notifications MTN', [
                'url' => "{$config['api_base_url']}/messages/sms/outbound/{$senderAddress}/subscription",
                'notify_url' => $notifyUrl,
                'response' => $response->json()
            ]);

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
        $config = $this->getCurrentConfig();

        try {
            $token = $this->getAccessToken();
            
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->delete("{$config['api_base_url']}/messages/sms/outbound/{$senderAddress}/subscription/{$subscriptionId}");

            Log::channel('sms')->info('Suppression abonnement notifications MTN', [
                'url' => "{$config['api_base_url']}/messages/sms/outbound/{$senderAddress}/subscription/{$subscriptionId}",
                'response' => $response->json()
            ]);

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
            
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
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
