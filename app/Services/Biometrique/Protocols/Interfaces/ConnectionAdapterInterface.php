<?php

namespace App\Services\Biometrique\Protocols\Interfaces;

/**
 * Interface pour les adaptateurs de connexion
 */
interface ConnectionAdapterInterface
{
    /**
     * Établit une connexion
     *
     * @return bool
     */
    public function connect(): bool;
    
    /**
     * Ferme la connexion
     *
     * @return bool
     */
    public function disconnect(): bool;
    
    /**
     * Vérifie si la connexion est établie
     *
     * @return bool
     */
    public function isConnected(): bool;
    
    /**
     * Envoie des données
     *
     * @param string $data
     * @return bool
     */
    public function send(string $data): bool;
    
    /**
     * Reçoit des données
     *
     * @param int $length
     * @param bool $waitForComplete
     * @return string|null
     */
    public function receive(int $length = 1024, bool $waitForComplete = false): ?string;
    
    /**
     * Récupère la dernière erreur
     *
     * @return string
     */
    public function getLastError(): string;
}
