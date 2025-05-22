<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Models\Facturation;
use App\Traits\BelongsToEntreprise;

class FraisUsage extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'facturation_id',
        'type_frais',
        'description',
        'quantite',
        'prix_unitaire',
        'montant_total',
        'periode_debut',
        'periode_fin',
        'statut',
        'devise'
    ];

    protected $casts = [
        'periode_debut' => 'datetime',
        'periode_fin' => 'datetime',
        'prix_unitaire' => 'decimal:2',
        'montant_total' => 'decimal:2',
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function facturation()
    {
        return $this->belongsTo(Facturation::class);
    }

    // Scopes
    public function scopeFacture($query)
    {
        return $query->whereNotNull('facturation_id');
    }

    public function scopeNonFacture($query)
    {
        return $query->whereNull('facturation_id');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_frais', $type);
    }

    public function scopePeriode($query, $debut, $fin)
    {
        return $query->whereBetween('periode_debut', [$debut, $fin])
                    ->orWhereBetween('periode_fin', [$debut, $fin]);
    }

    // Helpers
    public function calculerMontantTotal()
    {
        $this->montant_total = $this->quantite * $this->prix_unitaire;
        $this->save();
    }

    public function getMontantTotalFormate()
    {
        return number_format($this->montant_total, 2) . ' ' . $this->devise;
    }

    public function getPrixUnitaireFormate()
    {
        return number_format($this->prix_unitaire, 2) . ' ' . $this->devise;
    }

    public function getDureePeriode()
    {
        return $this->periode_debut->diffInDays($this->periode_fin);
    }
}
