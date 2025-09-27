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
     * Envoie un code OTP par SMS avec un message sécurisé
     *
     * @param string $phoneNumber Numéro de téléphone destinataire
     * @param string $otpCode Code OTP à envoyer
     * @return bool Succès de l'envoi
     */
    public function sendOtp(string $phoneNumber, string $otpCode): bool
    {
        try {
            // Formater le message avec un format plus professionnel
            $message = "[GENIUS WORK] Votre code de vérification est {$otpCode}. Ne le partagez avec personne. Valable 10 minutes.";
            
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
