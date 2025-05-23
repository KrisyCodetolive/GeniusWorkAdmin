<?php

namespace App\Services\Presence;

use App\Models\User;
use App\Models\Employeur;
use App\Models\Presence;
use App\Models\PlageHoraire;
use App\Models\Notification;
use App\Models\Site;
use App\Models\Entreprise;
use App\Models\Departement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class RetardAbsenceService
{
    /**
     * Service de pointage.
     *
     * @var WebPointageService
     */
    protected $webPointageService;

    /**
     * Service de notification.
     *
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * Service de configuration des présences.
     *
     * @var ConfigurationPresenceService
     */
    protected $configurationPresenceService;

    /**
     * Crée une nouvelle instance du service.
     *
     * @param WebPointageService $webPointageService
     * @param NotificationService $notificationService
     * @param ConfigurationPresenceService $configurationPresenceService
     * @return void
     */
    public function __construct(
        WebPointageService $webPointageService,
        NotificationService $notificationService,
        ConfigurationPresenceService $configurationPresenceService
    ) {
        $this->webPointageService = $webPointageService;
        $this->notificationService = $notificationService;
        $this->configurationPresenceService = $configurationPresenceService;
    }

    /**
     * Vérifie et traite les retards pour tous les employeurs actifs.
     *
     * @param Carbon|null $date Date à vérifier (aujourd'hui par défaut)
     * @return array Statistiques des retards traités
     */
    public function verifierRetards(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $stats = [
            'total_employeurs' => 0,
            'retards_detectes' => 0,
            'notifications_envoyees' => 0,
            'erreurs' => 0
        ];

        try {
            // Récupérer tous les employeurs actifs
            $employeurs = Employeur::where('statut', 'actif')->get();
            $stats['total_employeurs'] = $employeurs->count();

            foreach ($employeurs as $employeur) {
                try {
                    // Vérifier si les notifications de retard sont activées pour cet employeur
                    if (!$this->configurationPresenceService->notificationsActives($employeur->entreprise_id, 'retard')) {
                        Log::channel('presences')->info("Notifications de retard désactivées pour l'employeur {$employeur->id} ({$employeur->nom_complet})");
                        continue;
                    }

                    // Vérifier si l'employeur doit travailler aujourd'hui
                    if (!$this->doitTravaillerAujourdhui($employeur, $date)) {
                        Log::channel('presences')->debug("L'employeur {$employeur->id} ({$employeur->nom_complet}) ne travaille pas le {$date->format('d/m/Y')}");
                        continue;
                    }

                    // Vérifier si l'employeur est en congé
                    if ($this->estEnConge($employeur, $date)) {
                        Log::channel('presences')->debug("L'employeur {$employeur->id} ({$employeur->nom_complet}) est en congé le {$date->format('d/m/Y')}");
                        continue;
                    }

                    // Récupérer les pointages de l'employeur pour aujourd'hui - utiliser la même logique que WebPointageService
                    $pointages = Presence::where('employeur_id', $employeur->id)
                        ->where(function($query) use ($date) {
                            $query->whereDate('date_heure', $date)
                                  ->orWhereDate('date_heure_entree', $date);
                        })
                        ->orderBy('date_heure', 'asc')
                        ->get();

                    // Vérifier s'il y a un pointage d'entrée avec retard
                    $pointageRetard = $pointages->first(function ($pointage) {
                        return ($pointage->type === 'entree' && $pointage->retard) || 
                               ($pointage->date_heure_entree && $pointage->retard);
                    });

                    if ($pointageRetard) {
                        $stats['retards_detectes']++;

                        // Vérifier si une notification a déjà été envoyée pour ce retard
                        $notificationExistante = Notification::where('user_id', $employeur->id)
                            ->where('type', 'retard')
                            ->whereDate('created_at', $date)
                            ->exists();

                        if (!$notificationExistante) {
                            // Récupérer la plage horaire pour déterminer l'heure prévue
                            $plageHoraire = $this->webPointageService->getPlageHoraireForEmployeur($employeur, $date);
                            $heurePrevue = $plageHoraire ? Carbon::parse($date->format('Y-m-d') . ' ' . $plageHoraire->heure_debut) : null;
                            $heurePointage = $pointageRetard->date_heure_entree ?? $pointageRetard->date_heure;

                            // Envoyer une notification de retard
                            $minutesRetard = $pointageRetard->minutes_retard ?? 0;
                            $success = $this->notificationService->notifierRetard($employeur, $minutesRetard, [
                                'heure_prevue' => $heurePrevue ? $heurePrevue->format('H:i') : 'Non définie',
                                'heure_pointage' => $heurePointage->format('H:i'),
                                'minutes_retard' => $minutesRetard,
                                'date' => $date->format('d/m/Y')
                            ]);

                            if ($success) {
                                $stats['notifications_envoyees']++;
                                Log::channel('presences')->info("Notification de retard envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet}) pour un retard de {$minutesRetard} minutes le {$date->format('d/m/Y')}");
                            } else {
                                Log::channel('presences')->warning("Échec de l'envoi de notification de retard à l'employeur {$employeur->id} ({$employeur->nom_complet})");
                            }
                        } else {
                            Log::channel('presences')->debug("Une notification de retard a déjà été envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet}) aujourd'hui");
                        }
                    }
                } catch (\Exception $e) {
                    $stats['erreurs']++;
                    Log::channel('presences')->error("Erreur lors de la vérification des retards pour l'employeur {$employeur->id}: " . $e->getMessage(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } catch (\Exception $e) {
            $stats['erreurs']++;
            Log::channel('presences')->error("Erreur générale lors de la vérification des retards: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $stats;
    }

    /**
     * Vérifie et traite les absences pour tous les employeurs actifs.
     *
     * @param Carbon|null $date Date à vérifier (aujourd'hui par défaut)
     * @return array Statistiques des absences traitées
     */
    public function verifierAbsences(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $stats = [
            'total_employeurs' => 0,
            'absences_detectees' => 0,
            'notifications_envoyees' => 0,
            'erreurs' => 0
        ];

        try {
            // Récupérer tous les employeurs actifs
            $employeurs = Employeur::where('statut', 'actif')->get();
            $stats['total_employeurs'] = $employeurs->count();

            foreach ($employeurs as $employeur) {
                try {
                    // Vérifier si les notifications d'absence sont activées pour cet employeur
                    if (!$this->configurationPresenceService->notificationsActives($employeur->entreprise_id, 'absence')) {
                        Log::channel('presences')->info("Notifications d'absence désactivées pour l'employeur {$employeur->id} ({$employeur->nom_complet})");
                        continue;
                    }

                    // Vérifier si l'employeur doit travailler aujourd'hui
                    if (!$this->doitTravaillerAujourdhui($employeur, $date)) {
                        Log::channel('presences')->debug("L'employeur {$employeur->id} ({$employeur->nom_complet}) ne travaille pas le {$date->format('d/m/Y')}");
                        continue;
                    }

                    // Vérifier si l'employeur est en congé
                    if ($this->estEnConge($employeur, $date)) {
                        Log::channel('presences')->debug("L'employeur {$employeur->id} ({$employeur->nom_complet}) est en congé le {$date->format('d/m/Y')}");
                        continue;
                    }

                    // Vérifier si l'employeur a pointé aujourd'hui - utiliser la même logique que WebPointageService
                    $aPointe = Presence::where('employeur_id', $employeur->id)
                        ->where(function($query) use ($date) {
                            $query->whereDate('date_heure', $date)
                                  ->orWhereDate('date_heure_entree', $date);
                        })
                        ->exists();

                    if (!$aPointe) {
                        $stats['absences_detectees']++;

                        // Vérifier si une notification a déjà été envoyée pour cette absence
                        $notificationExistante = Notification::where('user_id', $employeur->id)
                            ->where('type', 'absence')
                            ->whereDate('created_at', $date)
                            ->exists();

                        if (!$notificationExistante) {
                            // Vérifier si l'heure actuelle dépasse l'heure limite pour considérer une absence
                            $heureLimite = $this->getHeureLimiteAbsence($employeur, $date);
                            
                            if (Carbon::now()->gt($heureLimite)) {
                                // Envoyer une notification d'absence
                                $success = $this->notificationService->notifierAbsence($employeur, [
                                    'date' => $date->format('d/m/Y'),
                                    'heure_limite' => $heureLimite->format('H:i')
                                ]);

                                if ($success) {
                                    $stats['notifications_envoyees']++;
                                    Log::channel('presences')->info("Notification d'absence envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet}) pour le {$date->format('d/m/Y')}");
                                } else {
                                    Log::channel('presences')->warning("Échec de l'envoi de notification d'absence à l'employeur {$employeur->id} ({$employeur->nom_complet})");
                                }
                            } else {
                                Log::channel('presences')->debug("L'heure actuelle n'a pas encore dépassé l'heure limite ({$heureLimite->format('H:i')}) pour considérer l'absence de l'employeur {$employeur->id}");
                            }
                        } else {
                            Log::channel('presences')->debug("Une notification d'absence a déjà été envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet}) aujourd'hui");
                        }
                    }
                } catch (\Exception $e) {
                    $stats['erreurs']++;
                    Log::channel('presences')->error("Erreur lors de la vérification des absences pour l'employeur {$employeur->id}: " . $e->getMessage(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } catch (\Exception $e) {
            $stats['erreurs']++;
            Log::channel('presences')->error("Erreur générale lors de la vérification des absences: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $stats;
    }

    /**
     * Vérifie et traite les sorties manquantes pour tous les employeurs actifs.
     *
     * @param Carbon|null $date Date à vérifier (aujourd'hui par défaut)
     * @return array Statistiques des sorties manquantes traitées
     */
    public function verifierSortiesManquantes(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $stats = [
            'total_employeurs' => 0,
            'sorties_manquantes' => 0,
            'notifications_envoyees' => 0,
            'erreurs' => 0
        ];

        try {
            // Récupérer tous les employeurs actifs
            $employeurs = Employeur::where('statut', 'actif')->get();
            $stats['total_employeurs'] = $employeurs->count();

            foreach ($employeurs as $employeur) {
                try {
                    // Vérifier si les notifications sont activées pour cet employeur
                    if (!$this->configurationPresenceService->notificationsActives($employeur->entreprise_id, 'sortie_manquante')) {
                        Log::channel('presences')->info("Notifications de sortie manquante désactivées pour l'employeur {$employeur->id} ({$employeur->nom_complet})");
                        continue;
                    }

                    // Vérifier si l'employeur doit travailler aujourd'hui
                    if (!$this->doitTravaillerAujourdhui($employeur, $date)) {
                        Log::channel('presences')->debug("L'employeur {$employeur->id} ({$employeur->nom_complet}) ne travaille pas le {$date->format('d/m/Y')}");
                        continue;
                    }

                    // Vérifier si l'employeur est en congé
                    if ($this->estEnConge($employeur, $date)) {
                        Log::channel('presences')->debug("L'employeur {$employeur->id} ({$employeur->nom_complet}) est en congé le {$date->format('d/m/Y')}");
                        continue;
                    }

                    // Vérifier si l'employeur a pointé en entrée aujourd'hui mais pas en sortie
                    $presences = Presence::where('employeur_id', $employeur->id)
                        ->where(function($query) use ($date) {
                            $query->whereDate('date_heure', $date)
                                  ->orWhereDate('date_heure_entree', $date);
                        })
                        ->orderBy('date_heure', 'asc')
                        ->get();

                    if ($presences->isEmpty()) {
                        continue;
                    }

                    $dernierePresence = $presences->last();
                    
                    // Si le dernier pointage est une entrée ou une fin de pause, il manque un pointage de sortie
                    // Utiliser la même logique que dans WebPointageService
                    if (in_array($dernierePresence->type, ['entree', 'pause_fin']) || 
                        ($dernierePresence->date_heure_entree && !$dernierePresence->date_heure_sortie)) {
                        $stats['sorties_manquantes']++;

                        // Vérifier si l'heure actuelle dépasse l'heure de fin de travail + marge
                        $heureFin = $this->getHeureFinTravail($employeur, $date);
                        $margeMinutes = 60; // 1 heure de marge après l'heure de fin
                        $heureLimite = (clone $heureFin)->addMinutes($margeMinutes);
                        
                        if (Carbon::now()->gt($heureLimite)) {
                            // Vérifier si une notification a déjà été envoyée pour cette sortie manquante
                            $notificationExistante = Notification::where('user_id', $employeur->id)
                                ->where('type', 'sortie_manquante')
                                ->whereDate('created_at', $date)
                                ->exists();

                            if (!$notificationExistante) {
                                // Récupérer l'heure d'entrée
                                $heureEntree = null;
                                $premierePresence = $presences->first(function($presence) {
                                    return $presence->type === 'entree' || $presence->date_heure_entree;
                                });
                                
                                if ($premierePresence) {
                                    $heureEntree = $premierePresence->date_heure_entree ?? $premierePresence->date_heure;
                                }

                                // Envoyer une notification de sortie manquante
                                $data = [
                                    'date' => $date->format('d/m/Y'),
                                    'heure_entree' => $heureEntree ? $heureEntree->format('H:i') : 'Inconnue',
                                    'heure_fin_prevue' => $heureFin->format('H:i'),
                                    'heure_limite' => $heureLimite->format('H:i')
                                ];
                                
                                // Utiliser la méthode générique pour envoyer une notification
                                $success = $this->notificationService->envoyerNotification($employeur, 'sortie_manquante', $data);

                                if ($success) {
                                    $stats['notifications_envoyees']++;
                                    Log::channel('presences')->info("Notification de sortie manquante envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet}) pour le {$date->format('d/m/Y')}");
                                } else {
                                    Log::channel('presences')->warning("Échec de l'envoi de notification de sortie manquante à l'employeur {$employeur->id} ({$employeur->nom_complet})");
                                }
                            } else {
                                Log::channel('presences')->debug("Une notification de sortie manquante a déjà été envoyée à l'employeur {$employeur->id} ({$employeur->nom_complet}) aujourd'hui");
                            }
                        } else {
                            Log::channel('presences')->debug("L'heure actuelle n'a pas encore dépassé l'heure limite ({$heureLimite->format('H:i')}) pour considérer une sortie manquante pour l'employeur {$employeur->id}");
                        }
                    }
                } catch (\Exception $e) {
                    $stats['erreurs']++;
                    Log::channel('presences')->error("Erreur lors de la vérification des sorties manquantes pour l'employeur {$employeur->id}: " . $e->getMessage(), [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } catch (\Exception $e) {
            $stats['erreurs']++;
            Log::channel('presences')->error("Erreur générale lors de la vérification des sorties manquantes: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $stats;
    }

    /**
     * Vérifie si un employeur doit travailler à une date donnée.
     *
     * @param Employeur $employeur L'employeur à vérifier
     * @param Carbon $date La date à vérifier
     * @return bool
     */
    protected function doitTravaillerAujourdhui(Employeur $employeur, Carbon $date): bool
    {
        try {
            // Dans la version 2.0, utiliser directement la méthode du WebPointageService 
            // pour récupérer la plage horaire de l'employeur pour ce jour
            $plageHoraire = $this->webPointageService->getPlageHoraireForEmployeur($employeur, $date);
            
            // Si une plage horaire est définie, l'employeur doit travailler
            return $plageHoraire !== null;
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de la vérification si l'employeur {$employeur->id} doit travailler: " . $e->getMessage());
            return false; // Par défaut, on considère que l'employeur ne travaille pas en cas d'erreur
        }
    }
    
    /**
     * Vérifie si un employeur est en congé à une date donnée.
     *
     * @param Employeur $employeur L'employeur à vérifier
     * @param Carbon $date La date à vérifier
     * @return bool
     */
    protected function estEnConge(Employeur $employeur, Carbon $date): bool
    {
        try {
            // Vérifier si l'employeur a un congé approuvé pour cette date
            return $employeur->conges()
                ->where('statut', 'approuve')
                ->where(function($query) use ($date) {
                    $query->whereDate('date_debut', '<=', $date)
                          ->whereDate('date_fin', '>=', $date);
                })
                ->exists();
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de la vérification si l'employeur {$employeur->id} est en congé: " . $e->getMessage());
            return false; // Par défaut, on considère que l'employeur n'est pas en congé en cas d'erreur
        }
    }

    /**
     * Obtient l'heure limite pour considérer une absence.
     *
     * @param Employeur $employeur L'employeur
     * @param Carbon $date La date
     * @return Carbon L'heure limite
     */
    protected function getHeureLimiteAbsence(Employeur $employeur, Carbon $date): Carbon
    {
        try {
            // Utiliser le service WebPointage pour récupérer la plage horaire
            $plageHoraire = $this->webPointageService->getPlageHoraireForEmployeur($employeur, $date);
            
            if (!$plageHoraire) {
                Log::channel('presences')->info("Aucune plage horaire définie pour l'employeur {$employeur->id} le {$date->format('d/m/Y')}");
                // Par défaut, utiliser 10h du matin si aucune plage horaire n'est définie
                return Carbon::parse($date->format('Y-m-d') . ' 10:00:00');
            }
            
            // Ajouter une marge de tolérance (par exemple, 30 minutes après l'heure de début)
            $heureDebut = Carbon::parse($date->format('Y-m-d') . ' ' . $plageHoraire->heure_debut);
            $toleranceMinutes = 30;
            
            return $heureDebut->addMinutes($toleranceMinutes);
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de la récupération de l'heure limite d'absence pour l'employeur {$employeur->id}: " . $e->getMessage());
            // Par défaut, utiliser 10h du matin en cas d'erreur
            return Carbon::parse($date->format('Y-m-d') . ' 10:00:00');
        }
    }

    /**
     * Obtient l'heure de fin de travail pour un employeur à une date donnée.
     *
     * @param Employeur $employeur L'employeur
     * @param Carbon $date La date
     * @return Carbon L'heure de fin de travail
     */
    protected function getHeureFinTravail(Employeur $employeur, Carbon $date): Carbon
    {
        try {
            // Utiliser le service WebPointage pour récupérer la plage horaire
            $plageHoraire = $this->webPointageService->getPlageHoraireForEmployeur($employeur, $date);
            
            if (!$plageHoraire) {
                Log::channel('presences')->info("Aucune plage horaire définie pour l'employeur {$employeur->id} le {$date->format('d/m/Y')}");
                // Par défaut, utiliser 18h si aucune plage horaire n'est définie
                return Carbon::parse($date->format('Y-m-d') . ' 18:00:00');
            }
            
            return Carbon::parse($date->format('Y-m-d') . ' ' . $plageHoraire->heure_fin);
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de la récupération de l'heure de fin de travail pour l'employeur {$employeur->id}: " . $e->getMessage());
            // Par défaut, utiliser 18h en cas d'erreur
            return Carbon::parse($date->format('Y-m-d') . ' 18:00:00');
        }
    }

    /**
     * Exécute toutes les vérifications (retards, absences, sorties manquantes).
     *
     * @param Carbon|null $date Date à vérifier (aujourd'hui par défaut)
     * @return array Statistiques combinées
     */
    public function executerToutesVerifications(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        
        try {
            Log::channel('presences')->info("Début de l'exécution de toutes les vérifications pour le {$date->format('d/m/Y')}");
            
            $statsRetards = $this->verifierRetards($date);
            $statsAbsences = $this->verifierAbsences($date);
            $statsSorties = $this->verifierSortiesManquantes($date);
            
            $totalNotifications = $statsRetards['notifications_envoyees'] + 
                                 $statsAbsences['notifications_envoyees'] + 
                                 $statsSorties['notifications_envoyees'];
            
            $totalErreurs = $statsRetards['erreurs'] + 
                           $statsAbsences['erreurs'] + 
                           $statsSorties['erreurs'];
            
            Log::channel('presences')->info("Fin de l'exécution de toutes les vérifications pour le {$date->format('d/m/Y')}. " . 
                     "Total notifications: {$totalNotifications}, Total erreurs: {$totalErreurs}");
            
            return [
                'date' => $date->format('Y-m-d'),
                'retards' => $statsRetards,
                'absences' => $statsAbsences,
                'sorties_manquantes' => $statsSorties,
                'total_notifications' => $totalNotifications,
                'total_erreurs' => $totalErreurs
            ];
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de l'exécution de toutes les vérifications: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'date' => $date->format('Y-m-d'),
                'erreur' => $e->getMessage(),
                'total_notifications' => 0,
                'total_erreurs' => 1
            ];
        }
    }
}
