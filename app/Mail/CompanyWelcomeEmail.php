<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $entreprise;
    public $abonnement;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return void
     */
    public function __construct(User $user, Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->user = $user;
        $this->entreprise = $entreprise;
        $this->abonnement = $abonnement;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Bienvenue sur GENIUS WORK - Votre entreprise est maintenant active')
                    ->markdown('emails.company-welcome')
                    ->with([
                        'user' => $this->user,
                        'entreprise' => $this->entreprise,
                        'abonnement' => $this->abonnement,
                        'planNom' => $this->abonnement->planAbonnement->nom,
                        'dateDebut' => $this->abonnement->date_debut->format('d/m/Y'),
                        'dateFin' => $this->abonnement->date_fin->format('d/m/Y'),
                        'supportEmail' => config('mail.support_email', 'support@GENIUS WORK.com'),
                        'dashboardUrl' => url('/admin')
                    ]);
    }
}
