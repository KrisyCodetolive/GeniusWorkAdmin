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
        
        Log::info('Orange SMS Config par défaut chargée', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'dev_phone_number' => $this->devPhoneNumber
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
            ];
        }

        return [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'dev_phone_number' => $this->devPhoneNumber,
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
        ->post('https://api.orange.com/oauth/v3/token', [
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

    public function sendSMS($phoneNumber, $message)
    {
        $config = $this->getCurrentConfig();
        $log = $this->smsLogService->logSMS($phoneNumber, $message, 'orange_sms');

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            $this->smsLogService->markAsFailed($log, 'Identifiants Orange SMS non configurés');
            return ['success' => false, 'error' => 'Identifiants Orange SMS non configurés'];
        }

        try {
            $token = $this->getAccessToken();
            
            // Formater le numéro de téléphone (enlever le + et les espaces)
            $recipientNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // En dev/staging, utiliser le numéro de test
            if (app()->environment('local', 'staging')) {
                $recipientNumber = preg_replace('/[^0-9]/', '', $config['dev_phone_number']);
            }

            // Utiliser le sender_id numérique pour l'API
            $senderId = $config['dev_phone_number'];
            
            $response = Http::withToken($token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://api.orange.com/smsmessaging/v1/outbound/tel%3A{$senderId}/requests", [
                    'outboundSMSMessageRequest' => [
                        'address' => "tel:+{$recipientNumber}",
                        'senderAddress' => "tel:{$senderId}",
                        'outboundSMSTextMessage' => [
                            'message' => $message
                        ]
                    ]
                ]);

            Log::info('Requête Orange SMS', [
                'url' => "https://api.orange.com/smsmessaging/v1/outbound/tel%3A{$senderId}/requests",
                'recipient' => $recipientNumber,
                'sender' => $senderId,
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                $this->smsLogService->markAsSent($log);
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'message_id' => $response->json()['requestId'] ?? null,
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
}
