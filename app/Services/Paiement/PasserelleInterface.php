<?php

namespace App\Services\Paiement;

use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;

interface PasserelleInterface
{
    /**
     * Initialiser un paiement
     *
     * @param Facturation $facturation
     * @param User $initiateur
     * @param array $options
     * @return array
     */
    public function initialiser(Facturation $facturation, User $initiateur, array $options = []): array;
    
    /**
     * Vérifier le statut d'un paiement
     *
     * @param Paiement $paiement
     * @return array
     */
    public function verifierStatut(Paiement $paiement): array;
    
    /**
     * Traiter la réponse de la passerelle (callback)
     *
     * @param array $donnees
     * @return array
     */
    public function traiterReponse(array $donnees): array;
    
    /**
     * Annuler un paiement
     *
     * @param Paiement $paiement
     * @param string|null $raison
     * @return bool
     */
    public function annuler(Paiement $paiement, ?string $raison = null): bool;
    
    /**
     * Rembourser un paiement
     *
     * @param Paiement $paiement
     * @param float|null $montant
     * @param string|null $raison
     * @return bool
     */
    public function rembourser(Paiement $paiement, ?float $montant = null, ?string $raison = null): bool;
}
