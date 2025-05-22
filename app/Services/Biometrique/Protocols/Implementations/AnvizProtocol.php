<?php

namespace App\Services\Biometrique\Protocols\Implementations;

use App\Models\AppareilBiometrique;
use App\Models\User;
use App\Services\Biometrique\Protocols\AbstractBiometriqueProtocol;
use App\Services\Biometrique\Protocols\Adapters\SocketAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Implémentation du protocole pour les appareils Anviz
 */
class AnvizProtocol extends AbstractBiometriqueProtocol
{
    /**
     * @var SocketAdapter
     */
    protected $adapter;
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * @var string
     */
    protected $deviceId = '0';
    
    /**
     * Constructeur
     *
     * @param AppareilBiometrique $appareil
     */
    public function __construct(AppareilBiometrique $appareil)
    {
        parent::__construct($appareil);
        
        $this->config = $appareil->configuration ?? [];
        $this->deviceId = $this->config['device_id'] ?? '0';
        
        // Créer l'adaptateur socket
        $this->adapter = new SocketAdapter(
            $appareil->adresse_ip,
            $appareil->port ?? 5010,
            $this->config['timeout'] ?? 5
        );
    }
    
    /**
     * Établit une connexion avec l'appareil
     *
     * @return bool
     */
    public function connect(): bool
    {
        $this->logInfo("Tentative de connexion à l'appareil Anviz", [
            'adresse_ip' => $this->appareil->adresse_ip,
            'port' => $this->appareil->port
        ]);
        
        $result = $this->adapter->connect();
        
        if (!$result) {
            $this->logError("Échec de connexion à l'appareil Anviz", [
                'error' => $this->adapter->getLastError()
            ]);
            return false;
        }
        
        // Envoyer une commande de vérification
        $response = $this->sendCommand('INFO');
        
        if (!$response) {
            $this->logError("Connexion établie mais impossible de communiquer avec l'appareil");
            $this->adapter->disconnect();
            return false;
        }
        
        $this->connected = true;
        $this->deviceInfo = $this->parseDeviceInfo($response);
        
        $this->logInfo("Connexion réussie à l'appareil Anviz", [
            'device_info' => $this->deviceInfo
        ]);
        
        return true;
    }
    
    /**
     * Ferme la connexion avec l'appareil
     *
     * @return bool
     */
    public function disconnect(): bool
    {
        if (!$this->connected) {
            return true;
        }
        
        $result = $this->adapter->disconnect();
        
        if ($result) {
            $this->connected = false;
            $this->logInfo("Déconnexion réussie de l'appareil Anviz");
        } else {
            $this->logError("Échec de déconnexion de l'appareil Anviz", [
                'error' => $this->adapter->getLastError()
            ]);
        }
        
        return $result;
    }
    
    /**
     * Envoie une commande à l'appareil
     *
     * @param string $command
     * @param array $params
     * @return string|null
     */
    protected function sendCommand(string $command, array $params = []): ?string
    {
        // Préparer les paramètres
        $paramString = '';
        foreach ($params as $key => $value) {
            $paramString .= "&{$key}={$value}";
        }
        
        // Construire la commande complète
        $fullCommand = "~{$this->deviceId}#{$command}{$paramString}\r\n";
        
        // Envoyer la commande
        $result = $this->adapter->send($fullCommand);
        
        if (!$result) {
            $this->logError("Échec d'envoi de la commande", [
                'command' => $command,
                'error' => $this->adapter->getLastError()
            ]);
            return null;
        }
        
        // Recevoir la réponse
        $response = $this->adapter->receive(4096, true);
        
        if (!$response) {
            $this->logError("Aucune réponse reçue", [
                'command' => $command,
                'error' => $this->adapter->getLastError()
            ]);
            return null;
        }
        
        // Vérifier si la réponse contient une erreur
        if (strpos($response, 'ERROR') === 0) {
            $this->logError("Erreur reçue de l'appareil", [
                'command' => $command,
                'response' => $response
            ]);
            return null;
        }
        
        return $response;
    }
    
