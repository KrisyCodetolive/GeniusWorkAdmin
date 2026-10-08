<?php

namespace App\Notifications;

use App\Models\DemandeDepense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepenseNotification extends Notification
{
    use Queueable;

    private const MESSAGES = [
        'a_valider_comptable' => ['Demande de dépense à valider', 'Une nouvelle demande de dépense attend la validation de la comptabilité.'],
        'a_valider_ceo' => ['Demande de dépense à valider (CEO)', 'Une demande de dépense validée par la comptabilité dépasse le seuil et attend votre validation.'],
        'approuvee' => ['Demande de dépense approuvée', 'Votre demande de dépense a été approuvée.'],
        'a_payer' => ['Dépense approuvée à payer', 'Une demande de dépense a été approuvée et signée par le demandeur : elle peut être décaissée.'],
        'a_signer' => ['Dépense approuvée — signature requise', 'Votre demande de dépense a été approuvée. Signez votre décharge pour autoriser le paiement.'],
        'rejetee' => ['Demande de dépense rejetée', 'Votre demande de dépense a été rejetée.'],
        'renvoyee' => ['Demande de dépense à corriger', 'Votre demande de dépense vous a été renvoyée pour correction.'],
        'payee' => ['Dépense payée', 'Le paiement de votre demande de dépense a été effectué. Pensez à joindre vos reçus pour justifier l\'utilisation des fonds.'],
        'justification_a_verifier' => ['Justification de dépense à vérifier', 'Un demandeur a soumis les reçus d\'une dépense payée pour vérification.'],
        'cloturee' => ['Dépense clôturée', 'Les justificatifs de votre demande de dépense ont été vérifiés. La demande est clôturée.'],
        'justification_renvoyee' => ['Justification à compléter', 'Les justificatifs de votre demande de dépense vous sont renvoyés pour complément.'],
    ];

    public function __construct(
        private DemandeDepense $demande,
        private string $evenement,
        private ?string $motif = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$sujet, $message] = self::MESSAGES[$this->evenement];

        $mail = (new MailMessage)
            ->subject($sujet.' — '.$this->demande->reference)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line($message)
            ->line('Référence : '.$this->demande->reference)
            ->line('Objet : '.$this->demande->objet)
            ->line('Montant : '.number_format((float) $this->demande->montant, 0, ',', ' ').' '.$this->demande->devise);

        if ($this->motif) {
            $mail->line('Motif : '.$this->motif);
        }

        $url = ($notifiable->isComptable() || $notifiable->isSuperAdmin() || $notifiable->isAdmin())
            ? url('/admin/demande-depenses/'.$this->demande->id)
            : route('employe.depenses.show', $this->demande);

        return $mail->action('Voir la demande', $url);
    }
}
