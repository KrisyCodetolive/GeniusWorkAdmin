<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Presence;
use App\Models\Entreprise;
use App\Models\Politique;
use App\Models\Employeur;
use App\Services\Presence\ConfigurationPresenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VerifierPresencesSansSortie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:verifier-sans-sortie {--entreprise= : ID de l\'entreprise spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie et annule les présences sans sortie selon la politique de l\'entreprise';

    /**
     * @var ConfigurationPresenceService
     */
    protected $configPresenceService;

    /**
     * Create a new command instance.
     */
    public function __construct(ConfigurationPresenceService $configPresenceService)
    {
        parent::__construct();
        $this->configPresenceService = $configPresenceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $entrepriseId = $this->option('entreprise');

        $this->info('Démarrage de la vérification des présences sans sortie...');

        try {
            // Si un ID d'entreprise est spécifié, on ne traite que cette entreprise
            if ($entrepriseId) {
                $entreprises = Entreprise::where('id', $entrepriseId)->get();
                if ($entreprises->isEmpty()) {
                    $this->error("Entreprise avec ID {$entrepriseId} non trouvée.");
                    return 1;
                }
            } else {
                $entreprises = Entreprise::all();
            }

            $total = 0;

            foreach ($entreprises as $entreprise) {
                $this->info("Traitement des présences pour l'entreprise: {$entreprise->nom}");
                
                // Récupérer la politique de l'entreprise
                $politique = $this->configPresenceService->getPolitiqueForEntreprise($entreprise->id);
                
                // Récupérer également la configuration de présence
                $config = $this->configPresenceService->getConfigurationForEntreprise($entreprise->id);
                
                // Vérifier si l'annulation des horaires sans sortie est activée
                $annulerPresences = false;
                
                if ($politique && $politique->annuler_horaires_si_sortie_manquee) {
                    $annulerPresences = true;
                } elseif ($config && $config->annuler_presences_sans_sortie) {
                    $annulerPresences = true;
                }
                
                if (!$annulerPresences) {
                    $this->info("L'annulation des présences sans sortie n'est pas activée pour l'entreprise {$entreprise->nom}.");
                    continue;
                }

                // Récupérer la date d'hier
                $yesterday = Carbon::yesterday()->format('Y-m-d');
                
                // Récupérer les présences d'hier sans heure de sortie
                $presencesSansSortie = Presence::whereDate('date_heure', $yesterday)
                    ->whereNotNull('date_heure_entree')
                    ->whereNull('date_heure_sortie')
                    ->whereHas('employeur', function ($query) use ($entreprise) {
                        $query->where('entreprise_id', $entreprise->id);
                    })
                    ->get();
                
                $count = $presencesSansSortie->count();
                $this->info("Nombre de présences sans sortie trouvées: {$count}");
                
                foreach ($presencesSansSortie as $presence) {
                    // Annuler la présence
                    $presence->update([
                        'statut' => 'annule',
                        'commentaire' => $presence->commentaire . ' | Annulé automatiquement: sortie manquante',
                        'annule_par' => 'system',
                        'date_annulation' => now()
                    ]);
                    
                    $this->info("Présence ID {$presence->id} annulée pour l'employeur ID {$presence->employeur_id}");
                    
                    // Envoyer une notification si configuré
                    if (($politique && $politique->notifier_utilisateurs) || 
                        ($config && $config->notifier_sorties_manquantes)) {
                        $this->notifierEmployeur($presence, $entreprise);
                    }
                }
                
                $total += $count;
            }

            $this->info("Vérification terminée. Total des présences sans sortie annulées: {$total}");
            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de la vérification des présences: " . $e->getMessage());
            Log::error("Erreur dans VerifierPresencesSansSortie: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Envoie une notification à l'employeur concernant sa présence annulée
     */
    protected function notifierEmployeur($presence, $entreprise)
    {
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            
            // Récupérer la plage horaire associée
            $plageHoraire = $presence->plageHoraire;
            
            if ($plageHoraire) {
                $notificationService->notifierSortieManquante(
                    $presence->employeur,
                    $entreprise,
                    $plageHoraire,
                    $presence
                );
                
                $this->info("Notification envoyée à l'employeur ID {$presence->employeur_id}");
            }
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de la notification: " . $e->getMessage());
            Log::error("Erreur d'envoi de notification: " . $e->getMessage());
        }
    }
}
