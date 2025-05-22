<?php

namespace App\Console\Commands;

use App\Models\Paiement;
use App\Notifications\PaiementReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaiementReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paiements:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des rappels pour les paiements en attente';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Envoi des rappels de paiement...');
        
        // Récupérer les paiements en attente
        $paiementsEnAttente = Paiement::where('statut', Paiement::STATUT_EN_ATTENTE)
            ->whereNotNull('initiateur_id')
            ->get();
            
        $this->info("Nombre de paiements en attente trouvés: " . $paiementsEnAttente->count());
        
        $count = 0;
        
        foreach ($paiementsEnAttente as $paiement) {
            // Calculer le nombre de jours depuis la création du paiement
            $daysWaiting = Carbon::now()->diffInDays($paiement->created_at);
            
            // Envoyer un rappel si le paiement est en attente depuis plus de 1 jour
            // et si le dernier rappel a été envoyé il y a plus de 3 jours (ou jamais)
            $lastReminder = $paiement->meta_donnees['last_reminder'] ?? null;
            $shouldSendReminder = $daysWaiting >= 1 && 
                ($lastReminder === null || Carbon::now()->diffInDays(Carbon::parse($lastReminder)) >= 3);
                
            if ($shouldSendReminder) {
                $initiateur = $paiement->initiateur;
                
                if ($initiateur) {
                    try {
                        // Envoyer la notification
                        $initiateur->notify(new PaiementReminderNotification($paiement, $daysWaiting));
                        
                        // Mettre à jour la date du dernier rappel
                        $paiement->update([
                            'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                                'last_reminder' => Carbon::now()->toDateTimeString(),
                                'reminder_count' => ($paiement->meta_donnees['reminder_count'] ?? 0) + 1
                            ])
                        ]);
                        
                        $count++;
                        
                        $this->info("Rappel envoyé pour le paiement #{$paiement->reference} ({$daysWaiting} jours)");
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi du rappel pour le paiement #{$paiement->reference}: " . $e->getMessage());
                        Log::error("Erreur lors de l'envoi du rappel de paiement", [
                            'paiement_id' => $paiement->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
        
        $this->info("Nombre de rappels envoyés: {$count}");
        
        return 0;
    }
}
