<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class Adresse extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'employeur_id',
        'type',
        'rue',
        'ville',
        'region',
        'pays',
        'code_postal',
        'complement',
        'latitude',
        'longitude',
        'est_principale',
        'statut'
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'est_principale' => 'boolean',
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
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

    public function scopePrincipale($query)
    {
        return $query->where('est_principale', true);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function getAdresseComplete()
    {
        $adresse = $this->rue;
        if ($this->complement) $adresse .= ', ' . $this->complement;
        if ($this->code_postal) $adresse .= ', ' . $this->code_postal;
        $adresse .= ', ' . $this->ville;
        if ($this->region) $adresse .= ', ' . $this->region;
        $adresse .= ', ' . $this->pays;
        
        return $adresse;
    }

    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    public function setAsPrincipal()
    {
        if ($this->entreprise_id) {
            $this->entreprise->adresses()->update(['est_principale' => false]);
        } elseif ($this->employeur_id) {
            $this->employeur->adresses()->update(['est_principale' => false]);
        }
        
        $this->est_principale = true;
        $this->save();
    }
}
