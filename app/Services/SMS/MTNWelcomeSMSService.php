<?php

namespace App\Services\SMS;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;
use Illuminate\Support\Facades\Log;

class MTNWelcomeSMSService implements SMSServiceInterface
{
    protected $mtnSMSService;

    public function __construct(MTNSMSService $mtnSMSService)
    {
        $this->mtnSMSService = $mtnSMSService;
    }

    /**
     * Envoie un SMS de bienvenue à l'utilisateur
     *
     * @param \App\Models\User $user L'utilisateur
     * @param \App\Models\Entreprise $entreprise L'entreprise
     * @param \App\Models\Abonnement $abonnement L'abonnement
     * @return array Résultat de l'envoi
     */
    public function sendUserWelcomeSMS($user, $entreprise, $abonnement)
    {
        $phoneNumber = $user->telephone;
        
        if (empty($phoneNumber)) {
            return [
                'success' => false,
                'error' => 'Numéro de téléphone non disponible'
            ];
        }
        
        $message = "Bienvenue {$user->prenom} sur GeniusWork! ";
        $message .= "Votre compte a été créé avec succès pour l'entreprise {$entreprise->nom}. ";
        $message .= "Connectez-vous à l'application pour commencer à utiliser nos services.";
        
        Log::channel('sms')->info('Envoi SMS bienvenue utilisateur via MTN', [
            'user_id' => $user->id,
            'entreprise_id' => $entreprise->id,
            'abonnement_id' => $abonnement->id
        ]);
        
        return $this->mtnSMSService->sendSMS($phoneNumber, $message);
    }

    /**
     * Envoie un SMS de bienvenue à l'entreprise
     *
     * @param \App\Models\User $user L'utilisateur administrateur
     * @param \App\Models\Entreprise $entreprise L'entreprise
     * @param \App\Models\Abonnement $abonnement L'abonnement
     * @return array Résultat de l'envoi
     */
    public function sendCompanyWelcomeSMS($user, $entreprise, $abonnement)
    {
        $phoneNumber = $user->telephone;
        
        if (empty($phoneNumber)) {
            return [
                'success' => false,
                'error' => 'Numéro de téléphone non disponible'
            ];
        }
        
        $message = "Félicitations! L'entreprise {$entreprise->nom} a été enregistrée avec succès sur GeniusWork. ";
        $message .= "Votre abonnement {$abonnement->nom} est maintenant actif. ";
        $message .= "Connectez-vous à l'application pour gérer votre espace entreprise.";
        
        Log::channel('sms')->info('Envoi SMS bienvenue entreprise via MTN', [
            'user_id' => $user->id,
            'entreprise_id' => $entreprise->id,
            'abonnement_id' => $abonnement->id
        ]);
        
        return $this->mtnSMSService->sendSMS($phoneNumber, $message);
    }
}
