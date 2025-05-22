<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Messages\MailMessage;

class PaiementFailedNotification extends PaiementStatusNotification
{
    /**
     * Create a new notification instance.
     *
     * @param Paiement $paiement
     * @param string $errorMessage
     */
    public function __construct(Paiement $paiement, string $errorMessage = '')
    {
        $details = !empty($errorMessage) 
            ? 'Raison: ' . $errorMessage 
            : 'Veuillez réessayer ou contacter notre service client si le problème persiste.';
            
        parent::__construct(
            $paiement,
            'Votre paiement n\'a pas pu être traité.',
            $details
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
        
        // Ajouter un lien pour réessayer le paiement
        $mailMessage->action(
            'Réessayer le paiement',
            route('workflow.payment', ['reference' => $this->paiement->reference])
        );
        
        // Ajouter les coordonnées du support
        $mailMessage->line('Si vous avez besoin d\'aide, contactez notre service client:')
            ->line('Email: support@GENIUS WORK.com')
            ->line('Téléphone: +123 456 789');
        
        return $mailMessage;
    }
}
