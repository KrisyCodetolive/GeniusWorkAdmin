<?php

namespace App\Models\Paie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;

class ConfigurationPaie extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'configurations_paie';

    protected $fillable = [
        'entreprise_id',
        'nom',
        'description',
        'est_defaut',
        'smig',
        'plafond_cnps',
        'taux_cnps_employe',
        'taux_cnps_employeur',
        'taux_prestations_familiales',
        'taux_accident_travail',
        'taux_assurance_maladie',
        'abattement_igr',
        'baremes_igr',
        'parametres_indemnites',
        'parametres_primes',
        'meta_donnees'
    ];

    protected $casts = [
        'est_defaut' => 'boolean',
        'smig' => 'decimal:2',
        'plafond_cnps' => 'decimal:2',
        'taux_cnps_employe' => 'decimal:4',
        'taux_cnps_employeur' => 'decimal:4',
        'taux_prestations_familiales' => 'decimal:4',
        'taux_accident_travail' => 'decimal:4',
        'taux_assurance_maladie' => 'decimal:4',
        'abattement_igr' => 'decimal:4',
        'baremes_igr' => 'json',
        'parametres_indemnites' => 'json',
        'parametres_primes' => 'json',
        'meta_donnees' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($config) {
            // Si cette configuration est définie comme par défaut, désactiver les autres configurations par défaut
            if ($config->est_defaut) {
                self::where('entreprise_id', $config->entreprise_id)
                    ->where('est_defaut', true)
                    ->update(['est_defaut' => false]);
            }
        });

        static::updating(function ($config) {
            // Si cette configuration est définie comme par défaut, désactiver les autres configurations par défaut
            if ($config->est_defaut) {
                self::where('entreprise_id', $config->entreprise_id)
                    ->where('id', '!=', $config->id)
                    ->where('est_defaut', true)
                    ->update(['est_defaut' => false]);
            }
        });
    }

    /**
     * Relation avec l'entreprise
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Scope pour filtrer par entreprise
     */
    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour obtenir la configuration par défaut
     */
    public function scopeParDefaut($query)
    {
        return $query->where('est_defaut', true);
    }

    /**
     * Obtenir le barème IGR pour un montant donné
     */
    public function getBaremeIGR($montant)
    {
        $baremes = $this->baremes_igr ?? [];
        
        foreach ($baremes as $bareme) {
            if ($montant >= ($bareme['min'] ?? 0) && $montant <= ($bareme['max'] ?? PHP_FLOAT_MAX)) {
                return $bareme;
            }
        }
        
        return null;
    }

    /**
     * Calculer l'IGR pour un montant imposable donné
     */
    public function calculerIGR($montantImposable)
    {
        $bareme = $this->getBaremeIGR($montantImposable);
        
        if (!$bareme) {
            return 0;
        }
        
        $tauxIGR = $bareme['taux'] ?? 0;
        $montantAbattement = $montantImposable * ($this->abattement_igr / 100);
        $montantApresAbattement = $montantImposable - $montantAbattement;
        
        return $montantApresAbattement * ($tauxIGR / 100);
    }

    /**
     * Calculer la cotisation CNPS employé pour un salaire brut donné
     */
    public function calculerCNPSEmploye($salaireBrut)
    {
        $assiette = min($salaireBrut, $this->plafond_cnps);
        return $assiette * ($this->taux_cnps_employe / 100);
    }

    /**
     * Calculer les charges patronales pour un salaire brut donné
     */
    public function calculerChargesPatronales($salaireBrut)
    {
        $assiette = min($salaireBrut, $this->plafond_cnps);
        
        $cnpsEmployeur = $assiette * ($this->taux_cnps_employeur / 100);
        $prestationsFamiliales = $assiette * ($this->taux_prestations_familiales / 100);
        $accidentTravail = $assiette * ($this->taux_accident_travail / 100);
        $assuranceMaladie = $assiette * ($this->taux_assurance_maladie / 100);
        
        return [
            'cnps_employeur' => $cnpsEmployeur,
            'prestations_familiales' => $prestationsFamiliales,
            'accident_travail' => $accidentTravail,
            'assurance_maladie' => $assuranceMaladie,
            'total' => $cnpsEmployeur + $prestationsFamiliales + $accidentTravail + $assuranceMaladie
        ];
    }

    /**
     * Obtenir le paramètre d'indemnité par catégorie
     */
    public function getParametreIndemnite($categorie)
    {
        $parametres = $this->parametres_indemnites ?? [];
        return $parametres[$categorie] ?? null;
    }

    /**
     * Obtenir le paramètre de prime par catégorie
     */
    public function getParametrePrime($categorie)
    {
        $parametres = $this->parametres_primes ?? [];
        return $parametres[$categorie] ?? null;
    }

    /**
     * Créer une configuration par défaut pour une entreprise
     */
    public static function creerConfigurationDefaut($entrepriseId)
    {
        return self::create([
            'entreprise_id' => $entrepriseId,
            'nom' => 'Configuration par défaut',
            'description' => 'Configuration par défaut pour la paie',
            'est_defaut' => true,
            'smig' => 75000, // SMIG 2023 en FCFA
            'plafond_cnps' => 225000, // Plafond mensuel CNPS
            'taux_cnps_employe' => 6.3, // 6.3%
            'taux_cnps_employeur' => 7.7, // 7.7%
            'taux_prestations_familiales' => 5.75, // 5.75%
            'taux_accident_travail' => 2.0, // 2.0% (peut varier selon le secteur)
            'taux_assurance_maladie' => 0.75, // 0.75%
            'abattement_igr' => 20.0, // 20% d'abattement forfaitaire
            'baremes_igr' => [
                [
                    'min' => 0,
                    'max' => 25000,
                    'taux' => 0
                ],
                [
                    'min' => 25001,
                    'max' => 100000,
                    'taux' => 10
                ],
                [
                    'min' => 100001,
                    'max' => 200000,
                    'taux' => 15
                ],
                [
                    'min' => 200001,
                    'max' => 300000,
                    'taux' => 20
                ],
                [
                    'min' => 300001,
                    'max' => PHP_INT_MAX,
                    'taux' => 25
                ]
            ],
            'parametres_indemnites' => [
                'indemnite_logement' => [
                    'type' => 'pourcentage',
                    'base' => 'salaire_base',
                    'taux' => 15.0,
                    'imposable' => true
                ],
                'indemnite_transport' => [
                    'type' => 'pourcentage',
                    'base' => 'salaire_base',
                    'taux' => 10.0,
                    'imposable' => false
                ]
            ],
            'parametres_primes' => [
                'prime_anciennete' => [
                    'type' => 'pourcentage',
                    'base' => 'salaire_base',
                    'taux' => [
                        '2' => 2.0, // 2% après 2 ans
                        '5' => 5.0, // 5% après 5 ans
                        '10' => 10.0, // 10% après 10 ans
                        '15' => 15.0, // 15% après 15 ans
                        '20' => 20.0 // 20% après 20 ans
                    ],
                    'imposable' => true
                ],
                'prime_rendement' => [
                    'type' => 'pourcentage',
                    'base' => 'salaire_base',
                    'taux' => 5.0,
                    'imposable' => true
                ]
            ]
        ]);
    }
}
