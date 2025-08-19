<?php

namespace App\Services\SMS;

use App\Services\SMS\SMSLogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrangeSMSService
{
    protected $clientId;
    protected $clientSecret;
    protected $devPhoneNumber;
    protected $apiBaseUrl;
    protected $accessToken;
    protected $tokenExpiry;
    protected $smsLogService;
    protected $customConfig = null;

    public function __construct(SMSLogService $smsLogService)
    {
        // Configuration par défaut
        $this->clientId = Config::get('sms.orange.client_id');
        $this->clientSecret = Config::get('sms.orange.client_secret');
        $this->devPhoneNumber = Config::get('sms.orange.dev_phone_number');
        $this->apiBaseUrl = Config::get('sms.orange.api_base_url', 'https://api.orange.com');
        
        // Utiliser le canal de log SMS pour les informations de configuration
        Log::channel('sms')->info('Orange SMS Config par défaut chargée', [
            'client_id' => $this->clientId ? '****' . substr($this->clientId, -4) : null,
            'client_secret' => $this->clientSecret ? '****' . substr($this->clientSecret, -4) : null,
            'dev_phone_number' => $this->devPhoneNumber,
            'api_base_url' => $this->apiBaseUrl
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
                'api_base_url' => $this->customConfig['api_base_url'] ?? $this->apiBaseUrl,
            ];
        }

        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'dev_phone_number' => $this->devPhoneNumber,
            'api_base_url' => $this->apiBaseUrl,
        ];
    }

    protected function getAccessToken()
    {
        $config = $this->getCurrentConfig();
        $cacheKey = "orange_sms_token_" . md5($config['client_id'] . $config['client_secret']);

        // Vérifier le cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])
        ->asForm()
        ->post($config['api_base_url'] . '/oauth/v3/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret']
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $token = $data['access_token'];
            $expiresIn = $data['expires_in'] - 60; // 60 secondes de marge
            
            Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));
            
            return $token;
        }

        throw new \Exception('Échec de l\'obtention du token Orange SMS: ' . $response->body());
    }

    public function sendSMS($phoneNumber, $message, $options = [])
    {
        $config = $this->getCurrentConfig();
        $log = $this->smsLogService->logSMS($phoneNumber, $message, 'orange_sms');

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            $this->smsLogService->markAsFailed($log, 'Identifiants Orange SMS non configurés');
            return ['success' => false, 'error' => 'Identifiants Orange SMS non configurés'];
        }

        try {
            // Écrire dans le fichier journal quotidien
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Début envoi SMS Orange - Numéro: ' . $phoneNumber . "\n",
                FILE_APPEND
            );
            
            $token = $this->getAccessToken();
            
            // Formater le numéro de téléphone correctement pour Orange API
            $recipientNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // En dev/staging, utiliser le numéro de test
            if (app()->environment('local', 'staging')) {
                $recipientNumber = preg_replace('/[^0-9]/', '', $config['dev_phone_number']);
            }

            // Formater le numéro d'expéditeur (sans le +)
            $senderIdRaw = preg_replace('/[^0-9]/', '', $config['dev_phone_number']);
            
            // Générer un identifiant unique pour la corrélation client (pour éviter les doublons)
            $clientCorrelator = $options['client_correlator'] ?? uniqid('sms_', true);
            
            // Utiliser un nom d'expéditeur personnalisé si fourni, sinon utiliser "GENIUS" par défaut
            $senderName = $options['sender_name'] ?? 'GENIUS';
            
            // Construire l'URL avec le bon format d'encodage pour le numéro d'expéditeur
            // Selon la documentation, on peut utiliser un numéro générique pour le pays
            $apiUrl = "{$config['api_base_url']}/smsmessaging/v1/outbound/tel%3A%2B{$senderIdRaw}/requests";
            
            // Construire le payload selon la documentation Swagger
            $payload = [
                'outboundSMSMessageRequest' => [
                    'address' => ["tel:+{$recipientNumber}"],  // Format tableau attendu par l'API
                    'senderAddress' => "tel:+{$senderIdRaw}",
                    'outboundSMSTextMessage' => [
                        'message' => $message
                    ],
                    'clientCorrelator' => $clientCorrelator
                ]
            ];
            
            // Ajouter le nom d'expéditeur (toujours présent car valeur par défaut)
            $payload['outboundSMSMessageRequest']['senderName'] = $senderName;
            
            // Ajouter la demande de notification de livraison si nécessaire
            if (!empty($options['receipt_request'])) {
                $payload['outboundSMSMessageRequest']['receiptRequest'] = [
                    'callbackData' => $options['receipt_request']
                ];
            }
            
            // Journaliser la requête avant envoi
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Requête Orange SMS - URL: ' . $apiUrl . "\n" .
                '[' . date('Y-m-d H:i:s') . '] Payload: ' . json_encode($payload, JSON_PRETTY_PRINT) . "\n",
                FILE_APPEND
            );
            
            // Envoyer la requête avec le token d'authentification
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($apiUrl, $payload);

            // Journaliser la réponse complète
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] Réponse Orange SMS - Status: ' . $response->status() . "\n" .
                '[' . date('Y-m-d H:i:s') . '] Corps: ' . $response->body() . "\n",
                FILE_APPEND
            );
            
            // Utiliser le canal de log SMS pour les informations de requête
            Log::channel('sms')->info('Requête Orange SMS', [
                'url' => $apiUrl,
                'recipient' => $recipientNumber,
                'sender' => $senderIdRaw,
                'payload' => $payload,
                'response_status' => $response->status(),
                'response' => $response->json()
            ]);
            
            // Si le contexte est une notification de présence, logger aussi dans le canal des présences
            if (isset($log->data['context']) && $log->data['context'] === 'presence_notification') {
                Log::channel('presences')->info('Envoi de SMS de notification de présence', [
                    'recipient' => $recipientNumber,
                    'message_type' => $log->data['type'] ?? 'notification',
                    'message_id' => $response->json()['outboundSMSMessageRequest']['resourceURL'] ?? null
                ]);
            }

            if ($response->successful()) {
                // Extraire l'ID du message à partir de l'URL de ressource
                $resourceURL = $response->json()['outboundSMSMessageRequest']['resourceURL'] ?? '';
                $messageId = null;
                
                // Extraire l'ID du message à partir de l'URL (dernier segment)
                if (!empty($resourceURL)) {
                    $urlParts = explode('/', $resourceURL);
                    $messageId = end($urlParts);
                }
                
                $this->smsLogService->markAsSent($log);
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message_id' => $messageId,
                    'error' => null
                ];
            } else {
                // Gérer les différents codes d'erreur selon la documentation
                $statusCode = $response->status();
                $responseData = $response->json();
                
                $errorCode = null;
                $errorMessage = 'Échec de l\'envoi du SMS: ';
                
                // Extraire le code d'erreur et le message selon le format de la réponse
                if (isset($responseData['requestError']['serviceException'])) {
                    $errorCode = $responseData['requestError']['serviceException']['messageId'] ?? null;
                    $errorText = $responseData['requestError']['serviceException']['text'] ?? 'Erreur de service inconnue';
                    $errorMessage .= "[{$errorCode}] {$errorText}";
                } elseif (isset($responseData['requestError']['policyException'])) {
                    $errorCode = $responseData['requestError']['policyException']['messageId'] ?? null;
                    $errorText = $responseData['requestError']['policyException']['text'] ?? 'Erreur de politique inconnue';
                    $errorMessage .= "[{$errorCode}] {$errorText}";
                } else {
                    $errorMessage .= 'Code HTTP ' . $statusCode . ' - ' . $response->body();
                }
                
                $this->smsLogService->markAsFailed($log, $errorMessage);
                return [
                    'success' => false,
                    'data' => $responseData,
                    'error' => $errorMessage,
                    'error_code' => $errorCode
                ];
            }
        } catch (\Exception $e) {
            $errorMessage = 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage();
            
            // Journaliser l'erreur
            file_put_contents(
                storage_path('logs/sms-' . date('Y-m-d') . '.log'),
                '[' . date('Y-m-d H:i:s') . '] ERREUR Orange SMS: ' . $e->getMessage() . "\n" .
                '[' . date('Y-m-d H:i:s') . '] Trace: ' . $e->getTraceAsString() . "\n",
                FILE_APPEND
            );
            
            Log::channel('sms')->error($errorMessage, [
                'trace' => $e->getTraceAsString()
            ]);
            
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
}
