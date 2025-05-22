<?php

namespace App\Services\SMS;

interface SMSServiceInterface
{
    /**
     * Envoie un SMS de bienvenue à l'utilisateur
     *
     * @param \App\Models\User $user L'utilisateur
     * @param \App\Models\Entreprise $entreprise L'entreprise
     * @param \App\Models\Abonnement $abonnement L'abonnement
     * @return array Résultat de l'envoi
     */
    public function sendUserWelcomeSMS($user, $entreprise, $abonnement);

    /**
     * Envoie un SMS de bienvenue à l'entreprise
     *
     * @param \App\Models\User $user L'utilisateur administrateur
     * @param \App\Models\Entreprise $entreprise L'entreprise
     * @param \App\Models\Abonnement $abonnement L'abonnement
     * @return array Résultat de l'envoi
     */
    public function sendCompanyWelcomeSMS($user, $entreprise, $abonnement);
}
