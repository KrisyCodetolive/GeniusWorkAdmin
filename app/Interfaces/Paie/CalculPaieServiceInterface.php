<?php

namespace App\Interfaces\Paie;

use App\Models\Employeur;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;

interface CalculPaieServiceInterface
{
    /**
     * Calculer le salaire brut d'un employé
     *
     * @param Employeur $employeur
     * @param ConfigurationPaie $configuration
     * @param array $elementsSupplementaires
     * @return array
     */
    public function calculerSalaireBrut(Employeur $employeur, ConfigurationPaie $configuration, array $elementsSupplementaires = []);

    /**
     * Calculer les retenues salariales
     *
     * @param float $salaireBrut
     * @param ConfigurationPaie $configuration
     * @param array $elementsImposables
     * @return array
     */
    public function calculerRetenuesSalariales(float $salaireBrut, ConfigurationPaie $configuration, array $elementsImposables = []);

    /**
     * Calculer le salaire net
     *
     * @param float $salaireBrut
     * @param float $totalRetenues
     * @return float
     */
    public function calculerSalaireNet(float $salaireBrut, float $totalRetenues);

    /**
     * Calculer les charges patronales
     *
     * @param float $salaireBrut
     * @param ConfigurationPaie $configuration
     * @return array
     */
    public function calculerChargesPatronales(float $salaireBrut, ConfigurationPaie $configuration);

    /**
     * Calculer l'IGR (Impôt Général sur le Revenu)
     *
     * @param float $baseImposable
     * @param ConfigurationPaie $configuration
     * @return float
     */
    public function calculerIGR(float $baseImposable, ConfigurationPaie $configuration);

    /**
     * Calculer la cotisation CNPS employé
     *
     * @param float $salaireBrut
     * @param ConfigurationPaie $configuration
     * @return float
     */
    public function calculerCNPSEmploye(float $salaireBrut, ConfigurationPaie $configuration);

    /**
     * Calculer les indemnités (logement, transport, etc.)
     *
     * @param Employeur $employeur
     * @param ConfigurationPaie $configuration
     * @return array
     */
    public function calculerIndemnites(Employeur $employeur, ConfigurationPaie $configuration);

    /**
     * Calculer les primes (ancienneté, rendement, etc.)
     *
     * @param Employeur $employeur
     * @param ConfigurationPaie $configuration
     * @param array $primesSupplementaires
     * @return array
     */
    public function calculerPrimes(Employeur $employeur, ConfigurationPaie $configuration, array $primesSupplementaires = []);

    /**
     * Générer un bulletin de paie complet
     *
     * @param Employeur $employeur
     * @param ConfigurationPaie $configuration
     * @param array $parametres
     * @return BulletinPaie
     */
    public function genererBulletinPaie(Employeur $employeur, ConfigurationPaie $configuration, array $parametres = []);
}
