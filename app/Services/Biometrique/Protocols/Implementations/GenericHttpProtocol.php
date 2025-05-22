<?php

namespace App\Services\Biometrique\Protocols\Implementations;

use App\Models\AppareilBiometrique;
use App\Models\User;
use App\Services\Biometrique\Protocols\AbstractBiometriqueProtocol;
use App\Services\Biometrique\Protocols\Adapters\HttpAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Implémentation du protocole générique HTTP pour les appareils biométriques
 * Ce protocole est conçu pour être utilisé avec des appareils qui exposent une API REST
 */
class GenericHttpProtocol extends AbstractBiometriqueProtocol
{
    /**
     * @var HttpAdapter
     */
    protected $adapter;
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * @var array
     */
    protected $endpoints;
    
    /**
     * Constructeur
     *
     * @param AppareilBiometrique $appareil
     */
    public function __construct(AppareilBiometrique $appareil)
    {
        parent::__construct($appareil);
        
        $this->config = $appareil->configuration ?? [];
        
        // Récupérer les endpoints configurés
        $this->endpoints = $this->config['endpoints'] ?? [
            'info' => 'api/device/info',
            'users' => 'api/users',
            'user' => 'api/users/{id}',
            'logs' => 'api/logs',
            'sync_time' => 'api/device/sync-time',
            'reboot' => 'api/device/reboot'
        ];
        
        // Créer l'adaptateur HTTP
        $protocol = $this->config['use_https'] ?? false ? 'https' : 'http';
        $baseUrl = "{$protocol}://{$appareil->adresse_ip}";
        
        if (!empty($appareil->port)) {
            $baseUrl .= ":{$appareil->port}";
        }
        
        $this->adapter = new HttpAdapter($baseUrl, $this->config['timeout'] ?? 10);
        
        // Configurer l'authentification si disponible
        if (!empty($this->config['username']) && !empty($this->config['password'])) {
            $this->adapter->setAuth(
                $this->config['username'],
                $this->config['password']
            );
        }
        
        // Configurer les en-têtes par défaut
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        
        // Ajouter les en-têtes personnalisés
        if (!empty($this->config['headers']) && is_array($this->config['headers'])) {
            $headers = array_merge($headers, $this->config['headers']);
        }
        
        // Ajouter un token d'API si disponible
        if (!empty($this->config['api_token'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api_token'];
        }
        
        $this->adapter->setHeaders($headers);
    }
    
    /**
     * Établit une connexion avec l'appareil
     *
     * @return bool
     */
    public function connect(): bool
    {
        $this->logInfo("Tentative de connexion à l'appareil via HTTP", [
            'adresse_ip' => $this->appareil->adresse_ip,
            'port' => $this->appareil->port
        ]);
        
        $result = $this->adapter->connect();
        
        if (!$result) {
            $this->logError("Échec de connexion à l'appareil via HTTP", [
                'error' => $this->adapter->getLastError()
            ]);
            return false;
        }
        
        // Récupérer les informations de l'appareil pour confirmer la connexion
        $deviceInfo = $this->fetchDeviceInfo();
        
        if (!$deviceInfo) {
            $this->logError("Connexion établie mais impossible de récupérer les informations de l'appareil");
            $this->adapter->disconnect();
            return false;
        }
        
        $this->connected = true;
        $this->deviceInfo = $deviceInfo;
        
        $this->logInfo("Connexion réussie à l'appareil via HTTP", [
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
            $this->logInfo("Déconnexion réussie de l'appareil via HTTP");
        } else {
            $this->logError("Échec de déconnexion de l'appareil via HTTP", [
                'error' => $this->adapter->getLastError()
            ]);
        }
        
        return $result;
    }
    
    /**
     * Récupère les informations de l'appareil
     *
     * @return array|null
     */
    protected function fetchDeviceInfo(): ?array
    {
        $response = $this->adapter->get($this->endpoints['info']);
        
        if (!$response) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
            return null;
        }
        
        // Mapper les champs selon la configuration
        $mapping = $this->config['info_mapping'] ?? [
            'device_name' => 'name',
            'model' => 'model',
            'serial_number' => 'serial',
            'firmware_version' => 'firmware',
            'user_count' => 'users',
            'record_count' => 'records'
        ];
        
        $info = [];
        
        foreach ($mapping as $key => $field) {
            $info[$key] = $data[$field] ?? null;
        }
        
        return $info;
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
        
        $this->logInfo("Récupération des utilisateurs de l'appareil via HTTP");
        
        $response = $this->adapter->get($this->endpoints['users']);
        
        if (!$response) {
            $this->logError("Échec de récupération des utilisateurs", [
                'error' => $this->adapter->getLastError()
            ]);
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
            $this->logError("Réponse invalide lors de la récupération des utilisateurs", [
                'response' => $response
            ]);
            return null;
        }
        
        // Récupérer la liste des utilisateurs
        $usersData = $data['data'] ?? $data['users'] ?? $data;
        
        if (!is_array($usersData)) {
            $this->logError("Format de données utilisateur invalide");
            return null;
        }
        
        // Mapper les champs selon la configuration
        $mapping = $this->config['user_mapping'] ?? [
            'id' => 'id',
            'name' => 'name',
            'privilege' => 'privilege',
            'card' => 'card',
            'password' => 'password'
        ];
        
        $users = [];
        
        foreach ($usersData as $userData) {
            $user = [];
            
            foreach ($mapping as $key => $field) {
                $user[$key] = $userData[$field] ?? null;
            }
            
            if (!empty($user['id'])) {
                $users[] = $user;
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
        
        $this->logInfo("Enregistrement de l'utilisateur sur l'appareil via HTTP", [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
        
        // Mapper les champs selon la configuration
        $mapping = $this->config['user_mapping'] ?? [
            'id' => 'id',
            'name' => 'name',
            'privilege' => 'privilege',
            'card' => 'card',
            'password' => 'password'
        ];
        
        // Préparer les données de l'utilisateur
        $userData = [];
        
        foreach ($mapping as $key => $field) {
            switch ($key) {
                case 'id':
                    $userData[$field] = $user->id;
                    break;
                case 'name':
                    $userData[$field] = $user->name;
                    break;
                case 'privilege':
                    $userData[$field] = $options['privilege'] ?? 0;
                    break;
                case 'card':
                    $userData[$field] = $options['card'] ?? '';
                    break;
                case 'password':
                    $userData[$field] = $options['password'] ?? '';
                    break;
                default:
                    if (isset($options[$key])) {
                        $userData[$field] = $options[$key];
                    }
            }
        }
        
        // Ajouter des champs supplémentaires si nécessaire
        if (!empty($this->config['user_extra_fields']) && is_array($this->config['user_extra_fields'])) {
            foreach ($this->config['user_extra_fields'] as $field => $value) {
                $userData[$field] = $value;
            }
        }
        
        // Envoyer la requête
        $response = $this->adapter->post($this->endpoints['users'], $userData);
        
        if (!$response) {
            $this->logError("Échec d'enregistrement de l'utilisateur", [
                'error' => $this->adapter->getLastError(),
                'user_id' => $user->id
            ]);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
            $this->logError("Échec d'enregistrement de l'utilisateur", [
                'response' => $response,
                'user_id' => $user->id
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
        
        $this->logInfo("Suppression de l'utilisateur de l'appareil via HTTP", [
            'user_id' => $userId
        ]);
        
        // Remplacer {id} dans l'endpoint
        $endpoint = str_replace('{id}', $userId, $this->endpoints['user']);
        
        // Envoyer la requête
        $response = $this->adapter->delete($endpoint);
        
        if (!$response) {
            $this->logError("Échec de suppression de l'utilisateur", [
                'error' => $this->adapter->getLastError(),
                'user_id' => $userId
            ]);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
            $this->logError("Échec de suppression de l'utilisateur", [
                'response' => $response,
                'user_id' => $userId
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
        
        $this->logInfo("Récupération des logs de présence de l'appareil via HTTP", [
            'from_date' => $fromDate
        ]);
        
        // Préparer les paramètres
        $params = [];
        
        if ($fromDate) {
            $params['from_date'] = $this->formatDate($fromDate);
        }
        
        // Ajouter des paramètres supplémentaires si nécessaire
        if (!empty($this->config['logs_extra_params']) && is_array($this->config['logs_extra_params'])) {
            $params = array_merge($params, $this->config['logs_extra_params']);
        }
        
        // Envoyer la requête
        $response = $this->adapter->get($this->endpoints['logs'], $params);
        
        if (!$response) {
            $this->logError("Échec de récupération des logs", [
                'error' => $this->adapter->getLastError()
            ]);
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
            $this->logError("Réponse invalide lors de la récupération des logs", [
                'response' => $response
            ]);
            return null;
        }
        
        // Récupérer la liste des logs
        $logsData = $data['data'] ?? $data['logs'] ?? $data;
        
        if (!is_array($logsData)) {
            $this->logError("Format de données de log invalide");
            return null;
        }
        
        // Mapper les champs selon la configuration
        $mapping = $this->config['log_mapping'] ?? [
            'user_id' => 'user_id',
            'date_heure' => 'timestamp',
            'type' => 'type',
            'status' => 'status',
            'raw_data' => 'raw_data'
        ];
        
        $logs = [];
        
        foreach ($logsData as $logData) {
            $log = [
                'device_id' => $this->appareil->id,
                'device_name' => $this->deviceInfo['device_name'] ?? ''
            ];
            
            foreach ($mapping as $key => $field) {
                if ($key === 'date_heure' && isset($logData[$field])) {
                    // Convertir le format de date si nécessaire
                    $log[$key] = $this->formatDate($logData[$field]);
                } elseif ($key === 'type' && isset($logData[$field])) {
                    // Mapper le type selon la configuration
                    $log[$key] = $this->mapLogType($logData[$field]);
                } elseif ($key === 'raw_data' && isset($logData[$field])) {
                    // Convertir en JSON si ce n'est pas déjà le cas
                    $log[$key] = is_string($logData[$field]) ? $logData[$field] : json_encode($logData[$field]);
                } else {
                    $log[$key] = $logData[$field] ?? null;
                }
            }
            
            if (!empty($log['user_id']) && !empty($log['date_heure'])) {
                $logs[] = $log;
            }
        }
        
        $this->logInfo("Récupération réussie de " . count($logs) . " logs");
        
        return $logs;
    }
    
    /**
     * Mappe le type de log selon la configuration
     *
     * @param string|int $type
     * @return string
     */
    protected function mapLogType($type): string
    {
        $typeMapping = $this->config['type_mapping'] ?? [
            '0' => 'entree',
            '1' => 'sortie',
            '2' => 'pause_debut',
            '3' => 'pause_fin',
            'in' => 'entree',
            'out' => 'sortie',
            'break_out' => 'pause_debut',
            'break_in' => 'pause_fin'
        ];
        
        return $typeMapping[$type] ?? 'entree';
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
        
        $this->logInfo("Synchronisation de l'heure de l'appareil via HTTP");
        
        // Préparer les données
        $timeData = [
            'timestamp' => Carbon::now()->timestamp,
            'datetime' => Carbon::now()->format('Y-m-d H:i:s')
        ];
        
        // Ajouter des champs supplémentaires si nécessaire
        if (!empty($this->config['time_extra_fields']) && is_array($this->config['time_extra_fields'])) {
            $timeData = array_merge($timeData, $this->config['time_extra_fields']);
        }
        
        // Envoyer la requête
        $response = $this->adapter->post($this->endpoints['sync_time'], $timeData);
        
        if (!$response) {
            $this->logError("Échec de synchronisation de l'heure", [
                'error' => $this->adapter->getLastError()
            ]);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
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
        
        $this->logInfo("Redémarrage de l'appareil via HTTP");
        
        // Envoyer la requête
        $response = $this->adapter->post($this->endpoints['reboot'], []);
        
        if (!$response) {
            $this->logError("Échec de redémarrage", [
                'error' => $this->adapter->getLastError()
            ]);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $this->hasError($data)) {
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
}