    /**
     * Parse les informations de l'appareil
     *
     * @param string $response
     * @return array
     */
    protected function parseDeviceInfo(string $response): array
    {
        $info = [];
        
        // Format attendu: ~ID#INFO&SN=1234567890&MODEL=T60&VERSION=1.0.0&...
        $parts = explode('&', $response);
        
        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                list($key, $value) = explode('=', $part, 2);
                $info[strtolower($key)] = $value;
            }
        }
        
        return [
            'device_name' => $info['name'] ?? 'Unknown',
            'model' => $info['model'] ?? 'Unknown',
            'serial_number' => $info['sn'] ?? 'Unknown',
            'firmware_version' => $info['version'] ?? 'Unknown',
            'user_count' => $info['users'] ?? '0',
            'record_count' => $info['records'] ?? '0'
        ];
    }
    
    /**
     * Récupère la liste des utilisateurs enregistrés sur l'appareil
     *
     * @return array|null
     */
    public function getUsers(): ?array
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative de récupération des utilisateurs sans connexion");
            return null;
        }
        
        $this->logInfo("Récupération des utilisateurs de l'appareil Anviz");
        
        $response = $this->sendCommand('GETUSER');
        
        if (!$response) {
            return null;
        }
        
        $users = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || strpos($line, '~') === 0) {
                continue;
            }
            
            // Format attendu: ID=1234&NAME=John Doe&PRIV=0&...
            $userData = [];
            $parts = explode('&', $line);
            
            foreach ($parts as $part) {
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                    $userData[strtolower($key)] = $value;
                }
            }
            
            if (isset($userData['id'])) {
                $users[] = [
                    'id' => $userData['id'],
                    'name' => $userData['name'] ?? '',
                    'privilege' => $userData['priv'] ?? '0',
                    'card' => $userData['card'] ?? '',
                    'password' => $userData['pwd'] ?? ''
                ];
            }
        }
        
        $this->logInfo("Récupération réussie de " . count($users) . " utilisateurs");
        
        return $users;
    }
    
    /**
     * Enregistre un utilisateur sur l'appareil
     *
     * @param User $user
     * @param array $options
     * @return bool
     */
    public function registerUser(User $user, array $options = []): bool
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative d'enregistrement d'utilisateur sans connexion");
            return false;
        }
        
        $this->logInfo("Enregistrement de l'utilisateur sur l'appareil Anviz", [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
        
        // Préparer les paramètres
        $params = [
            'ID' => $user->id,
            'NAME' => $user->name,
            'PRIV' => $options['privilege'] ?? '0',
            'PWD' => $options['password'] ?? '',
            'CARD' => $options['card'] ?? ''
        ];
        
        $response = $this->sendCommand('SETUSER', $params);
        
        if (!$response || strpos($response, 'OK') === false) {
            $this->logError("Échec d'enregistrement de l'utilisateur", [
                'user_id' => $user->id,
                'response' => $response
            ]);
            return false;
        }
        
        $this->logInfo("Enregistrement réussi de l'utilisateur", [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
        
        return true;
    }
    
    /**
     * Supprime un utilisateur de l'appareil
     *
     * @param string $userId
     * @return bool
     */
    public function deleteUser(string $userId): bool
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative de suppression d'utilisateur sans connexion");
            return false;
        }
        
        $this->logInfo("Suppression de l'utilisateur de l'appareil Anviz", [
            'user_id' => $userId
        ]);
        
        $response = $this->sendCommand('DELUSER', ['ID' => $userId]);
        
        if (!$response || strpos($response, 'OK') === false) {
            $this->logError("Échec de suppression de l'utilisateur", [
                'user_id' => $userId,
                'response' => $response
            ]);
            return false;
        }
        
        $this->logInfo("Suppression réussie de l'utilisateur", [
            'user_id' => $userId
        ]);
        
        return true;
    }
    
    /**
     * Récupère les logs de présence depuis une date donnée
     *
     * @param string|null $fromDate
     * @return array|null
     */
    public function getLogs(?string $fromDate = null): ?array
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative de récupération des logs sans connexion");
            return null;
        }
        
        $this->logInfo("Récupération des logs de présence de l'appareil Anviz", [
            'from_date' => $fromDate
        ]);
        
        // Paramètres de date
        $params = [];
        
        if ($fromDate) {
            $date = Carbon::parse($fromDate);
            $params['DATE'] = $date->format('Ymd');
            $params['TIME'] = $date->format('His');
        }
        
        $response = $this->sendCommand('GETLOG', $params);
        
        if (!$response) {
            return null;
        }
        
        $logs = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line) || strpos($line, '~') === 0) {
                continue;
            }
            
            // Format attendu: ID=1234&TIME=20250304083000&STATUS=0&VERIFY=1
            $logData = [];
            $parts = explode('&', $line);
            
            foreach ($parts as $part) {
                if (strpos($part, '=') !== false) {
                    list($key, $value) = explode('=', $part, 2);
                    $logData[strtolower($key)] = $value;
                }
            }
            
            if (isset($logData['id']) && isset($logData['time'])) {
                // Convertir le format de date (AAAAMMJJHHMMSS)
                $dateTime = Carbon::createFromFormat('YmdHis', $logData['time'])->format('Y-m-d H:i:s');
                
                $logs[] = [
                    'user_id' => $logData['id'],
                    'date_heure' => $dateTime,
                    'type' => $this->mapStatusToType($logData['status'] ?? '0'),
                    'device_id' => $this->appareil->id,
                    'device_name' => $this->deviceInfo['device_name'] ?? '',
                    'status' => 'success',
                    'raw_data' => json_encode([
                        'status' => $logData['status'] ?? '0',
                        'verify' => $logData['verify'] ?? '0'
                    ])
                ];
            }
        }
        
        $this->logInfo("Récupération réussie de " . count($logs) . " logs");
        
        return $logs;
    }
    
    /**
     * Mappe le statut Anviz au type de pointage
     *
     * @param string $status
     * @return string
     */
    protected function mapStatusToType(string $status): string
    {
        switch ($status) {
            case '0': // Check-in
                return 'entree';
            case '1': // Check-out
                return 'sortie';
            case '2': // Break-out
                return 'pause_debut';
            case '3': // Break-in
                return 'pause_fin';
            default:
                return 'entree';
        }
    }
    
    /**
     * Synchronise l'heure de l'appareil
     *
     * @return bool
     */
    public function syncTime(): bool
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative de synchronisation de l'heure sans connexion");
            return false;
        }
        
        $this->logInfo("Synchronisation de l'heure de l'appareil Anviz");
        
        $now = Carbon::now();
        
        $params = [
            'DATE' => $now->format('Ymd'),
            'TIME' => $now->format('His')
        ];
        
        $response = $this->sendCommand('SETTIME', $params);
        
        if (!$response || strpos($response, 'OK') === false) {
            $this->logError("Échec de synchronisation de l'heure", [
                'response' => $response
            ]);
            return false;
        }
        
        $this->logInfo("Synchronisation réussie de l'heure");
        
        return true;
    }
    
    /**
     * Redémarre l'appareil
     *
     * @return bool
     */
    public function reboot(): bool
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative de redémarrage sans connexion");
            return false;
        }
        
        $this->logInfo("Redémarrage de l'appareil Anviz");
        
        $response = $this->sendCommand('REBOOT');
        
        if (!$response || strpos($response, 'OK') === false) {
            $this->logError("Échec de redémarrage", [
                'response' => $response
            ]);
            return false;
        }
        
        $this->logInfo("Redémarrage réussi");
        
        // Déconnecter après le redémarrage
        $this->disconnect();
        
        return true;
    }
    
    /**
     * Efface tous les logs de l'appareil
     *
     * @return bool
     */
    public function clearLogs(): bool
    {
        if (!$this->isConnected()) {
            $this->logError("Tentative de suppression des logs sans connexion");
            return false;
        }
        
        $this->logInfo("Suppression des logs de l'appareil Anviz");
        
        $response = $this->sendCommand('CLEARLOG');
        
        if (!$response || strpos($response, 'OK') === false) {
            $this->logError("Échec de suppression des logs", [
                'response' => $response
            ]);
            return false;
        }
        
        $this->logInfo("Suppression réussie des logs");
        
        return true;
    }
}
