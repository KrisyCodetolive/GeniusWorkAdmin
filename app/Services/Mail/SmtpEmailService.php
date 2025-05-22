<?php

namespace App\Services\Mail;

use App\Mail\UserWelcomeEmail;
use App\Mail\CompanyWelcomeEmail;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;
use App\Services\Mail\EmailLogger;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Mail\Mailable;

class SmtpEmailService implements EmailServiceInterface
{
    /**
     * Instance du logger d'emails
     * 
     * @var EmailLogger
     */
    protected $logger;
    
    /**
     * Configuration SMTP
     * 
     * @var array
     */
    protected $config;
    
    /**
     * Constructeur du service
     */
    public function __construct()
    {
        $this->logger = new EmailLogger();
        $this->config = Config::get('mail_service.smtp');
    }
    
    /**
     * Envoyer un email via SMTP
     *
     * @param string|array $to Destinataire(s) de l'email
     * @param Mailable $mailable Instance de Mailable à envoyer
     * @param array $options Options supplémentaires pour l'envoi
     * @return bool Succès de l'envoi
     */
    /**
     * Vérifier la validité de la configuration SMTP
     *
     * @return bool
     */
    protected function validateSmtpConfig(): bool
    {
        $requiredFields = ['host', 'port', 'username', 'password', 'encryption'];
        
        foreach ($requiredFields as $field) {
            if (empty($this->config[$field])) {
                $this->logger->emailError(new \Exception("Configuration SMTP manquante: {$field}"), [
                    'field' => $field,
                    'context' => 'smtp_config_validation'
                ]);
                return false;
            }
        }
        
        // Vérifier que le chiffrement est valide (tls ou smtps)
        if (!in_array($this->config['encryption'], ['tls', 'smtps'])) {
            $this->logger->emailError(new \Exception("Type de chiffrement SMTP non supporté: {$this->config['encryption']}"), [
                'encryption' => $this->config['encryption'],
                'context' => 'smtp_config_validation'
            ]);
            return false;
        }
        
        return true;
    }
    
    public function send($to, $mailable, array $options = []): bool
    {
        // Vérifier d'abord que la configuration SMTP est valide
        if (!$this->validateSmtpConfig()) {
            return false;
        }
        
        try {
            // Configurer l'expéditeur si non défini dans le mailable
            if (!$mailable->from && isset($this->config['from_address'])) {
                // S'assurer qu'un nom d'expéditeur est toujours défini pour réduire le risque de spam
                $fromName = !empty($this->config['from_name']) ? $this->config['from_name'] : 'Genius Work';
                $mailable->from($this->config['from_address'], $fromName);
                
                // Ajouter un Reply-To pour améliorer la délivrabilité
                if (!empty($this->config['reply_to_address'])) {
                    $replyToName = !empty($this->config['reply_to_name']) ? $this->config['reply_to_name'] : $fromName;
                    $mailable->replyTo($this->config['reply_to_address'], $replyToName);
                } else {
                    $mailable->replyTo($this->config['from_address'], $fromName);
                }
            }
            
            // Ajouter des options supplémentaires si nécessaire
            $mailer = Mail::to($to);
            
            if (isset($options['cc'])) {
                $mailer->cc($options['cc']);
            }
            
            // Gérer les BCC (combinaison des BCC par défaut et des BCC spécifiés)
            $bccAddresses = [];
            
            // Ajouter les BCC par défaut depuis la configuration
            if (isset($this->config['default_bcc']) && is_array($this->config['default_bcc'])) {
                $bccAddresses = array_merge($bccAddresses, $this->config['default_bcc']);
            }
            
            // Ajouter les BCC spécifiés dans les options
            if (isset($options['bcc'])) {
                if (is_array($options['bcc'])) {
                    $bccAddresses = array_merge($bccAddresses, $options['bcc']);
                } else {
                    $bccAddresses[] = $options['bcc'];
                }
            }
            
            // Appliquer les BCC s'il y en a
            if (!empty($bccAddresses)) {
                $mailer->bcc($bccAddresses);
                
                // Logger l'ajout des BCC
                $this->logger->info('Ajout des destinataires BCC', [
                    'bcc_addresses' => $bccAddresses,
                    'email_to' => $to
                ]);
            }
            
            // Ajouter des en-têtes supplémentaires pour améliorer la délivrabilité
            $mailable->withSwiftMessage(function ($message) use ($to) {
                $message->getHeaders()
                    ->addTextHeader('X-Mailer', 'Genius Work Mailer')
                    ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
                    
                // Ajouter un Message-ID unique basé sur le domaine de l'expéditeur
                if (!empty($this->config['from_address'])) {
                    $domain = explode('@', $this->config['from_address'])[1] ?? 'genius.ci';
                    $uniqueId = uniqid() . '.' . time();
                    $message->getHeaders()->addTextHeader('Message-ID', "<{$uniqueId}@{$domain}>");
                }
            });
            
            // Envoyer l'email
            $mailer->send($mailable);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->emailError($e, [
                'recipient' => $to,
                'mailable_class' => get_class($mailable),
                'smtp_host' => $this->config['host'],
                'smtp_port' => $this->config['port']
            ]);
            return false;
        }
    }
    
