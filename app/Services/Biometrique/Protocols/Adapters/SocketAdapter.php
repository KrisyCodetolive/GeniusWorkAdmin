<?php

namespace App\Services\Biometrique\Protocols\Adapters;

use App\Services\Biometrique\Protocols\Interfaces\ConnectionAdapterInterface;
use Illuminate\Support\Facades\Log;

/**
 * Adaptateur pour les connexions socket TCP/IP
 */
class SocketAdapter implements ConnectionAdapterInterface
{
    /**
     * @var resource|null
     */
    protected $socket = null;
    
    /**
     * @var string
     */
    protected $host;
    
    /**
     * @var int
     */
    protected $port;
    
    /**
     * @var int
     */
    protected $timeout;
    
    /**
     * @var string
     */
    protected $lastError = '';
    
    /**
     * Constructeur
     *
     * @param string $host
     * @param int $port
     * @param int $timeout
     */
    public function __construct(string $host, int $port, int $timeout = 5)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }
    
    /**
     * Établit une connexion
     *
     * @return bool
     */
    public function connect(): bool
    {
        try {
            $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
            
            if (!$this->socket) {
                $this->lastError = "Connexion échouée: $errstr ($errno)";
                return false;
            }
            
            stream_set_timeout($this->socket, $this->timeout);
            return true;
        } catch (\Exception $e) {
            $this->lastError = "Exception lors de la connexion: " . $e->getMessage();
            Log::error('Erreur de connexion socket', [
                'host' => $this->host,
                'port' => $this->port,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Ferme la connexion
     *
     * @return bool
     */
    public function disconnect(): bool
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
            return true;
        }
        
        return false;
    }
    
    /**
     * Vérifie si la connexion est établie
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->socket !== null && !feof($this->socket);
    }
    
    /**
     * Envoie des données
     *
     * @param string $data
     * @return bool
     */
    public function send(string $data): bool
    {
        if (!$this->isConnected()) {
            $this->lastError = "Non connecté";
            return false;
        }
        
        try {
            $result = fwrite($this->socket, $data);
            return $result !== false;
        } catch (\Exception $e) {
            $this->lastError = "Erreur d'envoi: " . $e->getMessage();
            Log::error('Erreur d\'envoi de données socket', [
                'host' => $this->host,
                'port' => $this->port,
                'data_length' => strlen($data),
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Reçoit des données
     *
     * @param int $length
     * @param bool $waitForComplete
     * @return string|null
     */
    public function receive(int $length = 1024, bool $waitForComplete = false): ?string
    {
        if (!$this->isConnected()) {
            $this->lastError = "Non connecté";
            return null;
        }
        
        try {
            $response = '';
            
            if ($waitForComplete) {
                while (!feof($this->socket)) {
                    $buffer = fread($this->socket, $length);
                    if ($buffer === false) {
                        break;
                    }
                    
                    $response .= $buffer;
                    
                    // Vérifier si la réponse est complète
                    if (strpos($buffer, "\r\n") !== false || strlen($buffer) < $length) {
                        break;
                    }
                }
            } else {
                $response = fread($this->socket, $length);
                
                if ($response === false) {
                    $this->lastError = "Erreur de lecture";
                    return null;
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            $this->lastError = "Erreur de réception: " . $e->getMessage();
            Log::error('Erreur de réception de données socket', [
                'host' => $this->host,
                'port' => $this->port,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Récupère la dernière erreur
     *
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }
}
