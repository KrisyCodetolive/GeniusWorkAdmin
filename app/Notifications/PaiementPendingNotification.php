<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Notifications\Messages\MailMessage;

class PaiementPendingNotification extends PaiementStatusNotification
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
            'Votre paiement est en attente de confirmation.',
            'Nous vous informerons dès que votre paiement sera confirmé.'
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
        
        // Instructions spécifiques selon la méthode de paiement
        if ($this->paiement->methode === Paiement::METHODE_VIREMENT) {
            $mailMessage->line('Veuillez effectuer un virement bancaire avec les informations suivantes:')
                ->line('Banque: BANK_NAME')
                ->line('IBAN: BANK_IBAN')
                ->line('BIC: BANK_BIC')
                ->line('Référence: ' . $this->paiement->reference);
        } elseif ($this->paiement->methode === Paiement::METHODE_CHEQUE) {
            $mailMessage->line('Veuillez envoyer votre chèque à l\'adresse suivante:')
                ->line('GENIUS WORK')
                ->line('123 Rue de l\'Innovation')
                ->line('01234 Ville, Pays')
                ->line('Référence à indiquer: ' . $this->paiement->reference);
        }
        
        return $mailMessage;
    }
}
