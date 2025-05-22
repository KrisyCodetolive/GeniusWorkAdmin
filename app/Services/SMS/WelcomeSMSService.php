<?php

namespace App\Services\SMS;

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;

class WelcomeSMSService implements SMSServiceInterface
{
    protected $smsService;
    protected $smsLogService;
    protected $smsLogger;

    /**
     * Constructeur du service d'envoi de SMS de bienvenue
     *
     * @param OrangeSMSService $smsService Service d'envoi de SMS
     * @param SMSLogService $smsLogService Service de journalisation des SMS en base de données
     * @param SMSLogger $smsLogger Service de journalisation des SMS dans les logs
     */
    public function __construct(OrangeSMSService $smsService, SMSLogService $smsLogService, SMSLogger $smsLogger)
    {
        $this->smsService = $smsService;
        $this->smsLogService = $smsLogService;
        $this->smsLogger = $smsLogger;
    }

    /**
     * Envoie un SMS de bienvenue à l'utilisateur
     *
     * @param User $user L'utilisateur
     * @param Entreprise $entreprise L'entreprise
     * @param Abonnement $abonnement L'abonnement
     * @return array Résultat de l'envoi
     */
    public function sendUserWelcomeSMS($user, $entreprise, $abonnement)
    {
        try {
            // Vérifier si l'utilisateur existe et a un numéro de téléphone
            if (!$user) {
                $this->smsLogger->missingPhoneNumber('user', 'unknown');
                return ['success' => false, 'error' => 'Utilisateur non disponible'];
            }
            
            // Récupérer le numéro de téléphone (vérifier les différentes propriétés possibles)
            $phoneNumber = $user->telephone ?? $user->phone_number ?? $user->phone ?? null;
            
            if (!$phoneNumber) {
                $this->smsLogger->missingPhoneNumber('user', $user->id ?? 'unknown');
                return ['success' => false, 'error' => 'Numéro de téléphone utilisateur non disponible'];
            }

            $this->smsLogger->info('Préparation du SMS de bienvenue utilisateur', [
                'user_id' => $user->id,
                'phone' => $phoneNumber
            ]);

            $message = $this->formatUserWelcomeMessage($user, $entreprise, $abonnement);
            
            // Enregistrer des données supplémentaires pour le log
            $additionalData = [
                'notifiable_id' => $user->id,
                'notifiable_type' => get_class($user)
            ];

            // Envoyer le SMS
            $result = $this->smsService->sendSMS($phoneNumber, $message);
            
            // Journaliser le résultat
            if ($result['success'] ?? false) {
                $this->smsLogger->userSMSSent($user->id, $phoneNumber);
            } else {
                $this->smsLogger->error('Échec de l\'envoi du SMS utilisateur', [
                    'user_id' => $user->id,
                    'phone' => $phoneNumber,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            // Journaliser l'erreur
            $this->smsLogger->error('Exception lors de l\'envoi du SMS utilisateur', [
                'user_id' => $user->id ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'error' => 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage()];
        }
    }

    /**
     * Envoie un SMS de bienvenue à l'entreprise
     *
     * @param User $user L'utilisateur administrateur
     * @param Entreprise $entreprise L'entreprise
     * @param Abonnement $abonnement L'abonnement
     * @return array Résultat de l'envoi
     */
    public function sendCompanyWelcomeSMS($user, $entreprise, $abonnement)
    {
        try {
            // Vérifier si l'entreprise existe
            if (!$entreprise) {
                $this->smsLogger->missingPhoneNumber('company', 'unknown');
                return ['success' => false, 'error' => 'Entreprise non disponible'];
            }
            
            // Récupérer le numéro de téléphone (vérifier les différentes propriétés possibles)
            $phoneNumber = $entreprise->telephone ?? $entreprise->phone_number ?? $entreprise->phone ?? null;
            
            if (!$phoneNumber) {
                $this->smsLogger->missingPhoneNumber('company', $entreprise->id ?? 'unknown');
                return ['success' => false, 'error' => 'Numéro de téléphone entreprise non disponible'];
            }

            $this->smsLogger->info('Préparation du SMS de bienvenue entreprise', [
                'entreprise_id' => $entreprise->id,
                'phone' => $phoneNumber
            ]);

            $message = $this->formatCompanyWelcomeMessage($user, $entreprise, $abonnement);
            
            // Enregistrer des données supplémentaires pour le log
            $additionalData = [
                'notifiable_id' => $entreprise->id,
                'notifiable_type' => get_class($entreprise)
            ];

            // Envoyer le SMS
            $result = $this->smsService->sendSMS($phoneNumber, $message);
            
            // Journaliser le résultat
            if ($result['success'] ?? false) {
                $this->smsLogger->companySMSSent($entreprise->id, $phoneNumber);
            } else {
                $this->smsLogger->error('Échec de l\'envoi du SMS entreprise', [
                    'entreprise_id' => $entreprise->id,
                    'phone' => $phoneNumber,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            // Journaliser l'erreur
            $this->smsLogger->error('Exception lors de l\'envoi du SMS entreprise', [
                'entreprise_id' => $entreprise->id ?? 'unknown',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return ['success' => false, 'error' => 'Erreur lors de l\'envoi du SMS: ' . $e->getMessage()];
        }
    }

    /**
     * Formate le message de bienvenue pour l'utilisateur
     *
     * @param User $user L'utilisateur
     * @param Entreprise $entreprise L'entreprise
     * @param Abonnement $abonnement L'abonnement
     * @return string Le message formaté
     */
    protected function formatUserWelcomeMessage($user, $entreprise, $abonnement)
    {
        // Vérifier si l'utilisateur existe
        $userName = $user && isset($user->name) ? $user->name : 'utilisateur';
        $message = "Bienvenue {$userName} sur Genius Work !\n\n";
        $message .= "Votre compte a été créé avec succès.\n";
        
        // Vérifier si l'entreprise existe
        if ($entreprise && isset($entreprise->nom)) {
            $message .= "Vous êtes maintenant membre de {$entreprise->nom}.\n";
        }
        
        $message .= "\nConnectez-vous sur https://work.dia.ci/admin pour accéder à votre tableau de bord.\n";
        $message .= "\nBesoin d'aide ? Discutez avec Jérémie N'da sur WhatsApp: https://wa.me/2250704750465\n";
        $message .= "\nL'Équipe Genius Work";
        
        return $message;
    }

    /**
     * Formate le message de bienvenue pour l'entreprise
     *
     * @param User $user L'utilisateur administrateur
     * @param Entreprise $entreprise L'entreprise
     * @param Abonnement $abonnement L'abonnement
     * @return string Le message formaté
     */
    protected function formatCompanyWelcomeMessage($user, $entreprise, $abonnement)
    {
        // Vérifier si l'entreprise existe
        $companyName = $entreprise && isset($entreprise->nom) ? $entreprise->nom : 'entreprise';
        $message = "Bienvenue {$companyName} sur Genius Work!\n\n";
        $message .= "Votre entreprise a été enregistrée avec succès.\n";
        
        // Vérifier si le plan existe avant d'accéder à ses propriétés
        if ($abonnement && $abonnement->plan) {
            $message .= "Abonnement: {$abonnement->plan->name}.\n";
        } else {
            $message .= "Votre abonnement a été activé.\n";
        }
        
        $message .= "\nConnectez-vous sur https://work.dia.ci/admin pour accéder à votre tableau de bord.\n";
        $message .= "\nBesoin d'aide ? Discutez avec Jérémie N'da sur WhatsApp: https://wa.me/2250704750465\n";
        $message .= "\nL'Équipe Genius Work";
        
        return $message;
    }
}
