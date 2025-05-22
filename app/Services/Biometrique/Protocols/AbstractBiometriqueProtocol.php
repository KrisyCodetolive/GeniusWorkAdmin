<?php

namespace App\Services\Biometrique\Protocols;

use App\Models\AppareilBiometrique;
use App\Services\Biometrique\Protocols\Interfaces\BiometriqueProtocolInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Classe abstraite de base pour tous les protocoles biométriques
 */
abstract class AbstractBiometriqueProtocol implements BiometriqueProtocolInterface
{
    /**
     * @var AppareilBiometrique
     */
    protected $appareil;
    
    /**
     * @var bool
     */
    protected $connected = false;
    
    /**
     * @var string
     */
    protected $errorMessage = '';
    
    /**
     * @var array
     */
    protected $deviceInfo = [];
    
    /**
     * Constructeur
     *
     * @param AppareilBiometrique $appareil
     */
    public function __construct(AppareilBiometrique $appareil)
    {
        $this->appareil = $appareil;
    }
    
    /**
     * Vérifie si l'appareil est connecté
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }
    
    /**
     * Récupère les informations de l'appareil
     *
     * @return array|null
     */
    public function getDeviceInfo(): ?array
    {
        if (!$this->isConnected()) {
            return null;
        }
        
        return $this->deviceInfo;
    }
    
    /**
     * Journalise une erreur
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->errorMessage = $message;
        
        $contextWithDevice = array_merge([
            'appareil_id' => $this->appareil->id,
            'fabricant' => $this->appareil->fabricant,
            'modele' => $this->appareil->modele,
            'adresse_ip' => $this->appareil->adresse_ip,
            'port' => $this->appareil->port
        ], $context);
        
        Log::error($message, $contextWithDevice);
    }
    
    /**
     * Journalise une information
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $contextWithDevice = array_merge([
            'appareil_id' => $this->appareil->id,
            'fabricant' => $this->appareil->fabricant,
            'modele' => $this->appareil->modele
        ], $context);
        
        Log::info($message, $contextWithDevice);
    }
    
    /**
     * Génère un identifiant unique pour une transaction
     *
     * @return string
     */
    protected function generateTransactionId(): string
    {
        return uniqid('bio_', true);
    }
    
    /**
     * Formate une date pour les requêtes
     *
     * @param string|null $date
     * @return string|null
     */
    protected function formatDate(?string $date): ?string
    {
        if (!$date) {
            return null;
        }
        
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }
    
    /**
     * Vérifie si une réponse contient une erreur
     *
     * @param mixed $response
     * @param string $successKey
     * @param string $errorKey
     * @return bool
     */
    protected function hasError($response, string $successKey = 'success', string $errorKey = 'error'): bool
    {
        if (is_array($response)) {
            if (isset($response[$errorKey]) && !empty($response[$errorKey])) {
                $this->errorMessage = is_string($response[$errorKey]) 
                    ? $response[$errorKey] 
                    : json_encode($response[$errorKey]);
                return true;
            }
            
            if (isset($response[$successKey]) && $response[$successKey] === false) {
                $this->errorMessage = isset($response['message']) 
                    ? $response['message'] 
                    : 'Unknown error';
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Récupère le dernier message d'erreur
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->errorMessage;
    }
}
