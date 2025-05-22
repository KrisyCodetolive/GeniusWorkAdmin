<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class Abonnement extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'plan_abonnement_id',
        'code_promo_id',
        'date_debut',
        'date_fin',
        'type_periode',
        'montant',
        'reduction_code_promo',
        'statut',
        'mode_paiement',
        'reference_paiement',
        'notes',
        'renouvellement_automatique',
        'periode_facturation',
        'methode_paiement',
        'reference_client',
        'facture_automatique',
        'notes_facturation',
        'nombre_personnels'
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'montant' => 'decimal:2',
        'reduction_code_promo' => 'decimal:2',
        'renouvellement_automatique' => 'boolean',
        'facture_automatique' => 'boolean'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function planAbonnement()
    {
        return $this->belongsTo(PlanAbonnement::class);
    }

    public function codePromo()
    {
        return $this->belongsTo(CodePromo::class);
    }

    public function facturations()
    {
        return $this->hasMany(Facturation::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
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

    public function scopeExpire($query)
    {
        return $query->where('date_fin', '<', now());
    }

    public function scopeEnEssai($query)
    {
        return $query->where('type_periode', 'essai');
    }

    // Helpers
    public function isActif()
    {
        return $this->statut === 'actif' && $this->date_fin > now();
    }

    public function isExpire()
    {
        return $this->date_fin < now();
    }

    public function getDureeRestante()
    {
        return now()->diffInDays($this->date_fin);
    }

    public function renouveler($type_periode = null)
    {
        $type_periode = $type_periode ?? $this->type_periode;
        $duree = $type_periode === 'mensuel' ? 30 : 365;
        
        $this->update([
            'date_debut' => now(),
            'date_fin' => now()->addDays($duree),
            'type_periode' => $type_periode,
            'statut' => 'actif'
        ]);
    }

    public function getMontantFormate()
    {
        return number_format($this->montant, 0) . ' ' . $this->planAbonnement->devise;
    }
}
