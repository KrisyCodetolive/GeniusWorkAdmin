<?php

namespace App\Notifications;

use App\Models\Conge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CongeStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Conge $conge;
    protected ?string $pdfPath;

    /**
     * Create a new notification instance.
     */
    public function __construct(Conge $conge, ?string $pdfPath = null)
    {
        $this->conge = $conge;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $dateDebut = Carbon::parse($this->conge->date_debut)->format('d/m/Y');
        $dateFin = Carbon::parse($this->conge->date_fin)->format('d/m/Y');
        
        $mail = (new MailMessage)
            ->subject('Mise à jour de votre demande de congé')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre demande de congé du ' . $dateDebut . ' au ' . $dateFin . ' a été ' . $this->getStatutText($this->conge->statut) . '.');
        
        if ($this->conge->commentaire_validation) {
            $mail->line('Commentaire : ' . $this->conge->commentaire_validation);
        }
        
        $mail->action('Voir les détails', url('/admin/conges/' . $this->conge->id));
        
        // Joindre le PDF s'il existe
        if ($this->pdfPath && Storage::disk('public')->exists($this->pdfPath)) {
            $mail->attach(Storage::disk('public')->path($this->pdfPath), [
                'as' => 'attestation_conge.pdf',
                'mime' => 'application/pdf',
            ]);
        }
        
        return $mail->line('Merci d\'utiliser notre application GENIUS WORK!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $dateDebut = Carbon::parse($this->conge->date_debut)->format('d/m/Y');
        $dateFin = Carbon::parse($this->conge->date_fin)->format('d/m/Y');
        
        return [
            'conge_id' => $this->conge->id,
            'titre' => 'Mise à jour de votre demande de congé',
            'message' => 'Votre demande de congé du ' . $dateDebut . ' au ' . $dateFin . ' a été ' . $this->getStatutText($this->conge->statut) . '.',
            'statut' => $this->conge->statut,
            'pdf_path' => $this->pdfPath,
        ];
    }
    
    /**
     * Obtient le texte correspondant au statut
     */
    private function getStatutText(string $statut): string
    {
        return match ($statut) {
            'en_attente' => 'mise en attente',
            'approuve' => 'approuvée',
            'rejete' => 'rejetée',
            'annule' => 'annulée',
            default => 'mise à jour',
        };
    }
}
