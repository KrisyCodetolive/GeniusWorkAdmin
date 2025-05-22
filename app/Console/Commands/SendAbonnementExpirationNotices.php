<?php

namespace App\Console\Commands;

use App\Models\Abonnement;
use App\Models\User;
use App\Notifications\AbonnementExpirationNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAbonnementExpirationNotices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abonnements:send-expiration-notices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des notifications d\'expiration pour les abonnements';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Envoi des notifications d\'expiration d\'abonnement...');
        
        // Dates importantes
        $today = Carbon::today();
        $in7Days = $today->copy()->addDays(7);
        $in3Days = $today->copy()->addDays(3);
        $in1Day = $today->copy()->addDays(1);
        
        // Récupérer les abonnements actifs qui expirent bientôt
        $abonnementsExpirant = Abonnement::where('statut', 'actif')
            ->where('date_fin', '>=', $today)
            ->where('date_fin', '<=', $in7Days)
            ->get();
            
        $this->info("Nombre d'abonnements expirant bientôt trouvés: " . $abonnementsExpirant->count());
        
        $count = 0;
        
        foreach ($abonnementsExpirant as $abonnement) {
            // Déterminer si une notification doit être envoyée aujourd'hui
            $daysUntilExpiration = $today->diffInDays($abonnement->date_fin);
            $lastNotice = $abonnement->meta_donnees['last_expiration_notice'] ?? null;
            
            $shouldSendNotice = false;
            
            // Envoyer une notification 7 jours, 3 jours, et 1 jour avant l'expiration
            if ($daysUntilExpiration == 7 || $daysUntilExpiration == 3 || $daysUntilExpiration == 1) {
                // Vérifier si une notification a déjà été envoyée aujourd'hui
                $shouldSendNotice = $lastNotice === null || 
                    Carbon::parse($lastNotice)->format('Y-m-d') !== $today->format('Y-m-d');
            }
            
            if ($shouldSendNotice) {
                // Récupérer l'administrateur de l'entreprise
                $admin = User::find($abonnement->entreprise->admin_id);
                
                if ($admin) {
                    try {
                        // Envoyer la notification
                        $admin->notify(new AbonnementExpirationNotification($abonnement, $daysUntilExpiration));
                        
                        // Mettre à jour la date de la dernière notification
                        $abonnement->update([
                            'meta_donnees' => array_merge($abonnement->meta_donnees ?? [], [
                                'last_expiration_notice' => $today->toDateTimeString(),
                                'expiration_notice_count' => ($abonnement->meta_donnees['expiration_notice_count'] ?? 0) + 1
                            ])
                        ]);
                        
                        $count++;
                        
                        $this->info("Notification d'expiration envoyée pour l'abonnement #{$abonnement->id} ({$daysUntilExpiration} jours)");
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi de la notification d'expiration pour l'abonnement #{$abonnement->id}: " . $e->getMessage());
                        Log::error("Erreur lors de l'envoi de la notification d'expiration d'abonnement", [
                            'abonnement_id' => $abonnement->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
        
        // Récupérer les abonnements expirés récemment (moins de 30 jours)
        $abonnementsExpires = Abonnement::where('statut', 'actif')
            ->where('date_fin', '<', $today)
            ->where('date_fin', '>=', $today->copy()->subDays(30))
            ->get();
            
        $this->info("Nombre d'abonnements expirés récemment trouvés: " . $abonnementsExpires->count());
        
        foreach ($abonnementsExpires as $abonnement) {
            // Envoyer une notification le jour de l'expiration et 7 jours après
            $daysAfterExpiration = $abonnement->date_fin->diffInDays($today);
            $lastExpiredNotice = $abonnement->meta_donnees['last_expired_notice'] ?? null;
            
            $shouldSendExpiredNotice = false;
            
            // Envoyer une notification le jour même et 7 jours après l'expiration
            if ($daysAfterExpiration == 0 || $daysAfterExpiration == 7) {
                // Vérifier si une notification a déjà été envoyée aujourd'hui
                $shouldSendExpiredNotice = $lastExpiredNotice === null || 
                    Carbon::parse($lastExpiredNotice)->format('Y-m-d') !== $today->format('Y-m-d');
            }
            
            if ($shouldSendExpiredNotice) {
                // Récupérer l'administrateur de l'entreprise
                $admin = User::find($abonnement->entreprise->admin_id);
                
                if ($admin) {
                    try {
                        // Envoyer la notification (avec daysRemaining négatif pour indiquer que c'est déjà expiré)
                        $admin->notify(new AbonnementExpirationNotification($abonnement, -$daysAfterExpiration));
                        
                        // Mettre à jour la date de la dernière notification
                        $abonnement->update([
                            'meta_donnees' => array_merge($abonnement->meta_donnees ?? [], [
                                'last_expired_notice' => $today->toDateTimeString(),
                                'expired_notice_count' => ($abonnement->meta_donnees['expired_notice_count'] ?? 0) + 1
                            ])
                        ]);
                        
                        $count++;
                        
                        $this->info("Notification d'expiration envoyée pour l'abonnement expiré #{$abonnement->id} (il y a {$daysAfterExpiration} jours)");
                    } catch (\Exception $e) {
                        $this->error("Erreur lors de l'envoi de la notification d'expiration pour l'abonnement expiré #{$abonnement->id}: " . $e->getMessage());
                        Log::error("Erreur lors de l'envoi de la notification d'expiration d'abonnement", [
                            'abonnement_id' => $abonnement->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
        
        $this->info("Nombre total de notifications envoyées: {$count}");
        
        return 0;
    }
}
