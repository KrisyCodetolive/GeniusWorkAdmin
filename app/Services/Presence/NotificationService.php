<?php

namespace App\Services\Presence;

use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Notification;
use App\Models\ConfigurationPresence;
use App\Services\SMS\OrangeSMSService;
use App\Services\SMS\SMSLogService;
use App\Services\Mail\SmtpEmailService;
use App\Mail\PresenceNotificationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * Service de configuration des présences.
     *
     * @var ConfigurationPresenceService
     */
    protected $configurationPresenceService;
    
    /**
     * Service d'envoi de SMS Orange.
     *
     * @var OrangeSMSService
     */
    protected $orangeSMSService;
    
    /**
     * Service de journalisation des SMS.
     *
     * @var SMSLogService
     */
    protected $smsLogService;
    
    /**
     * Service d'envoi d'emails SMTP.
     *
     * @var SmtpEmailService
     */
    protected $smtpEmailService;

    /**
     * Crée une nouvelle instance du service.
     *
     * @param ConfigurationPresenceService $configurationPresenceService
     * @param OrangeSMSService $orangeSMSService
     * @param SMSLogService $smsLogService
     * @param SmtpEmailService $smtpEmailService
     * @return void
     */
    public function __construct(
        ConfigurationPresenceService $configurationPresenceService,
        OrangeSMSService $orangeSMSService,
        SMSLogService $smsLogService,
        SmtpEmailService $smtpEmailService
    ) {
        $this->configurationPresenceService = $configurationPresenceService;
        $this->orangeSMSService = $orangeSMSService;
        $this->smsLogService = $smsLogService;
        $this->smtpEmailService = $smtpEmailService;
    }

    /**
     * Envoie une notification d'absence à un employeur.
     *
     * @param Employeur $employeur
     * @param array $data
     * @return bool
     */
    public function notifierAbsence(Employeur $employeur, array $data = [])
    {
        if (!$employeur->entreprise) {
            return false;
        }

        // Vérifier si les notifications d'absence sont activées
        if (!$this->configurationPresenceService->notificationsActives($employeur->entreprise_id, 'absence')) {
            return false;
        }

        // Préparer les données pour le message
        $messageData = array_merge([
            'nom' => $employeur->nom,
            'prenom' => $employeur->prenom,
            'date' => now()->format('d/m/Y'),
            'entreprise' => $employeur->entreprise->nom,
        ], $data);

        return $this->envoyerNotification($employeur, 'absence', $messageData);
    }

    /**
     * Envoie une notification de retard à un employeur.
     *
     * @param Employeur $employeur
     * @param array $data
     * @return bool
     */
    public function notifierRetard(Employeur $employeur, array $data = [])
    {
        if (!$employeur->entreprise) {
            return false;
        }

        // Vérifier si les notifications de retard sont activées
        if (!$this->configurationPresenceService->notificationsActives($employeur->entreprise_id, 'retard')) {
            return false;
        }

        // Préparer les données pour le message
        $messageData = array_merge([
            'nom' => $employeur->nom,
            'prenom' => $employeur->prenom,
            'date' => now()->format('d/m/Y'),
            'entreprise' => $employeur->entreprise->nom,
        ], $data);

        return $this->envoyerNotification($employeur, 'retard', $messageData);
    }

    /**
     * Envoie une notification de congé à un employeur.
     *
     * @param Employeur $employeur
     * @param array $congeData
     * @param array $data
     * @return bool
     */
    public function notifierConge(Employeur $employeur, array $congeData, array $data = [])
    {
        if (!$employeur->entreprise) {
            return false;
        }

        // Vérifier si les notifications de congé sont activées
        if (!$this->configurationPresenceService->notificationsActives($employeur->entreprise_id, 'conge')) {
            return false;
        }

        // Préparer les données pour le message
        $messageData = array_merge([
            'nom' => $employeur->nom,
            'prenom' => $employeur->prenom,
            'date_debut' => $congeData['date_debut'] ?? now()->format('d/m/Y'),
            'date_fin' => $congeData['date_fin'] ?? now()->format('d/m/Y'),
            'type_conge' => $congeData['type_conge'] ?? 'Congé',
            'statut' => $congeData['statut'] ?? 'En attente',
            'entreprise' => $employeur->entreprise->nom,
        ], $data);

        return $this->envoyerNotification($employeur, 'conge', $messageData);
    }

    /**
     * Envoie une notification personnalisée à un employeur.
     *
     * @param Employeur $employeur
     * @param string $type
     * @param array $data
     * @return bool
     */
    public function envoyerNotification(Employeur $employeur, string $type, array $data = [])
    {
        // Récupérer le message personnalisé
        $message = $this->getMessagePersonnalise($employeur->entreprise_id, $type, $data);

        // Enregistrer la notification dans la base de données
        $notification = $this->enregistrerNotification($employeur, $type, $message, $data);

        // Envoyer par email si l'adresse email est disponible
        $emailEnvoye = false;
        if ($employeur->email) {
            $emailEnvoye = $this->envoyerNotificationEmail($employeur, $type, $message, $data);
            $this->mettreAJourStatutNotification($notification, 'email', $emailEnvoye);
        }

        // Envoyer par SMS si le numéro de téléphone est disponible
        $smsEnvoye = false;
        if ($employeur->telephone) {
            $smsEnvoye = $this->envoyerSMSEmployeur($employeur, $message);
            $this->mettreAJourStatutNotification($notification, 'sms', $smsEnvoye);
        }

        return $emailEnvoye || $smsEnvoye;
    }

    /**
     * Enregistre une notification dans la base de données
     *
     * @param Employeur $employeur Employeur destinataire
     * @param string $type Type de notification
     * @param string $message Message de la notification
     * @param array $data Données supplémentaires
     * @return \App\Models\Notification
     */
    protected function enregistrerNotification(Employeur $employeur, string $type, string $message, array $data = [])
    {
        // Créer la notification
        $notification = new \App\Models\Notification([
            'employeur_id' => $employeur->id,
            'entreprise_id' => $employeur->entreprise_id,
            'message' => $message,
            'data' => json_encode($data),
            'lu' => false,
            'status' => 'created',
            'type' => $type, // Utiliser 'type' au lieu de 'type_notification'
            'canaux_envoyes' => json_encode([])
        ]);
        
        $notification->save();
        
        return $notification;
    }

    /**
     * Met à jour le statut d'une notification après envoi
     *
     * @param \App\Models\Notification $notification Notification à mettre à jour
     * @param string $canal Canal utilisé (email, sms, app)
     * @param bool $success Succès de l'envoi
     * @param string $erreur Message d'erreur en cas d'échec
     * @return \App\Models\Notification
     */
    protected function mettreAJourStatutNotification(\App\Models\Notification $notification, string $canal, bool $success, string $erreur = null)
    {
        // Récupérer les canaux déjà envoyés
        $canauxEnvoyes = json_decode($notification->canaux_envoyes, true) ?: [];
        
        if ($success) {
            // Ajouter le canal à la liste des canaux envoyés
            if (!in_array($canal, $canauxEnvoyes)) {
                $canauxEnvoyes[] = $canal;
            }
            
            // Mettre à jour le statut
            $notification->status = 'sent';
            $notification->date_envoi = now();
        } else {
            // Enregistrer l'erreur
            $notification->status = 'failed';
            $notification->erreur = $erreur;
        }
        
        // Mettre à jour les canaux envoyés
        $notification->canaux_envoyes = json_encode($canauxEnvoyes);
        $notification->save();
        
        return $notification;
    }

    /**
     * Envoie un email à l'employeur.
     *
     * @param Employeur $employeur
     * @param string $type
     * @param string $message
     * @param array $data
     * @return bool
     */
    protected function envoyerNotificationEmail(Employeur $employeur, string $type, string $message, array $data = [])
    {
        try {
            // Vérifier si l'email est valide
            if (!filter_var($employeur->email, FILTER_VALIDATE_EMAIL)) {
                Log::channel('presences')->warning("Adresse email invalide", [
                    'email' => $employeur->email,
                    'employeur_id' => $employeur->id,
                    'employeur_nom' => $employeur->nom_complet
                ]);
                return false;
            }
            
            // Préparer les données pour l'email
            $emailData = [
                'message' => $message,
                'employeur' => $employeur,
                'entreprise' => $employeur->entreprise,
                'type' => $type,
                'date' => now()->format('d/m/Y H:i:s'),
                'data' => $data
            ];
            
            // Créer le mailable
            $mailable = new PresenceNotificationEmail($emailData);
            $mailable->subject("Notification GENIUS WORK: " . ucfirst($type));
            
            // Options supplémentaires pour l'envoi
            $options = [];
            
            // Ajouter des BCC si configurés
            $configBcc = config('mail_service.notifications.bcc');
            if (!empty($configBcc)) {
                $options['bcc'] = $configBcc;
            }
            
            // Envoyer l'email via le service SMTP
            $result = $this->smtpEmailService->send($employeur->email, $mailable, $options);
            
            if ($result) {
                Log::channel('presences')->info("Email de notification envoyé", [
                    'to' => $employeur->email,
                    'employeur_id' => $employeur->id,
                    'type' => $type,
                    'subject' => "Notification GENIUS WORK: " . ucfirst($type)
                ]);
            } else {
                Log::channel('presences')->error("Échec de l'envoi de l'email de notification", [
                    'to' => $employeur->email,
                    'employeur_id' => $employeur->id,
                    'type' => $type
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de l'envoi de l'email", [
                'to' => $employeur->email,
                'employeur_id' => $employeur->id,
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }

    /**
     * Envoie un SMS à l'employeur.
     *
     * @param Employeur $employeur
     * @param string $message
     * @return bool
     */
    protected function envoyerSMSEmployeur(Employeur $employeur, string $message)
    {
        try {
            // Nettoyer le numéro de téléphone (supprimer espaces, tirets, etc.)
            $telephone = preg_replace('/[^0-9+]/', '', $employeur->telephone);
            
            // Vérifier si le service SMS est configuré
            if (!config('services.sms.enabled', false)) {
                Log::channel('presences')->info("Service SMS désactivé, message enregistré uniquement", [
                    'to' => $telephone,
                    'employeur_id' => $employeur->id,
                    'employeur_nom' => $employeur->nom_complet
                ]);
                
                // Enregistrer le SMS comme non envoyé mais sans erreur
                $this->smsLogService->logSMS($telephone, $message, 'presence_notification', [
                    'employeur_id' => $employeur->id,
                    'type' => 'presence',
                    'status' => 'skipped'
                ]);
                
                return true; // Retourne true car ce n'est pas une erreur
            }
            
            // Utiliser le service Orange SMS
            $result = $this->orangeSMSService->sendSMS($telephone, $message);
            
            if ($result['success']) {
                Log::channel('presences')->info("SMS envoyé via Orange SMS", [
                    'to' => $telephone,
                    'employeur_id' => $employeur->id,
                    'message_id' => $result['message_id'] ?? null
                ]);
                return true;
            } else {
                Log::channel('presences')->error("Échec de l'envoi du SMS via Orange SMS", [
                    'to' => $telephone,
                    'employeur_id' => $employeur->id,
                    'error' => $result['error']
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de l'envoi du SMS", [
                'to' => $employeur->telephone,
                'employeur_id' => $employeur->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Enregistrer l'erreur dans les logs SMS
            $this->smsLogService->logSMS($employeur->telephone, $message, 'presence_notification', [
                'employeur_id' => $employeur->id,
                'type' => 'presence',
                'status' => 'error',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Envoie une notification à tous les employeurs d'une entreprise.
     *
     * @param Entreprise $entreprise
     * @param string $type
     * @param string $message
     * @param array $data
     * @return int Nombre de notifications envoyées
     */
    public function notifierEntreprise(Entreprise $entreprise, string $type, string $message, array $data = [])
    {
        $employeurs = Employeur::where('entreprise_id', $entreprise->id)->get();
        $count = 0;

        foreach ($employeurs as $employeur) {
            $success = $this->envoyerNotification($employeur, $type, array_merge([
                'nom' => $employeur->nom,
                'prenom' => $employeur->prenom,
                'entreprise' => $entreprise->nom,
            ], $data));

            if ($success) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Crée une nouvelle notification dans la base de données
     *
     * @param int $employeurId ID de l'employeur
     * @param int $entrepriseId ID de l'entreprise
     * @param string $type Type de notification
     * @param string $message Message de la notification
     * @param array $data Données supplémentaires
     * @param string $canal Canal de notification (app, email, sms, tous)
     * @param string $priorite Priorité de la notification (basse, normale, haute)
     * @return \App\Models\Notification
     */
    public function creerNotification($employeurId, $entrepriseId, $type, $message, $data = [], $canal = 'app', $priorite = 'normale')
    {
        $notification = new \App\Models\Notification([
            'employeur_id' => $employeurId,
            'entreprise_id' => $entrepriseId,
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'canal' => $canal,
            'priorite' => $priorite,
            'statut' => 'en_attente',
            'tentatives' => 0
        ]);
        
        $notification->save();
        
        Log::channel('presences')->info("Notification créée", [
            'id' => $notification->id,
            'employeur_id' => $employeurId,
            'type' => $type,
            'canal' => $canal
        ]);
        
        return $notification;
    }

    /**
     * Envoie une notification par email
     *
     * @param string $email Adresse email du destinataire
     * @param string $sujet Sujet de l'email
     * @param string $message Corps du message
     * @param array $data Données supplémentaires
     * @return bool
     */
    public function envoyerEmailNotification($email, $sujet, $message, $data = [])
    {
        try {
            // Vérifier si l'email est valide
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Adresse email invalide", ['email' => $email]);
                return false;
            }
            
            // Utiliser Laravel Mail avec une vue Blade
            Mail::send('emails.notification', [
                'message' => $message,
                'data' => $data,
                'sujet' => $sujet
            ], function ($m) use ($email, $sujet) {
                $m->to($email)->subject($sujet);
                // Ajouter l'expéditeur depuis la configuration
                $m->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            Log::info("Email envoyé", [
                'to' => $email,
                'subject' => $sujet
            ]);
            
            return !Mail::failures();
        } catch (\Exception $e) {
            Log::channel('presences')->error("Erreur lors de l'envoi de l'email", [
                'to' => $email,
                'subject' => $sujet,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Envoie une notification par SMS
     *
     * @param string $telephone Numéro de téléphone du destinataire
     * @param string $message Corps du message
     * @return bool
     */
    public function envoyerSMS($telephone, $message)
    {
        try {
            // Nettoyer le numéro de téléphone (supprimer espaces, tirets, etc.)
            $telephone = preg_replace('/[^0-9+]/', '', $telephone);
            
            // Vérifier si le service SMS est configuré
            if (!config('services.sms.enabled', false)) {
                Log::info("Service SMS désactivé, message enregistré uniquement", [
                    'to' => $telephone
                ]);
                return true; // Retourne true car ce n'est pas une erreur
            }
            
            // Utiliser Twilio si configuré
            if (config('services.twilio.enabled', false)) {
                $twilioSid = config('services.twilio.sid');
                $twilioToken = config('services.twilio.token');
                $twilioFrom = config('services.twilio.from');
                
                if (!empty($twilioSid) && !empty($twilioToken) && !empty($twilioFrom)) {
                    $twilio = new \Twilio\Rest\Client($twilioSid, $twilioToken);
                    $twilio->messages->create($telephone, [
                        'from' => $twilioFrom, 
                        'body' => $message
                    ]);
                    
                    Log::info("SMS envoyé via Twilio", [
                        'to' => $telephone
                    ]);
                    
                    return true;
                }
            }
            
            // Utiliser un autre service SMS si Twilio n'est pas configuré
            // Exemple avec Nexmo/Vonage
            if (config('services.vonage.enabled', false)) {
                $vonage = new \Vonage\Client(new \Vonage\Client\Credentials\Basic(
                    config('services.vonage.key'),
                    config('services.vonage.secret')
                ));
                
                $vonage->sms()->send(
                    new \Vonage\SMS\Message\SMS(
                        $telephone,
                        config('services.vonage.from'),
                        $message
                    )
                );
                
                Log::info("SMS envoyé via Vonage", [
                    'to' => $telephone
                ]);
                
                return true;
            }
            
            // Si aucun service n'est configuré, on log simplement
            Log::info("SMS simulé (aucun service configuré)", [
                'to' => $telephone,
                'message' => $message
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du SMS", [
                'to' => $telephone,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Marque une notification comme lue
     *
     * @param int $notificationId ID de la notification
     * @return bool
     */
    public function marquerCommeLue($notificationId)
    {
        $notification = \App\Models\Notification::find($notificationId);
        
        if (!$notification) {
            return false;
        }
        
        $notification->lu = true;
        $notification->date_lecture = now();
        $notification->status = 'read';
        $notification->save();
        
        return true;
    }

    /**
     * Récupère l'historique des notifications pour un employeur
     *
     * @param int $employeurId ID de l'employeur
     * @param int $limit Nombre maximum de notifications à récupérer
     * @param int $offset Offset pour la pagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistoriqueNotifications($employeurId, $limit = 50, $offset = 0)
    {
        return \App\Models\Notification::where('employeur_id', $employeurId)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Récupère l'historique des notifications pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @param int $limit Nombre maximum de notifications à récupérer
     * @param int $offset Offset pour la pagination
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHistoriqueNotificationsEntreprise($entrepriseId, $limit = 100, $offset = 0)
    {
        return \App\Models\Notification::whereHas('employeur', function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Récupère les statistiques des notifications pour une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @param string $dateDebut Date de début (format Y-m-d)
     * @param string $dateFin Date de fin (format Y-m-d)
     * @return array
     */
    public function getStatistiquesNotifications($entrepriseId, $dateDebut = null, $dateFin = null)
    {
        $query = \App\Models\Notification::whereHas('employeur', function ($query) use ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        });
        
        if ($dateDebut) {
            $query->where('created_at', '>=', $dateDebut . ' 00:00:00');
        }
        
        if ($dateFin) {
            $query->where('created_at', '<=', $dateFin . ' 23:59:59');
        }
        
        // Statistiques globales
        $total = $query->count();
        $lues = $query->where('lu', true)->count();
        $nonLues = $total - $lues;
        
        // Statistiques par type
        $parType = $query->select('type_notification', DB::raw('count(*) as total'))
            ->groupBy('type_notification')
            ->get()
            ->pluck('total', 'type_notification')
            ->toArray();
        
        // Statistiques par statut
        $parStatut = $query->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
        
        return [
            'total' => $total,
            'lues' => $lues,
            'non_lues' => $nonLues,
            'par_type' => $parType,
            'par_statut' => $parStatut
        ];
    }

    /**
     * Envoie une notification de retard à un employeur
     *
     * @param \App\Models\Employeur $employeur Employeur
     * @param \App\Models\Entreprise $entreprise Entreprise
     * @param \App\Models\PlageHoraire $plageHoraire Plage horaire
     * @param \App\Models\Presence|null $presence Présence (optionnel)
     * @return \App\Models\Notification
     */
    public function notifierRetardPresence($employeur, $entreprise, $plageHoraire, $presence = null)
    {
        // Récupérer la configuration de présence
        $configService = app(\App\Services\ConfigurationPresenceService::class);
        $config = $configService->getConfigurationForEntreprise($entreprise->id);
        
        if (!$config || !$config->notifier_retards) {
            return null;
        }
        
        // Préparer le message
        $message = $config->message_retard ?? "Vous êtes en retard pour votre plage horaire de {debut}.";
        $message = str_replace('{debut}', \Carbon\Carbon::parse($plageHoraire->heure_debut)->format('H:i'), $message);
        $message = str_replace('{fin}', \Carbon\Carbon::parse($plageHoraire->heure_fin)->format('H:i'), $message);
        $message = str_replace('{nom}', $employeur->nom, $message);
        $message = str_replace('{prenom}', $employeur->prenom, $message);
        
        $data = [
            'type' => 'retard',
            'plage_horaire_id' => $plageHoraire->id,
            'heure_debut' => $plageHoraire->heure_debut,
            'heure_fin' => $plageHoraire->heure_fin,
            'presence_id' => $presence ? $presence->id : null
        ];
        
        // Créer la notification
        $notification = $this->creerNotification(
            $employeur->id,
            $entreprise->id,
            'retard',
            $message,
            $data
        );
        
        // Envoyer par email si configuré
        if ($config->notifier_par_email) {
            try {
                $this->envoyerEmailNotification(
                    $employeur->email,
                    "Notification GENIUS WORK: Retard",
                    $message,
                    $data
                );
                $notification->marquerEmailEnvoye();
            } catch (\Exception $e) {
                Log::channel('presences')->error("Erreur lors de l'envoi de l'email de retard: " . $e->getMessage());
            }
        }
        
        // Envoyer par SMS si configuré
        if ($config->notifier_par_sms && $employeur->telephone) {
            try {
                $this->envoyerSMS(
                    $employeur->telephone,
                    $message
                );
                $notification->marquerSmsEnvoye();
            } catch (\Exception $e) {
                Log::channel('presences')->error("Erreur lors de l'envoi du SMS de retard: " . $e->getMessage());
            }
        }
        
        // Marquer la notification comme envoyée
        $notification->marquerCommeEnvoyee();
        
        return $notification;
    }

    /**
     * Envoie une notification d'absence à un employeur
     *
     * @param \App\Models\Employeur $employeur Employeur
     * @param \App\Models\Entreprise $entreprise Entreprise
     * @param \App\Models\PlageHoraire $plageHoraire Plage horaire
     * @return \App\Models\Notification
     */
    public function notifierAbsencePresence($employeur, $entreprise, $plageHoraire)
    {
        // Récupérer la configuration de présence
        $configService = app(\App\Services\ConfigurationPresenceService::class);
        $config = $configService->getConfigurationForEntreprise($entreprise->id);
        
        if (!$config || !$config->notifier_absences) {
            return null;
        }
        
        // Préparer le message
        $message = $config->message_absence ?? "Vous êtes absent(e) pour votre plage horaire de {debut} à {fin}.";
        $message = str_replace('{debut}', \Carbon\Carbon::parse($plageHoraire->heure_debut)->format('H:i'), $message);
        $message = str_replace('{fin}', \Carbon\Carbon::parse($plageHoraire->heure_fin)->format('H:i'), $message);
        $message = str_replace('{nom}', $employeur->nom, $message);
        $message = str_replace('{prenom}', $employeur->prenom, $message);
        
        $data = [
            'type' => 'absence',
            'plage_horaire_id' => $plageHoraire->id,
            'heure_debut' => $plageHoraire->heure_debut,
            'heure_fin' => $plageHoraire->heure_fin
        ];
        
        // Créer la notification
        $notification = $this->creerNotification(
            $employeur->id,
            $entreprise->id,
            'absence',
            $message,
            $data
        );
        
        // Envoyer par email si configuré
        if ($config->notifier_par_email) {
            try {
                $this->envoyerEmailNotification(
                    $employeur->email,
                    "Notification GENIUS WORK: Absence",
                    $message,
                    $data
                );
                $notification->marquerEmailEnvoye();
            } catch (\Exception $e) {
                Log::channel('presences')->error("Erreur lors de l'envoi de l'email d'absence: " . $e->getMessage());
            }
        }
        
        // Envoyer par SMS si configuré
        if ($config->notifier_par_sms && $employeur->telephone) {
            try {
                $this->envoyerSMS(
                    $employeur->telephone,
                    $message
                );
                $notification->marquerSmsEnvoye();
            } catch (\Exception $e) {
                Log::channel('presences')->error("Erreur lors de l'envoi du SMS d'absence: " . $e->getMessage());
            }
        }
        
        // Marquer la notification comme envoyée
        $notification->marquerCommeEnvoyee();
        
        return $notification;
    }

    /**
     * Envoie une notification de sortie manquante à un employeur
     *
     * @param \App\Models\Employeur $employeur Employeur
     * @param \App\Models\Entreprise $entreprise Entreprise
     * @param \App\Models\PlageHoraire $plageHoraire Plage horaire
     * @param \App\Models\Presence $presence Présence
     * @return \App\Models\Notification
     */
    public function notifierSortieManquante($employeur, $entreprise, $plageHoraire, $presence)
    {
        // Récupérer la configuration de présence
        $configService = app(\App\Services\ConfigurationPresenceService::class);
        $config = $configService->getConfigurationForEntreprise($entreprise->id);
        
        if (!$config || !$config->notifier_sorties_manquantes) {
            return null;
        }
        
        // Préparer le message
        $message = $config->message_sortie_manquante ?? "Vous n'avez pas pointé votre sortie pour la plage horaire de {debut} à {fin}.";
        $message = str_replace('{debut}', \Carbon\Carbon::parse($plageHoraire->heure_debut)->format('H:i'), $message);
        $message = str_replace('{fin}', \Carbon\Carbon::parse($plageHoraire->heure_fin)->format('H:i'), $message);
        $message = str_replace('{nom}', $employeur->nom, $message);
        $message = str_replace('{prenom}', $employeur->prenom, $message);
        
        $data = [
            'type' => 'sortie_manquante',
            'plage_horaire_id' => $plageHoraire->id,
            'heure_debut' => $plageHoraire->heure_debut,
            'heure_fin' => $plageHoraire->heure_fin,
            'presence_id' => $presence->id
        ];
        
        // Créer la notification
        $notification = $this->creerNotification(
            $employeur->id,
            $entreprise->id,
            'sortie_manquante',
            $message,
            $data
        );
        
        // Envoyer par email si configuré
        if ($config->notifier_par_email) {
            try {
                $this->envoyerEmailNotification(
                    $employeur->email,
                    "Notification GENIUS WORK: Sortie manquante",
                    $message,
                    $data
                );
                $notification->marquerEmailEnvoye();
            } catch (\Exception $e) {
                Log::channel('presences')->error("Erreur lors de l'envoi de l'email de sortie manquante: " . $e->getMessage());
            }
        }
        
        // Envoyer par SMS si configuré
        if ($config->notifier_par_sms && $employeur->telephone) {
            try {
                $this->envoyerSMS(
                    $employeur->telephone,
                    $message
                );
                $notification->marquerSmsEnvoye();
            } catch (\Exception $e) {
                Log::channel('presences')->error("Erreur lors de l'envoi du SMS de sortie manquante: " . $e->getMessage());
            }
        }
        
        // Marquer la notification comme envoyée
        $notification->marquerCommeEnvoyee();
        
        return $notification;
    }

    /**
     * Récupère et personnalise un message de notification
     *
     * @param int $entrepriseId ID de l'entreprise
     * @param string $type Type de notification (retard, absence, sortie_manquante, etc.)
     * @param array $variables Variables à remplacer dans le message
     * @return string
     */
    public function getMessagePersonnalise($entrepriseId, $type, $variables = [])
    {
        // Récupérer le modèle de message depuis la configuration de l'entreprise
        $messageTemplate = DB::table('configurations_notifications')
            ->where('entreprise_id', $entrepriseId)
            ->where('type', $type)
            ->value('message_template');
        
        // Si aucun modèle n'est trouvé, utiliser un message par défaut
        if (empty($messageTemplate)) {
            $messageTemplate = $this->getMessageParDefaut($type);
        }
        
        // Remplacer les variables dans le message
        foreach ($variables as $key => $value) {
            $messageTemplate = str_replace('{' . $key . '}', $value, $messageTemplate);
        }
        
        return $messageTemplate;
    }
    
    /**
     * Récupère un message par défaut pour un type de notification
     *
     * @param string $type Type de notification
     * @return string
     */
    private function getMessageParDefaut($type)
    {
        $messages = [
            'retard' => 'Bonjour {nom}, vous êtes en retard de {minutes} minutes aujourd\'hui ({date}).',
            'absence' => 'Bonjour {nom}, votre absence a été enregistrée pour le {date}.',
            'sortie_manquante' => 'Bonjour {nom}, vous n\'avez pas enregistré votre sortie le {date}.',
            'conge_approuve' => 'Bonjour {nom}, votre demande de congé pour la période du {debut} au {fin} a été approuvée.',
            'conge_refuse' => 'Bonjour {nom}, votre demande de congé pour la période du {debut} au {fin} a été refusée.',
            'rappel_presence' => 'Bonjour {nom}, n\'oubliez pas d\'enregistrer votre présence aujourd\'hui.',
        ];
        
        return $messages[$type] ?? 'Notification du système GENIUS WORK.';
    }
}
