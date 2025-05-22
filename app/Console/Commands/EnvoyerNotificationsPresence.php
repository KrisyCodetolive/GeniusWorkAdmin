<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Presence\NotificationService;
use App\Services\Presence\ConfigurationPresenceService;
use App\Models\Entreprise;
use App\Models\Presence;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnvoyerNotificationsPresence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:presence {type? : Type de notification (retard, absence, sortie_manquante)} {--entreprise= : ID de l\'entreprise spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie les notifications de présence (retards, absences, sorties manquantes)';

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var ConfigurationPresenceService
     */
    protected $configPresenceService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService, ConfigurationPresenceService $configPresenceService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
        $this->configPresenceService = $configPresenceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $entrepriseId = $this->option('entreprise');

        $this->info('Démarrage de l\'envoi des notifications de présence...');

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

            foreach ($entreprises as $entreprise) {
                $this->info("Traitement des notifications pour l'entreprise: {$entreprise->nom}");
                
                // Récupérer la configuration de présence pour cette entreprise
                $config = $this->configPresenceService->getConfigurationForEntreprise($entreprise->id);
                
                // Vérifier si les notifications sont activées pour cette entreprise
                if (!$config || !$config->notifications_actives) {
                    $this->info("Notifications désactivées pour l'entreprise {$entreprise->nom}.");
                    continue;
                }

                // Traiter les différents types de notifications selon le paramètre
                if (!$type || $type === 'retard') {
                    $this->traiterNotificationsRetard($entreprise, $config);
                }
                
                if (!$type || $type === 'absence') {
                    $this->traiterNotificationsAbsence($entreprise, $config);
                }
                
                if (!$type || $type === 'sortie_manquante') {
                    $this->traiterNotificationsSortieManquante($entreprise, $config);
                }
            }

            $this->info('Envoi des notifications de présence terminé avec succès.');
            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi des notifications: " . $e->getMessage());
            Log::error("Erreur dans EnvoyerNotificationsPresence: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Traite les notifications de retard pour une entreprise
     */
    protected function traiterNotificationsRetard(Entreprise $entreprise, $config)
    {
        $this->info("Traitement des notifications de retard pour {$entreprise->nom}");
        
        // Vérifier si les notifications de retard sont activées
        if (!$config->notifier_retards) {
            $this->info("Notifications de retard désactivées pour {$entreprise->nom}");
            return;
        }

        // Récupérer la date du jour
        $today = Carbon::today();
        
        // Récupérer les employeurs actifs de l'entreprise
        $employeurs = Employeur::where('entreprise_id', $entreprise->id)
                    ->where('statut', 'actif')
                    ->get();
        
        foreach ($employeurs as $employeur) {
            // Vérifier si l'employeur a une plage horaire définie pour aujourd'hui
            $plagesHoraires = $employeur->plagesHoraires()
                ->whereDate('date_debut', $today)
                ->where('est_pause', false)
                ->get();
            
            if ($plagesHoraires->isEmpty()) {
                continue; // Pas de plage horaire définie pour aujourd'hui
            }
            
            foreach ($plagesHoraires as $plage) {
                // Vérifier si l'employeur est en retard
                $heureDebut = Carbon::parse($plage->heure_debut);
                $tolerance = $config->tolerance_retard_minutes ?? 0;
                $limiteRetard = $heureDebut->copy()->addMinutes($tolerance);
                
                // Récupérer la présence correspondant à cette plage horaire
                $presence = Presence::where('employeur_id', $employeur->id)
                    ->whereDate('date_heure', $today)
                    ->where('plage_horaire_id', $plage->id)
                    ->first();
                
                if (!$presence) {
                    // Pas encore de pointage, on vérifie si l'heure de début est dépassée
                    if (now()->gt($limiteRetard)) {
                        $this->envoyerNotificationRetard($employeur, $entreprise, $plage, $config);
                    }
                } elseif ($presence->date_heure_entree && Carbon::parse($presence->date_heure_entree)->gt($limiteRetard)) {
                    // Pointage en retard
                    $this->envoyerNotificationRetard($employeur, $entreprise, $plage, $config, $presence);
                }
            }
        }
    }

    /**
     * Traite les notifications d'absence pour une entreprise
     */
    protected function traiterNotificationsAbsence(Entreprise $entreprise, $config)
    {
        $this->info("Traitement des notifications d'absence pour {$entreprise->nom}");
        
        // Vérifier si les notifications d'absence sont activées
        if (!$config->notifier_absences) {
            $this->info("Notifications d'absence désactivées pour {$entreprise->nom}");
            return;
        }

        // Récupérer la date du jour
        $today = Carbon::today();
        
        // Récupérer les employeurs actifs de l'entreprise
        $employeurs = Employeur::where('entreprise_id', $entreprise->id)
                    ->where('statut', 'actif')
                    ->get();
        
        foreach ($employeurs as $employeur) {
            // Vérifier si l'employeur a une plage horaire définie pour aujourd'hui
            $plagesHoraires = $employeur->plagesHoraires()
                ->whereDate('date_debut', $today)
                ->where('est_pause', false)
                ->get();
            
            if ($plagesHoraires->isEmpty()) {
                continue; // Pas de plage horaire définie pour aujourd'hui
            }
            
            // Vérifier si l'employeur est absent
            $presences = Presence::where('employeur_id', $employeur->id)
                ->whereDate('date_heure', $today)
                ->get();
            
            if ($presences->isEmpty()) {
                // Aucune présence enregistrée pour aujourd'hui
                // Vérifier si l'heure actuelle dépasse l'heure limite pour considérer une absence
                $plage = $plagesHoraires->first();
                $heureDebut = Carbon::parse($plage->heure_debut);
                $tolerance = $config->tolerance_absence_minutes ?? 120; // 2 heures par défaut
                $limiteAbsence = $heureDebut->copy()->addMinutes($tolerance);
                
                if (now()->gt($limiteAbsence)) {
                    $this->envoyerNotificationAbsence($employeur, $entreprise, $plage, $config);
                }
            }
        }
    }

    /**
     * Traite les notifications de sortie manquante pour une entreprise
     */
    protected function traiterNotificationsSortieManquante(Entreprise $entreprise, $config)
    {
        $this->info("Traitement des notifications de sortie manquante pour {$entreprise->nom}");
        
        // Vérifier si les notifications de sortie manquante sont activées
        if (!$config->notifier_sorties_manquantes) {
            $this->info("Notifications de sortie manquante désactivées pour {$entreprise->nom}");
            return;
        }

        // Récupérer la date du jour
        $today = Carbon::today();
        
        // Récupérer les employeurs actifs de l'entreprise
        $employeurs = Employeur::where('entreprise_id', $entreprise->id)
                    ->where('statut', 'actif')
                    ->get();
        
        foreach ($employeurs as $employeur) {
            // Vérifier si l'employeur a une plage horaire définie pour aujourd'hui
            $plagesHoraires = $employeur->plagesHoraires()
                ->whereDate('date_debut', $today)
                ->where('est_pause', false)
                ->get();
            
            if ($plagesHoraires->isEmpty()) {
                continue; // Pas de plage horaire définie pour aujourd'hui
            }
            
            // Vérifier les présences de l'employeur pour aujourd'hui
            $presences = Presence::where('employeur_id', $employeur->id)
                ->whereDate('date_heure', $today)
                ->orderBy('date_heure', 'asc')
                ->get();
            
            if ($presences->isEmpty()) {
                continue; // Pas de présence enregistrée pour aujourd'hui
            }
            
            $dernierePresence = $presences->last();
            
            // Si le dernier pointage est une entrée ou une fin de pause, il manque un pointage de sortie
            if (in_array($dernierePresence->type, ['entree', 'pause_fin']) || 
                ($dernierePresence->date_heure_entree && !$dernierePresence->date_heure_sortie)) {
                
                // Vérifier si l'heure actuelle dépasse l'heure de fin de travail + marge
                $plage = $plagesHoraires->last();
                $heureFin = Carbon::parse($plage->heure_fin);
                $tolerance = $config->tolerance_sortie_minutes ?? 60; // 1 heure par défaut
                $limiteSortie = $heureFin->copy()->addMinutes($tolerance);
                
                if (now()->gt($limiteSortie)) {
                    $this->envoyerNotificationSortieManquante($employeur, $entreprise, $plage, $config, $dernierePresence);
                }
            }
        }
    }

    /**
     * Envoie une notification de retard
     */
    protected function envoyerNotificationRetard(Employeur $employeur, Entreprise $entreprise, $plageHoraire, $config, $presence = null)
    {
        try {
            $data = [
                'date' => Carbon::today()->format('d/m/Y'),
                'heure_prevue' => Carbon::parse($plageHoraire->heure_debut)->format('H:i'),
                'heure_arrivee' => $presence ? Carbon::parse($presence->date_heure_entree)->format('H:i') : 'Non pointé'
            ];
            
            $success = $this->notificationService->notifierRetard($employeur, $data);
            
            if ($success) {
                $this->info("Notification de retard envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet})");
            } else {
                $this->warn("Échec de l'envoi de notification de retard à l'employeur {$employeur->id}");
            }
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de la notification de retard: " . $e->getMessage());
            Log::error("Erreur d'envoi de notification de retard: " . $e->getMessage());
        }
    }

    /**
     * Envoie une notification d'absence
     */
    protected function envoyerNotificationAbsence(Employeur $employeur, Entreprise $entreprise, $plageHoraire, $config)
    {
        try {
            $data = [
                'date' => Carbon::today()->format('d/m/Y'),
                'heure_prevue' => Carbon::parse($plageHoraire->heure_debut)->format('H:i')
            ];
            
            $success = $this->notificationService->notifierAbsence($employeur, $data);
            
            if ($success) {
                $this->info("Notification d'absence envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet})");
            } else {
                $this->warn("Échec de l'envoi de notification d'absence à l'employeur {$employeur->id}");
            }
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de la notification d'absence: " . $e->getMessage());
            Log::error("Erreur d'envoi de notification d'absence: " . $e->getMessage());
        }
    }

    /**
     * Envoie une notification de sortie manquante
     */
    protected function envoyerNotificationSortieManquante(Employeur $employeur, Entreprise $entreprise, $plageHoraire, $config, $presence)
    {
        try {
            $data = [
                'date' => Carbon::today()->format('d/m/Y'),
                'heure_entree' => $presence->date_heure_entree ? Carbon::parse($presence->date_heure_entree)->format('H:i') : Carbon::parse($presence->date_heure)->format('H:i'),
                'heure_fin_prevue' => Carbon::parse($plageHoraire->heure_fin)->format('H:i')
            ];
            
            $success = $this->notificationService->notifierSortieManquante($employeur, $entreprise, $plageHoraire, $presence);
            
            if ($success) {
                $this->info("Notification de sortie manquante envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet})");
            } else {
                $this->warn("Échec de l'envoi de notification de sortie manquante à l'employeur {$employeur->id}");
            }
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de la notification de sortie manquante: " . $e->getMessage());
            Log::error("Erreur d'envoi de notification de sortie manquante: " . $e->getMessage());
        }
    }
}
