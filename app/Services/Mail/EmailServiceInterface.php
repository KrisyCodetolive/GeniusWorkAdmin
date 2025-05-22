<?php

namespace App\Services\Mail;

interface EmailServiceInterface
{
    /**
     * Envoyer un email
     *
     * @param string|array $to Destinataire(s) de l'email
     * @param object $mailable Instance de Mailable à envoyer
     * @param array $options Options supplémentaires pour l'envoi
     * @return bool Succès de l'envoi
     */
    public function send($to, $mailable, array $options = []): bool;
    
    /**
     * Envoyer un email de bienvenue à un utilisateur
     *
     * @param \App\Models\User $user
     * @param \App\Models\Entreprise $entreprise
     * @param \App\Models\Abonnement $abonnement
     * @return bool
     */
    public function sendUserWelcome($user, $entreprise, $abonnement): bool;
    
    /**
     * Envoyer un email de bienvenue à une entreprise
     *
     * @param \App\Models\User $user
     * @param \App\Models\Entreprise $entreprise
     * @param \App\Models\Abonnement $abonnement
     * @return bool
     */
    public function sendCompanyWelcome($user, $entreprise, $abonnement): bool;
}
