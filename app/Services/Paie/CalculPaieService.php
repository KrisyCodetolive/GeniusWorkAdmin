<?php

namespace App\Services\Paie;

use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ConfigurationPaie;
use App\Models\Paie\ElementPaie;
use App\Repositories\Paie\BulletinPaieRepository;
use Illuminate\Support\Facades\Log;

class CalculPaieService implements CalculPaieServiceInterface
{
    /**
     * @var BulletinPaieRepository
     */
    protected $bulletinRepository;

    /**
     * Constructeur
     *
     * @param BulletinPaieRepository $bulletinRepository
     */
    public function __construct(BulletinPaieRepository $bulletinRepository)
    {
        $this->bulletinRepository = $bulletinRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function calculerSalaireBrut(Employeur $employeur, ConfigurationPaie $configuration, array $elementsSupplementaires = [])
    {
        // Salaire de base
        $salaireBase = $employeur->salaire_base ?? $configuration->smig;

        // Calculer les indemnités
        $indemnites = $this->calculerIndemnites($employeur, $configuration);
        $totalIndemnites = array_sum(array_column($indemnites, 'montant'));

        // Calculer les primes
        $primes = $this->calculerPrimes($employeur, $configuration, $elementsSupplementaires['primes'] ?? []);
        $totalPrimes = array_sum(array_column($primes, 'montant'));

        // Autres éléments supplémentaires
        $autresElements = $elementsSupplementaires['autres'] ?? [];
        $totalAutresElements = array_sum(array_column($autresElements, 'montant'));

        // Calculer le salaire brut
        $salaireBrut = $salaireBase + $totalIndemnites + $totalPrimes + $totalAutresElements;

        return [
            'salaire_base' => $salaireBase,
            'indemnites' => $indemnites,
            'total_indemnites' => $totalIndemnites,
            'primes' => $primes,
            'total_primes' => $totalPrimes,
            'autres_elements' => $autresElements,
            'total_autres_elements' => $totalAutresElements,
            'salaire_brut' => $salaireBrut
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function calculerRetenuesSalariales(float $salaireBrut, ConfigurationPaie $configuration, array $elementsImposables = [])
    {
        // Calculer la cotisation CNPS employé
        $cnpsEmploye = $this->calculerCNPSEmploye($salaireBrut, $configuration);

        // Calculer la base imposable pour l'IGR
        $baseImposable = $salaireBrut;
        
        // Ajouter les éléments imposables supplémentaires
        foreach ($elementsImposables as $element) {
            $baseImposable += $element['montant'] ?? 0;
        }

        // Calculer l'IGR
        $igr = $this->calculerIGR($baseImposable, $configuration);

        // Autres retenues éventuelles
        $autresRetenues = 0;

        // Total des retenues
        $totalRetenues = $cnpsEmploye + $igr + $autresRetenues;

        return [
            'cnps_employe' => $cnpsEmploye,
            'base_imposable' => $baseImposable,
            'igr' => $igr,
            'autres_retenues' => $autresRetenues,
            'total_retenues' => $totalRetenues
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function calculerSalaireNet(float $salaireBrut, float $totalRetenues)
    {
        return $salaireBrut - $totalRetenues;
    }

    /**
     * {@inheritDoc}
     */
    public function calculerChargesPatronales(float $salaireBrut, ConfigurationPaie $configuration)
    {
        $assiette = min($salaireBrut, $configuration->plafond_cnps);
        
        $cnpsEmployeur = $assiette * ($configuration->taux_cnps_employeur / 100);
        $prestationsFamiliales = $assiette * ($configuration->taux_prestations_familiales / 100);
        $accidentTravail = $assiette * ($configuration->taux_accident_travail / 100);
        $assuranceMaladie = $assiette * ($configuration->taux_assurance_maladie / 100);
        
        $totalCharges = $cnpsEmployeur + $prestationsFamiliales + $accidentTravail + $assuranceMaladie;
        
        return [
            'cnps_employeur' => $cnpsEmployeur,
            'prestations_familiales' => $prestationsFamiliales,
            'accident_travail' => $accidentTravail,
            'assurance_maladie' => $assuranceMaladie,
            'total_charges' => $totalCharges
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function calculerIGR(float $baseImposable, ConfigurationPaie $configuration)
    {
        // Appliquer l'abattement forfaitaire
        $montantAbattement = $baseImposable * ($configuration->abattement_igr / 100);
        $montantApresAbattement = $baseImposable - $montantAbattement;
        
        // Trouver le barème applicable
        $bareme = $configuration->getBaremeIGR($montantApresAbattement);
        
        if (!$bareme) {
            return 0;
        }
        
        $tauxIGR = $bareme['taux'] ?? 0;
        
        // Calculer l'IGR
        return $montantApresAbattement * ($tauxIGR / 100);
    }

    /**
     * {@inheritDoc}
     */
    public function calculerCNPSEmploye(float $salaireBrut, ConfigurationPaie $configuration)
    {
        $assiette = min($salaireBrut, $configuration->plafond_cnps);
        return $assiette * ($configuration->taux_cnps_employe / 100);
    }
    
    /**
     * {@inheritDoc}
     */
    public function calculerIndemnites(Employeur $employeur, ConfigurationPaie $configuration, array $indemnitesSupplementaires = [])
    {
        $indemnites = [];
        $salaireBase = $employeur->salaire_base ?? $configuration->smig;
        
        // Récupérer les paramètres d'indemnités
        $parametresIndemnites = $configuration->parametres_indemnites ?? [];
        
        // Ajouter un log pour déboguer
        Log::info('Paramètres d\'indemnités', $parametresIndemnites);
        
        // Traitement des indemnités selon le format du tableau
        if (is_array($parametresIndemnites)) {
            $ordre = 10;
            
            // Vérifier si c'est un tableau indexé numériquement (format [{}])
            if (array_keys($parametresIndemnites) === range(0, count($parametresIndemnites) - 1)) {
                // Format de tableau indexé numériquement - nouvelle structure
                foreach ($parametresIndemnites as $indemnite) {
                    $montant = 0;
                    $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $indemnite['nom']), 0, 2));
                    
                    // Déterminer la catégorie d'indemnité
                    $categorie = ElementPaie::CATEGORIE_INDEMNITE_AUTRE;
                    if (stripos($indemnite['nom'], 'logement') !== false) {
                        $categorie = ElementPaie::CATEGORIE_INDEMNITE_LOGEMENT;
                        $code = 'IL';
                    } elseif (stripos($indemnite['nom'], 'transport') !== false) {
                        $categorie = ElementPaie::CATEGORIE_INDEMNITE_TRANSPORT;
                        $code = 'IT';
                    }
                    
                    // Calculer le montant selon le type
                    if ($indemnite['type'] === 'pourcentage' && isset($indemnite['taux'])) {
                        $montant = $salaireBase * ($indemnite['taux'] / 100);
                    } elseif (isset($indemnite['montant'])) {
                        $montant = $indemnite['montant'];
                    }
                    
                    // Ajouter l'indemnité au tableau des indemnités
                    $indemnites[] = [
                        'code' => $code,
                        'libelle' => $indemnite['nom'],
                        'type' => ElementPaie::TYPE_INDEMNITE,
                        'categorie' => $categorie,
                        'base' => $salaireBase,
                        'taux' => $indemnite['type'] === 'pourcentage' ? $indemnite['taux'] : null,
                        'montant' => $montant,
                        'imposable' => $indemnite['imposable'] ?? false,
                        'ordre' => $ordre
                    ];
                    
                    $ordre += 10;
                }
            } else {
                // Format de tableau associatif - ancienne structure
                // Indemnité de logement
                if (isset($parametresIndemnites['indemnite_logement'])) {
                    $paramLogement = $parametresIndemnites['indemnite_logement'];
                    $montant = 0;
                    
                    if ($paramLogement['type'] === 'pourcentage') {
                        $montant = $salaireBase * ($paramLogement['taux'] / 100);
                    } else {
                        $montant = $paramLogement['montant'] ?? 0;
                    }
                    
                    $indemnites[] = [
                        'code' => 'IL',
                        'libelle' => 'Indemnité de logement',
                        'type' => ElementPaie::TYPE_INDEMNITE,
                        'categorie' => ElementPaie::CATEGORIE_INDEMNITE_LOGEMENT,
                        'base' => $salaireBase,
                        'taux' => $paramLogement['taux'] ?? null,
                        'montant' => $montant,
                        'imposable' => $paramLogement['imposable'] ?? false,
                        'ordre' => 10
                    ];
                }
                
                // Indemnité de transport
                if (isset($parametresIndemnites['indemnite_transport'])) {
                    $paramTransport = $parametresIndemnites['indemnite_transport'];
                    $montant = 0;
                    
                    if ($paramTransport['type'] === 'pourcentage') {
                        $montant = $salaireBase * ($paramTransport['taux'] / 100);
                    } else {
                        $montant = $paramTransport['montant'] ?? 0;
                    }
                    
                    $indemnites[] = [
                        'code' => 'IT',
                        'libelle' => 'Indemnité de transport',
                        'type' => ElementPaie::TYPE_INDEMNITE,
                        'categorie' => ElementPaie::CATEGORIE_INDEMNITE_TRANSPORT,
                        'base' => $salaireBase,
                        'taux' => $paramTransport['taux'] ?? null,
                        'montant' => $montant,
                        'imposable' => $paramTransport['imposable'] ?? false,
                        'ordre' => 20
                    ];
                }
            }
        }
        
        // Autres indemnités personnalisées (à partir des meta_donnees de l'employeur)
        $indemnitesPersonnalisees = $employeur->getMeta('indemnites_personnalisees') ?? [];
        
        foreach ($indemnitesPersonnalisees as $index => $indemnite) {
            if (isset($indemnite['libelle']) && isset($indemnite['montant'])) {
                $indemnites[] = [
                    'code' => 'IP' . ($index + 1),
                    'libelle' => $indemnite['libelle'],
                    'type' => ElementPaie::TYPE_INDEMNITE,
                    'categorie' => 'indemnite_personnalisee',
                    'base' => null,
                    'taux' => null,
                    'montant' => $indemnite['montant'],
                    'imposable' => $indemnite['imposable'] ?? false,
                    'ordre' => 30 + $index
                ];
            }
        }
        
        return $indemnites;
    }
    
    /**
     * {@inheritDoc}
     */
    public function calculerPrimes(Employeur $employeur, ConfigurationPaie $configuration, array $primesSupplementaires = [])
    {
        $salaireBase = $employeur->salaire_base ?? $configuration->smig;
        $primes = [];
        
        // Récupérer les paramètres de primes
        $parametresPrimes = $configuration->parametres_primes ?? [];
        
        Log::info('Paramètres de primes', $parametresPrimes);
        
        // Traitement des primes selon le format du tableau
        if (is_array($parametresPrimes)) {
            $ordre = 10;
            
            // Vérifier si c'est un tableau indexé numériquement (format [{}])
            if (array_keys($parametresPrimes) === range(0, count($parametresPrimes) - 1)) {
                // Format de tableau indexé numériquement - nouvelle structure
                foreach ($parametresPrimes as $prime) {
                    $montant = 0;
                    $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $prime['nom']), 0, 2));
                    
                    // Déterminer la catégorie de prime
                    $categorie = ElementPaie::CATEGORIE_PRIME_AUTRE;
                    if (stripos($prime['nom'], 'ancienneté') !== false) {
                        $categorie = ElementPaie::CATEGORIE_PRIME_ANCIENNETE;
                        $code = 'PA';
                        
                        // Pour la prime d'ancienneté, vérifier l'ancienneté de l'employé
                        $anciennete = $employeur->getAnciennete() ?? 0;
                        if ($anciennete < 2) { // Par exemple, nécessite 2 ans d'ancienneté
                            continue; // Passer cette prime si l'employé n'a pas assez d'ancienneté
                        }
                    } elseif (stripos($prime['nom'], 'rendement') !== false) {
                        $categorie = ElementPaie::CATEGORIE_PRIME_RENDEMENT;
                        $code = 'PR';
                    }
                    
                    // Calculer le montant selon le type
                    if ($prime['type'] === 'pourcentage' && isset($prime['taux'])) {
                        $montant = $salaireBase * ($prime['taux'] / 100);
                    } elseif (isset($prime['montant'])) {
                        $montant = $prime['montant'];
                    }
                    
                    // Ajouter la prime au tableau des primes
                    $primes[] = [
                        'code' => $code,
                        'libelle' => $prime['nom'],
                        'type' => ElementPaie::TYPE_PRIME,
                        'categorie' => $categorie,
                        'base' => $salaireBase,
                        'taux' => $prime['type'] === 'pourcentage' ? $prime['taux'] : null,
                        'montant' => $montant,
                        'imposable' => $prime['imposable'] ?? true,
                        'ordre' => $ordre
                    ];
                    
                    $ordre += 10;
                }
            } else {
                // Format de tableau associatif - ancienne structure
                // Prime d'ancienneté
                if (isset($parametresPrimes['prime_anciennete'])) {
                    $paramAnciennete = $parametresPrimes['prime_anciennete'];
                    $montant = 0;
                    
                    // Calculer l'ancienneté en années
                    $anciennete = $employeur->getAnciennete() ?? 0;
                    
                    // Déterminer le taux applicable selon l'ancienneté
                    $tauxApplicable = null;
                    
                    if ($paramAnciennete['type'] === 'pourcentage' && isset($paramAnciennete['taux'])) {
                        if (is_array($paramAnciennete['taux'])) {
                            // Trouver le taux applicable selon l'ancienneté
                            $tauxTrouve = null;
                            $paliers = array_keys($paramAnciennete['taux']);
                            sort($paliers, SORT_NUMERIC);
                            
                            foreach ($paliers as $palier) {
                                if ($anciennete >= (int)$palier) {
                                    $tauxTrouve = $paramAnciennete['taux'][$palier];
                                } else {
                                    break;
                                }
                            }
                            
                            if ($tauxTrouve !== null) {
                                $tauxApplicable = $tauxTrouve;
                            }
                        } else {
                            // Taux simple
                            $tauxApplicable = $paramAnciennete['taux'];
                        }
                        
                        if ($tauxApplicable !== null) {
                            $montant = $salaireBase * ($tauxApplicable / 100);
                        }
                    }
                    
                    // Ajouter la prime d'ancienneté si applicable
                    if ($tauxApplicable !== null) {
                        $primes[] = [
                            'code' => 'PA',
                            'libelle' => 'Prime d\'ancienneté',
                            'type' => ElementPaie::TYPE_PRIME,
                            'categorie' => ElementPaie::CATEGORIE_PRIME_ANCIENNETE,
                            'base' => $salaireBase,
                            'taux' => $tauxApplicable,
                            'montant' => $montant,
                            'imposable' => $paramAnciennete['imposable'] ?? true,
                            'ordre' => 10
                        ];
                    }
                }
                
                // Prime de rendement
                if (isset($parametresPrimes['prime_rendement'])) {
                    $paramRendement = $parametresPrimes['prime_rendement'];
                    $montant = 0;
                    
                    if ($paramRendement['type'] === 'pourcentage') {
                        $montant = $salaireBase * ($paramRendement['taux'] / 100);
                    } else {
                        $montant = $paramRendement['montant'] ?? 0;
                    }
                    
                    $primes[] = [
                        'code' => 'PR',
                        'libelle' => 'Prime de rendement',
                        'type' => ElementPaie::TYPE_PRIME,
                        'categorie' => ElementPaie::CATEGORIE_PRIME_RENDEMENT,
                        'base' => $salaireBase,
                        'taux' => $paramRendement['taux'] ?? null,
                        'montant' => $montant,
                        'imposable' => $paramRendement['imposable'] ?? true,
                        'ordre' => 20
                    ];
                }
            }
        }
        
        // Autres primes personnalisées (à partir des meta_donnees de l'employeur)
        $primesPersonnalisees = $employeur->getMeta('primes_personnalisees') ?? [];
        
        foreach ($primesPersonnalisees as $index => $prime) {
            if (isset($prime['libelle']) && isset($prime['montant'])) {
                $primes[] = [
                    'code' => 'PP' . ($index + 1),
                    'libelle' => $prime['libelle'],
                    'type' => ElementPaie::TYPE_PRIME,
                    'categorie' => 'prime_personnalisee',
                    'base' => null,
                    'taux' => null,
                    'montant' => $prime['montant'],
                    'imposable' => $prime['imposable'] ?? true,
                    'ordre' => 30 + $index
                ];
            }
        }
        
        // Ajouter les primes supplémentaires
        foreach ($primesSupplementaires as $index => $prime) {
            if (isset($prime['libelle']) && isset($prime['montant'])) {
                $primes[] = [
                    'code' => $prime['code'] ?? ('PS' . ($index + 1)),
                    'libelle' => $prime['libelle'],
                    'type' => ElementPaie::TYPE_PRIME,
                    'categorie' => $prime['categorie'] ?? 'prime_supplementaire',
                    'base' => $prime['base'] ?? null,
                    'taux' => $prime['taux'] ?? null,
                    'montant' => $prime['montant'],
                    'imposable' => $prime['imposable'] ?? true,
                    'ordre' => 50 + $index
                ];
            }
        }
        
        return $primes;
    }
    
    /**
     * {@inheritDoc}
     */
    public function genererBulletinPaie(Employeur $employeur, ConfigurationPaie $configuration, array $parametres = [])
    {
        // Ajouter des logs pour le débogage
        Log::info('Début de génération du bulletin de paie', [
            'employeur_id' => $employeur->id,
            'employeur_nom' => $employeur->nom_complet,
            'configuration_id' => $configuration->id,
            'parametres' => $parametres
        ]);
        // Récupérer les paramètres
        $periodeDebut = $parametres['periode_debut'] ?? now()->startOfMonth()->format('Y-m-d');
        $periodeFin = $parametres['periode_fin'] ?? now()->endOfMonth()->format('Y-m-d');
        $datePaiement = $parametres['date_paiement'] ?? now()->addDays(5)->format('Y-m-d');
        $generePar = $parametres['genere_par'] ?? null;
        
        // Calculer le salaire brut
        $elementsSupplementaires = $parametres['elements_supplementaires'] ?? [];
        $resultatSalaireBrut = $this->calculerSalaireBrut($employeur, $configuration, $elementsSupplementaires);
        
        // Calculer les retenues salariales
        $elementsImposables = $parametres['elements_imposables'] ?? [];
        $resultatRetenues = $this->calculerRetenuesSalariales($resultatSalaireBrut['salaire_brut'], $configuration, $elementsImposables);
        
        // Calculer le salaire net
        $salaireNet = $this->calculerSalaireNet($resultatSalaireBrut['salaire_brut'], $resultatRetenues['total_retenues']);
        
        // Calculer les charges patronales
        $resultatChargesPatronales = $this->calculerChargesPatronales($resultatSalaireBrut['salaire_brut'], $configuration);
        
    

        // Créer le bulletin de paie
        $bulletinData = [
            'employeur_id' => $employeur->id,
            'entreprise_id' => $employeur->entreprise_id,
            'periode_debut' => $periodeDebut,
            'periode_fin' => $periodeFin,
            'date_paiement' => $datePaiement,
            'salaire_base' => $resultatSalaireBrut['salaire_base'],
            'total_indemnites' => $resultatSalaireBrut['total_indemnites'],
            'total_primes' => $resultatSalaireBrut['total_primes'],
            'salaire_brut' => $resultatSalaireBrut['salaire_brut'],
            'cnps_employe' => $resultatRetenues['cnps_employe'],
            'igr' => $resultatRetenues['igr'],
            'total_retenues' => $resultatRetenues['total_retenues'],
            'salaire_net' => $salaireNet,
            'cnps_employeur' => $resultatChargesPatronales['cnps_employeur'],
            'charges_patronales' => $resultatChargesPatronales['total_charges'],
            'statut' => 'brouillon',
            'genere_par' => $generePar,
            'meta_donnees' => [
                'quotient_familial' => $employeur->getMeta('quotient_familial'),
                'elements_supplementaires' => $elementsSupplementaires,
                'elements_imposables' => $elementsImposables
            ]
        ];
        
        // Générer une référence unique pour ce bulletin en utilisant une approche plus robuste
        // Format: XX-YYYYMM-NNNN où XX est le préfixe, YYYYMM est l'année et le mois, NNNN est un numéro séquentiel
        $moisAnnee = date('Ym', strtotime($periodeDebut));
        
        // Utiliser les initiales de l'employé pour le préfixe
        $nomParts = explode(' ', trim($employeur->nom_complet));
        $initiales = '';
        foreach ($nomParts as $part) {
            if (!empty($part)) {
                $initiales .= strtoupper(substr($part, 0, 1));
            }
        }
        // Limiter à 2 caractères et s'assurer qu'il y a au moins 2 caractères
        $prefixe = substr($initiales . 'XX', 0, 2);
        
        // Utiliser un identifiant unique pour éviter les problèmes de concurrence
        $uniqueId = substr(md5($employeur->id . time() . rand(1000, 9999)), 0, 4);
        
        // Créer la référence unique avec un timestamp pour éviter les doublons
        $reference = "{$prefixe}-{$moisAnnee}-{$uniqueId}";
        
        // Vérifier si cette référence existe déjà (très peu probable mais par sécurité)
        $referenceExists = BulletinPaie::where('reference', $reference)->exists();
        if ($referenceExists) {
            // Si par hasard la référence existe, en générer une nouvelle avec un autre identifiant unique
            $uniqueId = substr(md5($employeur->id . time() . rand(10000, 99999)), 0, 4);
            $reference = "{$prefixe}-{$moisAnnee}-{$uniqueId}";
        }
        
        // Ajouter la référence aux données du bulletin
        $bulletinData['reference'] = $reference;
        
        Log::info('Génération de référence pour bulletin', [
            'employeur_id' => $employeur->id,
            'employeur_nom' => $employeur->nom_complet,
            'reference' => $reference,
            'mois_annee' => $moisAnnee,
            'prefixe' => $prefixe,
            'unique_id' => $uniqueId
        ]);
        
        // Créer le bulletin
        $bulletin = $this->bulletinRepository->creer($bulletinData);
        
        // Ajouter les éléments de paie
        $elements = [];
        
        // Élément salaire de base
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'SB',
            'libelle' => 'Salaire de base',
            'type' => ElementPaie::TYPE_SALAIRE,
            'categorie' => ElementPaie::CATEGORIE_SALAIRE_BASE,
            'base' => $resultatSalaireBrut['salaire_base'],
            'taux' => null,
            'montant' => $resultatSalaireBrut['salaire_base'],
            'imposable' => true,
            'ordre' => 1
        ];
        
        // Éléments indemnités
        foreach ($resultatSalaireBrut['indemnites'] as $index => $indemnite) {
            $elements[] = array_merge($indemnite, [
                'bulletin_paie_id' => $bulletin->id,
                'ordre' => 10 + $index
            ]);
        }
        
        // Éléments primes
        foreach ($resultatSalaireBrut['primes'] as $index => $prime) {
            $elements[] = array_merge($prime, [
                'bulletin_paie_id' => $bulletin->id,
                'ordre' => 20 + $index
            ]);
        }
        
        // Élément CNPS employé
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'CNPS',
            'libelle' => 'CNPS Employé',
            'type' => ElementPaie::TYPE_RETENUE_SALARIALE,
            'categorie' => ElementPaie::CATEGORIE_CNPS_EMPLOYE,
            'base' => min($resultatSalaireBrut['salaire_brut'], $configuration->plafond_cnps),
            'taux' => $configuration->taux_cnps_employe,
            'montant' => $resultatRetenues['cnps_employe'],
            'imposable' => false,
            'ordre' => 50
        ];
        
