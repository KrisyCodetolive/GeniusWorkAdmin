<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Traits\BelongsToEntreprise;

class TypeConge extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'description',
        'duree_max_annuelle',
        'necessite_justificatif',
        'est_paye',
        'deductible_solde',
        'delai_demande_prealable',
        'conditions_eligibilite',
        'configuration',
        'statut'
    ];

    protected $casts = [
        'necessite_justificatif' => 'boolean',
        'est_paye' => 'boolean',
        'deductible_solde' => 'boolean',
        'conditions_eligibilite' => 'json',
        'configuration' => 'json'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopePaye($query)
    {
        return $query->where('est_paye', true);
    }

    public function scopeNonPaye($query)
    {
        return $query->where('est_paye', false);
    }

    // Helpers
    public function isActif()
    {
        return $this->statut === 'actif';
    }

    public function estPaye()
    {
        return $this->est_paye;
    }

    public function estDeductible()
    {
        return $this->deductible_solde;
    }

    public function necessiteJustificatif()
    {
        return $this->necessite_justificatif;
    }

    public function getDelaiDemande()
    {
        return $this->delai_demande_prealable;
    }

    public function verifierEligibilite(Employeur $employeur)
    {
        $conditions = $this->conditions_eligibilite ?? [];
        // Logique de vérification des conditions d'éligibilité
        return true;
    }
}
