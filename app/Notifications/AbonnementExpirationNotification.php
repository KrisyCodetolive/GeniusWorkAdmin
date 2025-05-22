<?php

namespace App\Notifications;

use App\Models\Abonnement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbonnementExpirationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $abonnement;
    protected $daysRemaining;

    /**
     * Create a new notification instance.
     *
     * @param Abonnement $abonnement
     * @param int $daysRemaining
     */
    public function __construct(Abonnement $abonnement, int $daysRemaining)
    {
        $this->abonnement = $abonnement;
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
            ? "Votre abonnement GENIUS WORK expire dans {$this->daysRemaining} jours" 
            : "Votre abonnement GENIUS WORK a expiré";

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . $notifiable->name . ',');

        if ($this->daysRemaining > 0) {
            $mailMessage->line("Votre abonnement GENIUS WORK expire dans {$this->daysRemaining} jours.");
            $mailMessage->line("Pour continuer à bénéficier de tous les avantages de GENIUS WORK, veuillez renouveler votre abonnement avant son expiration.");
        } else {
            $mailMessage->line("Votre abonnement GENIUS WORK a expiré.");
            $mailMessage->line("Pour réactiver votre compte et récupérer l'accès à toutes les fonctionnalités, veuillez renouveler votre abonnement dès maintenant.");
        }

        // Ajouter les détails de l'abonnement
        $mailMessage->line('Détails de l\'abonnement:')
            ->line('Plan: ' . $this->abonnement->plan)
            ->line('Date de début: ' . $this->abonnement->date_debut->format('d/m/Y'))
            ->line('Date d\'expiration: ' . $this->abonnement->date_fin->format('d/m/Y'))
            ->line('Entreprise: ' . $this->abonnement->entreprise->nom);

        // Ajouter un lien pour renouveler l'abonnement
        $mailMessage->action(
            'Renouveler mon abonnement',
            route('abonnements.renouveler', $this->abonnement->id)
        );

        $mailMessage->line('Si vous avez des questions, n\'hésitez pas à contacter notre service client.')
            ->line('Merci de votre confiance!');

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
        $isExpired = $this->daysRemaining <= 0;
        
        return [
            'abonnement_id' => $this->abonnement->id,
            'entreprise_id' => $this->abonnement->entreprise_id,
            'plan' => $this->abonnement->plan,
            'days_remaining' => $this->daysRemaining,
            'is_expired' => $isExpired,
            'message' => $isExpired 
                ? "Votre abonnement a expiré" 
                : "Votre abonnement expire dans {$this->daysRemaining} jours",
            'url' => route('abonnements.renouveler', $this->abonnement->id)
        ];
    }
}