        // Élément IGR
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'IGR',
            'libelle' => 'Impôt Général sur le Revenu',
            'type' => ElementPaie::TYPE_RETENUE_SALARIALE,
            'categorie' => ElementPaie::CATEGORIE_IGR,
            'base' => $resultatRetenues['base_imposable'],
            'taux' => null,
            'montant' => $resultatRetenues['igr'],
            'imposable' => false,
            'ordre' => 51
        ];
        
        // Éléments charges patronales
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'CNPSE',
            'libelle' => 'CNPS Employeur',
            'type' => ElementPaie::TYPE_CHARGE_PATRONALE,
            'categorie' => ElementPaie::CATEGORIE_CNPS_EMPLOYEUR,
            'base' => min($resultatSalaireBrut['salaire_brut'], $configuration->plafond_cnps),
            'taux' => $configuration->taux_cnps_employeur,
            'montant' => $resultatChargesPatronales['cnps_employeur'],
            'imposable' => false,
            'ordre' => 60
        ];
        
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'PF',
            'libelle' => 'Prestations Familiales',
            'type' => ElementPaie::TYPE_CHARGE_PATRONALE,
            'categorie' => ElementPaie::CATEGORIE_PRESTATIONS_FAMILIALES,
            'base' => min($resultatSalaireBrut['salaire_brut'], $configuration->plafond_cnps),
            'taux' => $configuration->taux_prestations_familiales,
            'montant' => $resultatChargesPatronales['prestations_familiales'],
            'imposable' => false,
            'ordre' => 61
        ];
        
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'AT',
            'libelle' => 'Accident du Travail',
            'type' => ElementPaie::TYPE_CHARGE_PATRONALE,
            'categorie' => ElementPaie::CATEGORIE_ACCIDENT_TRAVAIL,
            'base' => min($resultatSalaireBrut['salaire_brut'], $configuration->plafond_cnps),
            'taux' => $configuration->taux_accident_travail,
            'montant' => $resultatChargesPatronales['accident_travail'],
            'imposable' => false,
            'ordre' => 62
        ];
        
        $elements[] = [
            'bulletin_paie_id' => $bulletin->id,
            'code' => 'AM',
            'libelle' => 'Assurance Maladie',
            'type' => ElementPaie::TYPE_CHARGE_PATRONALE,
            'categorie' => ElementPaie::CATEGORIE_ASSURANCE_MALADIE,
            'base' => min($resultatSalaireBrut['salaire_brut'], $configuration->plafond_cnps),
            'taux' => $configuration->taux_assurance_maladie,
            'montant' => $resultatChargesPatronales['assurance_maladie'],
            'imposable' => false,
            'ordre' => 63
        ];
        
        // Ajouter les éléments au bulletin
        foreach ($elements as $element) {
            $bulletin->elements()->create($element);
        }
        
        return $bulletin;
    }
}