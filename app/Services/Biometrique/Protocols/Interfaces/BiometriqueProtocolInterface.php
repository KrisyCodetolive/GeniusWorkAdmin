<?php

namespace App\Services\Biometrique\Protocols\Interfaces;

/**
 * Interface pour les protocoles de communication avec les appareils biométriques
 */
interface BiometriqueProtocolInterface
{
    /**
     * Établit une connexion avec l'appareil
     *
     * @return bool
     */
    public function connect(): bool;
    
    /**
     * Ferme la connexion avec l'appareil
     *
     * @return bool
     */
    public function disconnect(): bool;
    
    /**
     * Vérifie si l'appareil est connecté
     *
     * @return bool
     */
    public function isConnected(): bool;
    
    /**
     * Récupère les informations de l'appareil
     *
     * @return array|null
     */
    public function getDeviceInfo(): ?array;
    
    /**
     * Récupère les logs de présence de l'appareil
     *
     * @param int|null $limit
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getAttendanceLogs(?int $limit = null, ?string $startDate = null, ?string $endDate = null): array;
    
    /**
     * Enregistre un utilisateur sur l'appareil
     *
     * @param string $userId
     * @param string $userName
     * @param string $dataType
     * @param array $biometricData
     * @return bool
     */
    public function registerUser(string $userId, string $userName, string $dataType, array $biometricData = []): bool;
    
    /**
     * Supprime un utilisateur de l'appareil
     *
     * @param string $userId
     * @return bool
     */
    public function deleteUser(string $userId): bool;
    
    /**
     * Récupère la liste des utilisateurs enregistrés sur l'appareil
     *
     * @return array
     */
    public function getUsers(): array;
    
    /**
     * Redémarre l'appareil
     *
     * @return bool
     */
    public function rebootDevice(): bool;
    
    /**
     * Efface tous les logs de l'appareil
     *
     * @return bool
     */
    public function clearLogs(): bool;
    
    /**
     * Envoie une commande personnalisée à l'appareil
     *
     * @param string $command
     * @param array $params
     * @return mixed
     */
    public function sendCommand(string $command, array $params = []);
}
