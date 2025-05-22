<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Log;

class SMSLogger
{
    /**
     * Canal de log spécifique pour les SMS
     * 
     * @var string
     */
    protected $channel = 'sms';

    /**
     * Enregistre un log d'information sur l'envoi d'un SMS
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        Log::channel($this->channel)->info($message, $context);
    }

    /**
     * Enregistre un log d'erreur sur l'envoi d'un SMS
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        Log::channel($this->channel)->error($message, $context);
    }

    /**
     * Enregistre un log de succès pour l'envoi d'un SMS utilisateur
     * 
     * @param int $userId
     * @param string $phoneNumber
     * @return void
     */
    public function userSMSSent($userId, $phoneNumber)
    {
        $this->info('SMS de bienvenue envoyé à l\'utilisateur', [
            'user_id' => $userId,
            'phone_number' => $phoneNumber,
            'timestamp' => now()->toDateTimeString(),
            'type' => 'user_welcome'
        ]);
    }

    /**
     * Enregistre un log de succès pour l'envoi d'un SMS entreprise
     * 
     * @param int $entrepriseId
     * @param string $phoneNumber
     * @return void
     */
    public function companySMSSent($entrepriseId, $phoneNumber)
    {
        $this->info('SMS de bienvenue envoyé à l\'entreprise', [
            'entreprise_id' => $entrepriseId,
            'phone_number' => $phoneNumber,
            'timestamp' => now()->toDateTimeString(),
            'type' => 'company_welcome'
        ]);
    }

    /**
     * Enregistre un log d'erreur pour l'envoi d'un SMS
     * 
     * @param \Exception $exception
     * @param array $context
     * @return void
     */
    public function smsError(\Exception $exception, array $context = [])
    {
        $this->error('Erreur lors de l\'envoi de SMS', array_merge([
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toDateTimeString()
        ], $context));
    }

    /**
     * Enregistre un log pour un SMS non envoyé en raison d'un numéro manquant
     * 
     * @param string $type Type de destinataire (user ou company)
     * @param int $id ID de l'utilisateur ou de l'entreprise
     * @return void
     */
    public function missingPhoneNumber($type, $id)
    {
        $this->info('SMS de bienvenue non envoyé : numéro de téléphone manquant', [
            $type . '_id' => $id,
            'timestamp' => now()->toDateTimeString(),
            'type' => $type . '_welcome'
        ]);
    }
}
