<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaiementReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $paiement;
    protected $daysRemaining;

    /**
     * Create a new notification instance.
     *
     * @param Paiement $paiement
     * @param int $daysRemaining
     */
    public function __construct(Paiement $paiement, int $daysRemaining)
    {
        $this->paiement = $paiement;
        $this->daysRemaining = $daysRemaining;
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
        $subject = $this->daysRemaining > 0 
            ? "Rappel: Paiement en attente depuis {$this->daysRemaining} jours" 
            : "Action requise: Paiement en attente";

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Nous vous rappelons que votre paiement est toujours en attente.');

        if ($this->daysRemaining > 0) {
            $mailMessage->line("Cela fait {$this->daysRemaining} jours que votre paiement est en attente.");
        } else {
            $mailMessage->line("Votre paiement nécessite votre attention immédiate.");
        }

        // Ajouter les détails du paiement
        $mailMessage->line('Détails du paiement:')
            ->line('Référence: ' . $this->paiement->reference)
            ->line('Montant: ' . number_format($this->paiement->montant, 2) . ' ' . $this->paiement->devise)
            ->line('Date de création: ' . $this->paiement->created_at->format('d/m/Y H:i'));

        // Ajouter un lien pour compléter le paiement
        $mailMessage->action(
            'Compléter le paiement',
            route('workflow.payment', ['reference' => $this->paiement->reference])
        );

        $mailMessage->line('Si vous avez déjà effectué ce paiement, veuillez nous contacter.')
            ->line('Merci d\'utiliser GENIUS WORK!');

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
            'days_remaining' => $this->daysRemaining,
            'message' => "Paiement en attente depuis {$this->daysRemaining} jours",
            'url' => route('workflow.payment', ['reference' => $this->paiement->reference])
        ];
    }
}
