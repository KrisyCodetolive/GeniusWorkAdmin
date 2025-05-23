<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PresenceNotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Les données de la notification.
     *
     * @var array
     */
    public $data;

    /**
     * Crée une nouvelle instance du message.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Construit le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.presence-notification')
            ->with([
                'message' => $this->data['message'],
                'employeur' => $this->data['employeur'],
                'entreprise' => $this->data['entreprise'],
                'type' => $this->data['type'],
                'date' => $this->data['date'],
                'data' => $this->data['data']
            ])
            ->priority(1); // Haute priorité pour les notifications de présence
    }
}
