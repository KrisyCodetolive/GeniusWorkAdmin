<?php

namespace App\Services\Biometrique\Protocols\Implementations;

use App\Models\AppareilBiometrique;
use App\Models\User;
use App\Services\Biometrique\Protocols\AbstractBiometriqueProtocol;
use App\Services\Biometrique\Protocols\Adapters\HttpAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Implémentation du protocole pour les appareils HikVision
 */
class HikVisionProtocol extends AbstractBiometriqueProtocol
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
     * Constructeur
     *
     * @param AppareilBiometrique $appareil
     */
    public function __construct(AppareilBiometrique $appareil)
    {
        parent::__construct($appareil);
        
        $this->config = $appareil->configuration ?? [];
        
        // Créer l'adaptateur HTTP
        $baseUrl = "http://{$appareil->adresse_ip}";
        if (!empty($appareil->port)) {
            $baseUrl .= ":{$appareil->port}";
        }
        
        $this->adapter = new HttpAdapter($baseUrl);
        
        // Configurer l'authentification si disponible
        if (!empty($this->config['username']) && !empty($this->config['password'])) {
            $this->adapter->setAuth(
                $this->config['username'],
                $this->config['password']
            );
        }
        
        // Configurer les en-têtes par défaut
        $this->adapter->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
    }
    
    /**
     * Établit une connexion avec l'appareil
     *
     * @return bool
     */
    public function connect(): bool
    {
        $this->logInfo("Tentative de connexion à l'appareil HikVision", [
            'adresse_ip' => $this->appareil->adresse_ip,
            'port' => $this->appareil->port
        ]);
        
        $result = $this->adapter->connect();
        
        if (!$result) {
            $this->logError("Échec de connexion à l'appareil HikVision", [
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
        
        $this->logInfo("Connexion réussie à l'appareil HikVision", [
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
            $this->logInfo("Déconnexion réussie de l'appareil HikVision");
        } else {
            $this->logError("Échec de déconnexion de l'appareil HikVision", [
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
        $response = $this->adapter->get('ISAPI/System/deviceInfo');
        
        if (!$response) {
            return null;
        }
        
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            return null;
        }
        
        return [
            'device_name' => (string)$xml->deviceName,
            'model' => (string)$xml->model,
            'serial_number' => (string)$xml->serialNumber,
            'firmware_version' => (string)$xml->firmwareVersion,
            'firmware_release_date' => (string)$xml->firmwareReleasedDate,
            'mac_address' => (string)$xml->macAddress
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
        
        $this->logInfo("Récupération des utilisateurs de l'appareil HikVision");
        
        $response = $this->adapter->get('ISAPI/AccessControl/UserInfo/Search');
        
        if (!$response) {
            $this->logError("Échec de récupération des utilisateurs", [
                'error' => $this->adapter->getLastError()
            ]);
            return null;
        }
        
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            $this->logError("Réponse XML invalide lors de la récupération des utilisateurs");
            return null;
        }
        
        $users = [];
        
        foreach ($xml->UserInfo as $userInfo) {
            $users[] = [
                'id' => (string)$userInfo->employeeNo,
                'name' => (string)$userInfo->name,
                'user_type' => (string)$userInfo->userType,
                'valid_begin_time' => (string)$userInfo->validBeginTime,
                'valid_end_time' => (string)$userInfo->validEndTime
            ];
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
        
        $this->logInfo("Enregistrement de l'utilisateur sur l'appareil HikVision", [
            'user_id' => $user->id,
            'user_name' => $user->name
        ]);
        
        // Préparer les données de l'utilisateur
        $validBeginTime = Carbon::now()->format('Y-m-d\TH:i:s');
        $validEndTime = Carbon::now()->addYears(10)->format('Y-m-d\TH:i:s');
        
        $userData = [
            'UserInfo' => [
                'employeeNo' => $user->id,
                'name' => $user->name,
                'userType' => 'normal',
                'validBeginTime' => $validBeginTime,
                'validEndTime' => $validEndTime,
                'password' => $options['password'] ?? '',
                'doorRight' => 1,
                'RightPlan' => [
                    'doorNo' => 1,
                    'planTemplateNo' => 1
                ]
            ]
        ];
        
        // Convertir en XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><UserInfo></UserInfo>');
        $this->arrayToXml($userData['UserInfo'], $xml);
        $xmlString = $xml->asXML();
        
        // Envoyer la requête
        $this->adapter->addHeader('Content-Type', 'application/xml');
        $response = $this->adapter->post('ISAPI/AccessControl/UserInfo/Record', $xmlString);
        $this->adapter->addHeader('Content-Type', 'application/json');
        
        if (!$response) {
            $this->logError("Échec d'enregistrement de l'utilisateur", [
                'error' => $this->adapter->getLastError(),
                'user_id' => $user->id
            ]);
            return false;
        }
        
        $xml = simplexml_load_string($response);
        
        if (!$xml || (string)$xml->statusCode !== '1') {
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
        
        $this->logInfo("Suppression de l'utilisateur de l'appareil HikVision", [
            'user_id' => $userId
        ]);
        
        $response = $this->adapter->delete("ISAPI/AccessControl/UserInfo/Delete?employeeNo={$userId}");
        
        if (!$response) {
            $this->logError("Échec de suppression de l'utilisateur", [
                'error' => $this->adapter->getLastError(),
                'user_id' => $userId
            ]);
            return false;
        }
        
        $xml = simplexml_load_string($response);
        
        if (!$xml || (string)$xml->statusCode !== '1') {
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
        
        $this->logInfo("Récupération des logs de présence de l'appareil HikVision", [
            'from_date' => $fromDate
        ]);
        
        // Préparer les paramètres de recherche
        $startTime = $fromDate ? Carbon::parse($fromDate)->format('Y-m-d\TH:i:s') : Carbon::now()->subDays(7)->format('Y-m-d\TH:i:s');
        $endTime = Carbon::now()->format('Y-m-d\TH:i:s');
        
        $searchData = [
            'AcsEventCond' => [
                'searchID' => $this->generateTransactionId(),
                'searchResultPosition' => 0,
                'maxResults' => 1000,
                'major' => 5, // Access Control
                'minor' => 75, // Card and Face
                'startTime' => $startTime,
                'endTime' => $endTime
            ]
        ];
        
        // Convertir en XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><AcsEventCond></AcsEventCond>');
        $this->arrayToXml($searchData['AcsEventCond'], $xml);
        $xmlString = $xml->asXML();
        
        // Envoyer la requête
        $this->adapter->addHeader('Content-Type', 'application/xml');
        $response = $this->adapter->post('ISAPI/AccessControl/AcsEvent/Search', $xmlString);
        $this->adapter->addHeader('Content-Type', 'application/json');
        
        if (!$response) {
            $this->logError("Échec de récupération des logs", [
                'error' => $this->adapter->getLastError()
            ]);
            return null;
        }
        
        $xml = simplexml_load_string($response);
        
        if (!$xml) {
            $this->logError("Réponse XML invalide lors de la récupération des logs");
            return null;
        }
        
        $logs = [];
        
        foreach ($xml->AcsEvent as $event) {
            $logs[] = [
                'user_id' => (string)$event->employeeNoString,
                'date_heure' => (string)$event->time,
                'type' => $this->mapEventType((int)$event->minor),
                'device_id' => $this->appareil->id,
                'device_name' => $this->deviceInfo['device_name'] ?? '',
                'status' => 'success',
                'raw_data' => json_encode([
                    'major' => (int)$event->major,
                    'minor' => (int)$event->minor,
                    'card_reader_kind' => (int)$event->cardReaderKind,
                    'card_reader_no' => (int)$event->cardReaderNo,
                    'verify_no' => (int)$event->verifyNo
                ])
            ];
        }
        
        $this->logInfo("Récupération réussie de " . count($logs) . " logs");
        
        return $logs;
    }
    
    /**
     * Mappe le type d'événement HikVision au type de pointage
     *
     * @param int $minor
     * @return string
     */
    protected function mapEventType(int $minor): string
    {
        switch ($minor) {
            case 75: // Card and Face
            case 76: // Card and Fingerprint
            case 201: // Face Recognition
                return 'entree';
            default:
                return 'entree'; // Par défaut, on considère comme une entrée
        }
    }
    
    /**
     * Convertit un tableau en XML
     *
     * @param array $array
     * @param \SimpleXMLElement $xml
     * @return void
     */
    protected function arrayToXml(array $array, \SimpleXMLElement &$xml): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
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
        
        $this->logInfo("Synchronisation de l'heure de l'appareil HikVision");
        
        $currentTime = Carbon::now()->format('Y-m-d\TH:i:s\Z');
        
        $timeData = [
            'Time' => [
                'timeMode' => 'manual',
                'localTime' => $currentTime,
                'timeZone' => 'CST-8:00:00'
            ]
        ];
        
        // Convertir en XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Time></Time>');
        $this->arrayToXml($timeData['Time'], $xml);
        $xmlString = $xml->asXML();
        
        // Envoyer la requête
        $this->adapter->addHeader('Content-Type', 'application/xml');
        $response = $this->adapter->put('ISAPI/System/time', $xmlString);
        $this->adapter->addHeader('Content-Type', 'application/json');
        
        if (!$response) {
            $this->logError("Échec de synchronisation de l'heure", [
                'error' => $this->adapter->getLastError()
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
        
        $this->logInfo("Redémarrage de l'appareil HikVision");
        
        $response = $this->adapter->put('ISAPI/System/reboot', '');
        
        if (!$response) {
            $this->logError("Échec de redémarrage", [
                'error' => $this->adapter->getLastError()
            ]);
            return false;
        }
        
        $this->logInfo("Redémarrage réussi");
        
        // Déconnecter après le redémarrage
        $this->disconnect();
        
        return true;
    }
}
