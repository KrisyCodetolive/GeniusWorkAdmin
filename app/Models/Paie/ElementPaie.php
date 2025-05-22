<?php

namespace App\Models\Paie;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ElementPaie extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $table = 'elements_paie';

    protected $fillable = [
        'bulletin_paie_id',
        'code',
        'libelle',
        'type',
        'categorie',
        'base',
        'taux',
        'montant',
        'imposable',
        'ordre',
        'meta_donnees'
    ];

    protected $casts = [
        'base' => 'decimal:2',
        'taux' => 'decimal:4',
        'montant' => 'decimal:2',
        'imposable' => 'boolean',
        'ordre' => 'integer',
        'meta_donnees' => 'json',
    ];

    /**
     * Les types d'éléments de paie possibles
     */
    const TYPE_SALAIRE = 'salaire';
    const TYPE_INDEMNITE = 'indemnite';
    const TYPE_PRIME = 'prime';
    const TYPE_RETENUE_SALARIALE = 'retenue_salariale';
    const TYPE_CHARGE_PATRONALE = 'charge_patronale';
    const CATEGORIE_PRIME_AUTRE = 'prime_autre';
    /**
     * Les catégories d'éléments de paie possibles
     */
    const CATEGORIE_SALAIRE_BASE = 'salaire_base';
    const CATEGORIE_INDEMNITE_LOGEMENT = 'indemnite_logement';
    const CATEGORIE_INDEMNITE_TRANSPORT = 'indemnite_transport';
    const CATEGORIE_PRIME_ANCIENNETE = 'prime_anciennete';
    const CATEGORIE_PRIME_RENDEMENT = 'prime_rendement';
    const CATEGORIE_CNPS_EMPLOYE = 'cnps_employe';
    const CATEGORIE_IGR = 'igr';
    const CATEGORIE_CNPS_EMPLOYEUR = 'cnps_employeur';
    const CATEGORIE_PRESTATIONS_FAMILIALES = 'prestations_familiales';
    const CATEGORIE_ACCIDENT_TRAVAIL = 'accident_travail';
    const CATEGORIE_ASSURANCE_MALADIE = 'assurance_maladie';
    const CATEGORIE_INDEMNITE_PERSONNALISEE = 'indemnite_personnalisee';
    const CATEGORIE_INDEMNITE_AUTRE = 'indemnite_autre';

    /**
     * Relation avec le bulletin de paie
     */
    public function bulletinPaie()
    {
        return $this->belongsTo(BulletinPaie::class);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeParCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Scope pour filtrer les éléments imposables
     */
    public function scopeImposable($query)
    {
        return $query->where('imposable', true);
    }

    /**
     * Scope pour filtrer les éléments non imposables
     */
    public function scopeNonImposable($query)
    {
        return $query->where('imposable', false);
    }

    /**
     * Vérifie si l'élément est un salaire
     */
    public function estSalaire()
    {
        return $this->type === self::TYPE_SALAIRE;
    }

    /**
     * Vérifie si l'élément est une indemnité
     */
    public function estIndemnite()
    {
        return $this->type === self::TYPE_INDEMNITE;
    }

    /**
     * Vérifie si l'élément est une prime
     */
    public function estPrime()
    {
        return $this->type === self::TYPE_PRIME;
    }

    /**
     * Vérifie si l'élément est une retenue salariale
     */
    public function estRetenueSalariale()
    {
        return $this->type === self::TYPE_RETENUE_SALARIALE;
    }

    /**
     * Vérifie si l'élément est une charge patronale
     */
    public function estChargePatronale()
    {
        return $this->type === self::TYPE_CHARGE_PATRONALE;
    }

    /**
     * Calcule le montant de l'élément en fonction de la base et du taux
     */
    public function calculerMontant()
    {
        if ($this->base && $this->taux) {
            return $this->base * ($this->taux / 100);
        }
        
        return $this->montant;
    }
}
