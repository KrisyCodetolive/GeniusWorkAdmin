<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class EmailLogger
{
    /**
     * Canal de log spécifique pour les emails
     * 
     * @var string
     */
    protected $channel = 'emails';

    /**
     * Enregistre un log d'information sur l'envoi d'un email
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
     * Enregistre un log d'erreur sur l'envoi d'un email
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
     * Enregistre un log de succès pour l'envoi d'un email utilisateur
     * 
     * @param int $userId
     * @param string $email
     * @return void
     */
    public function userEmailSent($userId, $email)
    {
        $this->info('Email de bienvenue envoyé à l\'utilisateur', [
            'user_id' => $userId,
            'email' => $email,
            'timestamp' => now()->toDateTimeString(),
            'type' => 'user_welcome'
        ]);
    }

    /**
     * Enregistre un log de succès pour l'envoi d'un email entreprise
     * 
     * @param int $entrepriseId
     * @param string $email
     * @return void
     */
    public function companyEmailSent($entrepriseId, $email)
    {
        $this->info('Email de bienvenue envoyé à l\'entreprise', [
            'entreprise_id' => $entrepriseId,
            'email' => $email,
            'timestamp' => now()->toDateTimeString(),
            'type' => 'company_welcome'
        ]);
    }

    /**
     * Enregistre un log d'erreur pour l'envoi d'un email
     * 
     * @param \Exception $exception
     * @param array $context
     * @return void
     */
    public function emailError(\Exception $exception, array $context = [])
    {
        $this->error('Erreur lors de l\'envoi d\'email', array_merge([
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toDateTimeString()
        ], $context));
    }
}
