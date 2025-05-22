<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Messages\MailMessage;

class PaiementSuccessNotification extends PaiementStatusNotification
{
    /**
     * Create a new notification instance.
     *
     * @param Paiement $paiement
     */
    public function __construct(Paiement $paiement)
    {
        parent::__construct(
            $paiement,
            'Votre paiement a été traité avec succès!',
            'Votre abonnement a été activé et vous pouvez maintenant accéder à toutes les fonctionnalités de GENIUS WORK.'
        );
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = parent::toMail($notifiable);
        
        // Ajouter des informations spécifiques au succès
        $mailMessage->line('Votre abonnement est maintenant actif jusqu\'au ' . 
            $this->paiement->abonnement->date_fin->format('d/m/Y') . '.');
        
        // Ajouter un lien vers le tableau de bord
        $mailMessage->action(
            'Accéder à votre tableau de bord',
            route('dashboard')
        );
        
        return $mailMessage;
    }
}
