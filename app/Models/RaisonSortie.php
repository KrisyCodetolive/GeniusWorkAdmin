<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Entreprise;
use App\Traits\BelongsToEntreprise;

class RaisonSortie extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'motif',
        'description',
        'type',
        'duree_max',
        'necessite_validation',
        'statut',
        'configuration'
    ];

    protected $casts = [
        'necessite_validation' => 'boolean',
        'configuration' => 'json',
    ];

    // Relations
    public function presences()
    {
        return $this->hasMany(Presence::class);
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

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeNecessiteValidation($query)
    {
        return $query->where('necessite_validation', true);
    }

    // Helpers
    public function isActif()
    {
        return $this->statut === 'actif';
    }

    public function necessiteValidation()
    {
        return $this->necessite_validation;
    }

    public function getUtilisationCount()
    {
        return $this->presences()->count();
    }

    public function estUtilisePar(Employeur $employeur)
    {
        return $this->presences()
            ->where('employeur_id', $employeur->id)
            ->exists();
    }

    public function getUtilisationMoyenneParJour()
    {
        $total = $this->presences()->count();
        $jours = $this->presences()
            ->selectRaw('COUNT(DISTINCT DATE(date_heure)) as jours')
            ->first()
            ->jours;

        return $jours > 0 ? $total / $jours : 0;
    }
}
