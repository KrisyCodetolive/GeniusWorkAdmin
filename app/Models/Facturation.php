<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Models\Abonnement;
use App\Models\FraisUsage;
use App\Traits\BelongsToEntreprise;

class Facturation extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'abonnement_id',
        'numero_facture',
        'date_facturation',
        'date_echeance',
        'montant_ht',
        'taux_tva',
        'montant_tva',
        'montant_ttc',
        'statut_paiement',
        'mode_paiement',
        'reference_paiement',
        'notes',
        'devise'
    ];

    protected $casts = [
        'date_facturation' => 'datetime',
        'date_echeance' => 'datetime',
        'montant_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class);
    }

    public function fraisUsages()
    {
        return $this->hasMany(FraisUsage::class);
    }
    
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    // Scopes
    public function scopePaye($query)
    {
        return $query->where('statut_paiement', 'payee');
    }

    public function scopeImpaye($query)
    {
        return $query->where('statut_paiement', 'impaye');
    }

    public function scopeEchu($query)
    {
        return $query->where('date_echeance', '<', now());
    }

    // Helpers
    public function isPaye()
    {
        return $this->statut_paiement === 'payee';
    }

    public function isEchu()
    {
        return $this->date_echeance < now();
    }

    public function getMontantHTFormate()
    {
        return number_format($this->montant_ht, 2) . ' ' . $this->devise;
    }

    public function getMontantTTCFormate()
    {
        return number_format($this->montant_ttc, 2) . ' ' . $this->devise;
    }

    public function calculerMontants()
    {
        $this->montant_tva = $this->montant_ht * ($this->taux_tva / 100);
        $this->montant_ttc = $this->montant_ht + $this->montant_tva;
        $this->save();
    }
}
