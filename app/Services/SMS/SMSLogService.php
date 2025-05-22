<?php

namespace App\Services\SMS;

use App\Models\SMSLog;
use Illuminate\Support\Facades\Auth;

class SMSLogService
{
    /**
     * Log un SMS envoyé ou à envoyer
     *
     * @param string $phoneNumber Numéro de téléphone destinataire
     * @param string $message Contenu du message
     * @param string $provider Fournisseur SMS utilisé
     * @param array $additionalData Données supplémentaires
     * @return \App\Models\SMSLog
     */
    public function logSMS($phoneNumber, $message, $provider, array $additionalData = [])
    {
        $data = [
            'phone_number' => $phoneNumber,
            'message' => $message,
            'status' => SMSLog::STATUS_PENDING,
            'notification_type' => $provider,
            'attempts' => 1,
        ];
        
        // Ajouter l'entreprise si l'utilisateur est connecté
        if (Auth::check() && Auth::user()->entreprise_id) {
            $data['entreprise_id'] = Auth::user()->entreprise_id;
        }
        
        // Ajouter les données notifiable si présentes
        if (isset($additionalData['notifiable_id']) && isset($additionalData['notifiable_type'])) {
            $data['notifiable_id'] = $additionalData['notifiable_id'];
            $data['notifiable_type'] = $additionalData['notifiable_type'];
        }
        
        return SMSLog::create($data);
    }

    public function markAsSent(SMSLog $log)
    {
        $log->markAsSent();
    }

    public function markAsFailed(SMSLog $log, string $errorMessage)
    {
        $log->markAsFailed($errorMessage);
    }

    public function getProviderStats($provider, $days = 30)
    {
        return SMSLog::where('notification_type', $provider)
            ->where('created_at', '>=', now()->subDays($days))
            ->get()
            ->groupBy('status')
            ->map(fn($group) => $group->count());
    }
}
