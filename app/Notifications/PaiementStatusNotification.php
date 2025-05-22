<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $paiement;
    protected $statusMessage;
    protected $detailsMessage;

    /**
     * Create a new notification instance.
     *
     * @param Paiement $paiement
     * @param string $statusMessage
     * @param string $detailsMessage
     */
    public function __construct(Paiement $paiement, string $statusMessage, string $detailsMessage = '')
    {
        $this->paiement = $paiement;
        $this->statusMessage = $statusMessage;
        $this->detailsMessage = $detailsMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('Mise à jour du paiement #' . $this->paiement->reference)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->statusMessage);

        if (!empty($this->detailsMessage)) {
            $mailMessage->line($this->detailsMessage);
        }

        // Ajouter les détails du paiement
        $mailMessage->line('Détails du paiement:')
            ->line('Référence: ' . $this->paiement->reference)
            ->line('Montant: ' . number_format($this->paiement->montant, 2) . ' ' . $this->paiement->devise)
            ->line('Méthode: ' . $this->getMethodeLabel($this->paiement->methode))
            ->line('Date: ' . $this->paiement->date_paiement->format('d/m/Y H:i'));

        // Ajouter un lien pour voir les détails
        $mailMessage->action(
            'Voir les détails',
            route('paiements.statut', $this->paiement->reference)
        );

        $mailMessage->line('Merci d\'utiliser GENIUS WORK!');

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'paiement_id' => $this->paiement->id,
            'reference' => $this->paiement->reference,
            'montant' => $this->paiement->montant,
            'devise' => $this->paiement->devise,
            'statut' => $this->paiement->statut,
            'message' => $this->statusMessage,
            'details' => $this->detailsMessage,
            'url' => route('paiements.statut', $this->paiement->reference)
        ];
    }

    /**
     * Get the human-readable label for payment method
     *
     * @param string $methode
     * @return string
     */
    protected function getMethodeLabel(string $methode): string
    {
        $labels = [
            Paiement::METHODE_CARTE => 'Carte bancaire',
            Paiement::METHODE_MOBILE_MONEY => 'Mobile Money',
            Paiement::METHODE_VIREMENT => 'Virement bancaire',
            Paiement::METHODE_CHEQUE => 'Chèque',
            Paiement::METHODE_ESPECES => 'Espèces',
            Paiement::METHODE_AUTRE => 'Autre'
        ];

        return $labels[$methode] ?? $methode;
    }
}