    /**
     * Envoyer un email de bienvenue à un utilisateur
     *
     * @param User|null $user
     * @param Entreprise|null $entreprise
     * @param Abonnement|null $abonnement
     * @return bool
     */
    public function sendUserWelcome($user, $entreprise, $abonnement): bool
    {
        // Vérifier que tous les paramètres nécessaires sont présents
        if (!$user || !$entreprise || !$abonnement) {
            $this->logger->error('Impossible d\'envoyer l\'email de bienvenue utilisateur : paramètres manquants', [
                'user_present' => (bool) $user,
                'entreprise_present' => (bool) $entreprise,
                'abonnement_present' => (bool) $abonnement
            ]);
            return false;
        }
        
        // Vérifier que l'utilisateur a une adresse email valide
        if (empty($user->email)) {
            $this->logger->error('Impossible d\'envoyer l\'email de bienvenue utilisateur : email manquant', [
                'user_id' => $user->id
            ]);
            return false;
        }
        
        // Vérifier la validité de la configuration SMTP avant de tenter d'envoyer l'email
        if (!$this->validateSmtpConfig()) {
            $this->logger->error('Impossible d\'envoyer l\'email de bienvenue utilisateur : configuration SMTP invalide', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return false;
        }
        
        try {
            // Journaliser la tentative d'envoi
            $this->logger->info('Tentative d\'envoi d\'email de bienvenue utilisateur', [
                'user_id' => $user->id,
                'email' => $user->email,
                'entreprise_id' => $entreprise->id,
                'abonnement_id' => $abonnement->id
            ]);
            
            $mailable = new UserWelcomeEmail($user, $entreprise, $abonnement);
            
            $result = $this->send($user->email, $mailable, [
                'bcc' => []
            ]);
            
            // Journaliser le résultat
            if ($result) {
                $this->logger->info('Email de bienvenue utilisateur envoyé avec succès', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            } else {
                $this->logger->error('Échec de l\'envoi de l\'email de bienvenue utilisateur', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->emailError($e, [
                'user_id' => $user->id,
                'entreprise_id' => $entreprise->id,
                'abonnement_id' => $abonnement->id,
                'context' => 'user_welcome_email'
            ]);
            return false;
        }
    }
    
    /**
     * Envoyer un email de bienvenue à une entreprise
     *
     * @param User|null $user
     * @param Entreprise|null $entreprise
     * @param Abonnement|null $abonnement
     * @return bool
     */
    public function sendCompanyWelcome($user, $entreprise, $abonnement): bool
    {
        // Vérifier que tous les paramètres nécessaires sont présents
        if (!$user || !$entreprise || !$abonnement) {
            $this->logger->error('Impossible d\'envoyer l\'email de bienvenue entreprise : paramètres manquants', [
                'user_present' => (bool) $user,
                'entreprise_present' => (bool) $entreprise,
                'abonnement_present' => (bool) $abonnement
            ]);
            return false;
        }
        
        // Ne pas envoyer si l'email de l'entreprise n'existe pas
        // ou s'il est identique à celui de l'utilisateur
        if (empty($entreprise->email) || ($user && $entreprise->email === $user->email)) {
            $this->logger->info('Email de bienvenue entreprise non envoyé : email manquant ou identique à celui de l\'utilisateur', [
                'entreprise_id' => $entreprise->id,
                'email_entreprise' => $entreprise->email ?? 'non défini',
                'user_email' => $user->email ?? 'non défini'
            ]);
            return false;
        }
        
        // Vérifier la validité de la configuration SMTP avant de tenter d'envoyer l'email
        if (!$this->validateSmtpConfig()) {
            $this->logger->error('Impossible d\'envoyer l\'email de bienvenue entreprise : configuration SMTP invalide', [
                'entreprise_id' => $entreprise->id,
                'email' => $entreprise->email
            ]);
            return false;
        }
        
        try {
            // Journaliser la tentative d'envoi
            $this->logger->info('Tentative d\'envoi d\'email de bienvenue entreprise', [
                'entreprise_id' => $entreprise->id,
                'email' => $entreprise->email,
                'user_id' => $user->id,
                'abonnement_id' => $abonnement->id
            ]);
            
            $mailable = new CompanyWelcomeEmail($user, $entreprise, $abonnement);
            
            $result = $this->send($entreprise->email, $mailable, [
                'bcc' => []
            ]);
            
            // Journaliser le résultat
            if ($result) {
                $this->logger->info('Email de bienvenue entreprise envoyé avec succès', [
                    'entreprise_id' => $entreprise->id,
                    'email' => $entreprise->email
                ]);
            } else {
                $this->logger->error('Échec de l\'envoi de l\'email de bienvenue entreprise', [
                    'entreprise_id' => $entreprise->id,
                    'email' => $entreprise->email
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->emailError($e, [
                'entreprise_id' => $entreprise->id,
                'email' => $entreprise->email,
                'user_id' => $user->id,
                'abonnement_id' => $abonnement->id,
                'context' => 'company_welcome_email'
            ]);
            return false;
        }
    }
}
