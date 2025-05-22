<?php

namespace App\Services\Presence;

use App\Models\ConfigurationPresence;
use App\Models\Entreprise;
use App\Models\Presence;
use App\Models\User;
use App\Models\Politique;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ConfigurationPresenceService
{
    /**
     * Obtient la configuration de présence pour une entreprise.
     *
     * @param Entreprise|int $entreprise
     * @return ConfigurationPresence
     */
    public function getConfiguration($entreprise)
    {
        $entrepriseId = $entreprise instanceof Entreprise ? $entreprise->id : $entreprise;
        
        $configuration = ConfigurationPresence::where('entreprise_id', $entrepriseId)->first();
        
        if (!$configuration) {
            // Créer une configuration par défaut si elle n'existe pas
            $configuration = $this->creerConfigurationParDefaut($entrepriseId);
        }
        
        return $configuration;
    }
    
    /**
     * Crée une configuration de présence par défaut pour une entreprise.
     *
     * @param int $entrepriseId
     * @return ConfigurationPresence
     */
    public function creerConfigurationParDefaut($entrepriseId)
    {
        return ConfigurationPresence::create([
            'entreprise_id' => $entrepriseId,
            'heures_supplementaires_actives' => true,
            'nombre_pointages_par_jour' => 2,
            'pauses_actives' => true,
            'annuler_presence_sans_sortie' => true,
            'delai_annulation_heures' => 24,
            'notifications_actives' => true,
            'notification_absence' => true,
            'notification_retard' => true,
            'notification_conge' => true,
            'message_absence' => 'Nous avons remarqué votre absence aujourd\'hui. Veuillez contacter votre responsable.',
            'message_retard' => 'Nous avons remarqué votre retard aujourd\'hui. Veuillez respecter les horaires de travail.',
            'message_conge' => 'Votre demande de congé a été traitée. Veuillez consulter votre espace personnel pour plus d\'informations.',
        ]);
    }
    
    /**
     * Met à jour la configuration de présence pour une entreprise.
     *
     * @param Entreprise|int $entreprise
     * @param array $data
     * @return ConfigurationPresence
     */
    public function updateConfiguration($entreprise, array $data)
    {
        $configuration = $this->getConfiguration($entreprise);
        $configuration->update($data);
        
        return $configuration;
    }
    
    /**
     * Vérifie si les heures supplémentaires sont activées pour une entreprise.
     *
     * @param Entreprise|int $entreprise
     * @return bool
     */
    public function heuresSupplementairesActives($entreprise)
    {
        return $this->getConfiguration($entreprise)->heures_supplementaires_actives;
    }
    
    /**
     * Vérifie si les pauses sont activées pour une entreprise.
     *
     * @param Entreprise|int $entreprise
     * @return bool
     */
    public function pausesActives($entreprise)
    {
        return $this->getConfiguration($entreprise)->pauses_actives;
    }
    
    /**
     * Obtient le nombre de pointages par jour pour une entreprise.
     *
     * @param Entreprise|int $entreprise
     * @return int
     */
    public function getNombrePointagesParJour($entreprise)
    {
        return $this->getConfiguration($entreprise)->nombre_pointages_par_jour;
    }
    
    /**
     * Vérifie si l'annulation des présences sans pointage de sortie est activée.
     *
     * @param Entreprise|int $entreprise
     * @return bool
     */
    public function annulerPresenceSansSortie($entreprise)
    {
        return $this->getConfiguration($entreprise)->annuler_presence_sans_sortie;
    }
    
    /**
     * Obtient le délai d'annulation des présences sans pointage de sortie en heures.
     *
     * @param Entreprise|int $entreprise
     * @return int
     */
    public function getDelaiAnnulationHeures($entreprise)
    {
        return $this->getConfiguration($entreprise)->delai_annulation_heures;
    }
    
    /**
     * Vérifie si les notifications sont activées pour une entreprise.
     *
     * @param Entreprise|int $entreprise
     * @param string|null $type Type de notification (absence, retard, conge)
     * @return bool
     */
    public function notificationsActives($entreprise, $type = null)
    {
        $config = $this->getConfiguration($entreprise);
        
        if (!$config->notifications_actives) {
            return false;
        }
        
        if ($type) {
            $field = 'notification_' . $type;
            return $config->$field;
        }
        
        return true;
    }
    
    /**
     * Obtient le message personnalisé pour un type de notification.
     *
     * @param Entreprise|int $entreprise
     * @param string $type Type de notification (absence, retard, conge)
     * @return string|null
     */
    public function getMessageNotification($entreprise, $type)
    {
        $config = $this->getConfiguration($entreprise);
        $field = 'message_' . $type;
        
        return $config->$field;
    }
    
    /**
     * Vérifie et annule les présences sans pointage de sortie.
     *
     * @return int Nombre de présences annulées
     */
    public function verifierEtAnnulerPresencesSansSortie()
    {
        $presencesAnnulees = 0;
        $entreprises = Entreprise::where('actif', true)->get();
        
        foreach ($entreprises as $entreprise) {
            if (!$this->annulerPresenceSansSortie($entreprise)) {
                continue;
            }
            
            $delaiHeures = $this->getDelaiAnnulationHeures($entreprise);
            $dateLimite = Carbon::now()->subHours($delaiHeures);
            
            // Récupérer les présences sans heure de sortie
            $presencesSansSortie = Presence::where('entreprise_id', $entreprise->id)
                ->whereNull('heure_sortie')
                ->where('heure_entree', '<', $dateLimite)
                ->get();
            
            foreach ($presencesSansSortie as $presence) {
                // Annuler la présence
                $presence->update([
                    'statut' => 'annule',
                    'commentaire' => $presence->commentaire . ' (Annulé automatiquement après ' . $delaiHeures . ' heures sans pointage de sortie)',
                ]);
                
                $presencesAnnulees++;
                
                Log::info("Présence ID {$presence->id} annulée automatiquement pour l'utilisateur ID {$presence->user_id} après {$delaiHeures} heures sans pointage de sortie.");
            }
        }
        
        return $presencesAnnulees;
    }
    
    /**
     * Envoie une notification selon le type spécifié.
     *
     * @param User $user
     * @param string $type Type de notification (absence, retard, conge)
     * @param array $data Données supplémentaires pour la notification
     * @return bool
     */
    public function envoyerNotification(User $user, $type, array $data = [])
    {
        $entreprise = $user->entreprise;
        
        if (!$entreprise || !$this->notificationsActives($entreprise, $type)) {
            return false;
        }
        
        $message = $this->getMessageNotification($entreprise, $type);
        
        if (empty($message)) {
            return false;
        }
        
        // Remplacer les variables dans le message
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        // Envoyer la notification par email
        try {
            // Logique d'envoi d'email à implémenter
            // Mail::to($user->email)->send(new NotificationPresence($message, $type));
            
            Log::info("Email de notification '{$type}' envoyé à l'utilisateur ID {$user->id} : {$message}");
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi de l'email de notification '{$type}' à l'utilisateur ID {$user->id} : " . $e->getMessage());
            return false;
        }
        
        // Envoyer la notification par SMS si le numéro de téléphone est disponible
        if ($user->telephone) {
            try {
                // Logique d'envoi de SMS à implémenter
                // SmsService::send($user->telephone, $message);
                
                Log::info("SMS de notification '{$type}' envoyé à l'utilisateur ID {$user->id} : {$message}");
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi du SMS de notification '{$type}' à l'utilisateur ID {$user->id} : " . $e->getMessage());
                // Continuer même si l'envoi du SMS échoue
            }
        }
        
        return true;
    }

    /**
     * Vérifie si les notifications sont activées pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function notificationsActivees($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config && $config->notifications_actives;
    }

    /**
     * Vérifie si les notifications de retard sont activées pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function notificationsRetardActivees($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config && $config->notifications_actives && $config->notifier_retards;
    }

    /**
     * Vérifie si les notifications d'absence sont activées pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function notificationsAbsenceActivees($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config && $config->notifications_actives && $config->notifier_absences;
    }

    /**
     * Vérifie si les notifications de sortie manquante sont activées pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function notificationsSortieManquanteActivees($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config && $config->notifications_actives && $config->notifier_sorties_manquantes;
    }

    /**
     * Récupère la tolérance de retard en minutes pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return int
     */
    public function getToleranceRetard($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config ? ($config->tolerance_retard_minutes ?? 0) : 0;
    }

    /**
     * Récupère la tolérance d'absence en minutes pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return int
     */
    public function getToleranceAbsence($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config ? ($config->tolerance_absence_minutes ?? 0) : 0;
    }

    /**
     * Récupère la tolérance de sortie manquante en minutes pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return int
     */
    public function getToleranceSortieManquante($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config ? ($config->tolerance_sortie_minutes ?? 0) : 0;
    }

    /**
     * Vérifie si les notifications par email sont activées pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function notificationsEmailActivees($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config && $config->notifications_actives && $config->notifier_par_email;
    }

    /**
     * Vérifie si les notifications par SMS sont activées pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function notificationsSmsActivees($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config && $config->notifications_actives && $config->notifier_par_sms;
    }

    /**
     * Récupère le message de retard personnalisé pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return string|null
     */
    public function getMessageRetard($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config ? $config->message_retard : null;
    }

    /**
     * Récupère le message d'absence personnalisé pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return string|null
     */
    public function getMessageAbsence($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config ? $config->message_absence : null;
    }

    /**
     * Récupère le message de sortie manquante personnalisé pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return string|null
     */
    public function getMessageSortieManquante($entrepriseId)
    {
        $config = $this->getConfigurationForEntreprise($entrepriseId);
        return $config ? $config->message_sortie_manquante : null;
    }

    /**
     * Récupère la configuration de présence pour une entreprise
     * 
     * @param int $entrepriseId
     * @return ConfigurationPresence|null
     */
    public function getConfigurationForEntreprise($entrepriseId)
    {
        return ConfigurationPresence::where('entreprise_id', $entrepriseId)->first();
    }
    
    /**
     * Récupère la politique d'entreprise
     * 
     * @param int $entrepriseId
     * @return Politique|null
     */
    public function getPolitiqueForEntreprise($entrepriseId)
    {
        $politique = Politique::where('entreprise_id', $entrepriseId)->first();
        
        if (!$politique) {
            // Créer une politique par défaut si elle n'existe pas
            $politique = $this->creerPolitiqueParDefaut($entrepriseId);
        }
        
        return $politique;
    }
    
    /**
     * Crée une politique par défaut pour une entreprise
     * 
     * @param int $entrepriseId
     * @return Politique
     */
    public function creerPolitiqueParDefaut($entrepriseId)
    {
        return Politique::create([
            'entreprise_id' => $entrepriseId,
            'annuler_horaires_si_sortie_manquee' => true,
            'nombre_pointages_par_jour' => 2,
            'activer_pauses' => true,
            'activer_heures_supplementaires' => true,
            'notifier_utilisateurs' => true,
            'tolerance_retard' => 15, // 15 minutes
            'tolerance_depart_anticipe' => 0,
            'autoriser_permutations' => true,
            'autoriser_recuperations' => true,
            'autoriser_travail_weekend' => false,
            'configuration' => [
                'notifier_par_email' => true,
                'notifier_par_sms' => false,
                'message_retard' => 'Nous avons remarqué votre retard aujourd\'hui. Veuillez respecter les horaires de travail.',
                'message_absence' => 'Nous avons remarqué votre absence aujourd\'hui. Veuillez contacter votre responsable.',
                'message_sortie_manquante' => 'Nous avons remarqué que vous n\'avez pas enregistré votre sortie hier. Veuillez veiller à bien enregistrer vos heures de travail.'
            ]
        ]);
    }
}
