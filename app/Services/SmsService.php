<?php

namespace App\Services;

use App\Services\SMS\OrangeSMSService;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $orangeSmsService;

    /**
     * Constructeur
     *
     * @param OrangeSMSService $orangeSmsService
     */
    public function __construct(OrangeSMSService $orangeSmsService)
    {
        $this->orangeSmsService = $orangeSmsService;
    }

    /**
     * Envoie un code OTP par SMS
     *
     * @param string $phoneNumber Numéro de téléphone destinataire
     * @param string $otpCode Code OTP à envoyer
     * @return bool Succès de l'envoi
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        try {
            // Formater le message
            $message = "Votre code de vérification GENIUS WORK est: {$otpCode}. Il est valable pendant 10 minutes.";
            
            // Envoyer le SMS via le service Orange
            $response = $this->orangeSmsService->sendSMS($phoneNumber, $message);
            
            if ($response['success']) {
                Log::info("SMS OTP envoyé avec succès à {$phoneNumber}");
                return true;
            } else {
                Log::error("Échec de l'envoi du SMS OTP: " . ($response['error'] ?? 'Erreur inconnue'));
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'envoi du SMS OTP: " . $e->getMessage());
            return false;
        }
    }
}
