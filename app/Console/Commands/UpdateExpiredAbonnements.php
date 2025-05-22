<?php

namespace App\Console\Commands;

use App\Models\Abonnement;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateExpiredAbonnements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abonnements:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mettre à jour le statut des abonnements ayant dépassé leur date de fin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la mise à jour des abonnements expirés...');
        
        // Récupérer les abonnements actifs qui ont dépassé leur date de fin
        $expiredAbonnements = Abonnement::where('date_fin', '<', now())
            ->get();
            
        $count = 0;
        
        foreach ($expiredAbonnements as $abonnement) {
            // Mettre à jour le statut de l'abonnement
            $abonnement->update([
                'statut' => 'expire',
                'date_expiration' => now()
            ]);
            
            // Journaliser l'expiration
            Log::info("Abonnement expiré", [
                'abonnement_id' => $abonnement->id,
                'entreprise_id' => $abonnement->entreprise_id,
                'date_fin' => $abonnement->date_fin,
                'date_expiration' => now()
            ]);
            
            $this->info("Abonnement {$abonnement->id} marqué comme expiré.");
            $count++;
        }
        
        // Vérifier les abonnements en période de grâce qui ont dépassé cette période
        $gracePeriodDays = config('app.abonnement_grace_period', 7); // Période de grâce par défaut: 15 jours
        
        $graceExpiredAbonnements = Abonnement::where('statut', 'grace')
            ->where('date_fin', '<', now()->subDays($gracePeriodDays))
            ->get();
            
        foreach ($graceExpiredAbonnements as $abonnement) {
            // Mettre à jour le statut de l'abonnement
            $abonnement->update([
                'statut' => 'expire',
                'date_expiration' => now()
            ]);
            
            // Journaliser la fin de la période de grâce
            Log::info("Fin de période de grâce pour l'abonnement", [
                'abonnement_id' => $abonnement->id,
                'entreprise_id' => $abonnement->entreprise_id,
                'date_fin' => $abonnement->date_fin,
                'date_expiration' => now()
            ]);
            
            $this->info("Abonnement {$abonnement->id} en période de grâce marqué comme expiré.");
            $count++;
        }
        
        $this->info("Mise à jour terminée. {$count} abonnements ont été marqués comme expirés.");
    }
}
