<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class JourTravail extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'jour_semaine',
        'est_travaille',
        'est_ferie',
        'heure_debut_standard',
        'heure_fin_standard',
        'duree_pause_standard',
        'plages_horaires',
        'configuration'
    ];

    protected $casts = [
        'est_travaille' => 'boolean',
        'est_ferie' => 'boolean',
        'configuration' => 'json',
        'plages_horaires' => 'json',
    ];

    // Relations
    public function jours()
    {
        return $this->hasMany(Jour::class);
    }

    public function plagesHoraires()
    {
        return $this->belongsToMany(PlageHoraire::class, 'plage_horaire_jour_travail');
    }

    // Scopes
    public function scopeTravaille($query)
    {
        return $query->where('est_travaille', true);
    }

    public function scopeNonTravaille($query)
    {
        return $query->where('est_travaille', false);
    }

    public function scopeParJour($query, $jour)
    {
        return $query->where('jour_semaine', $jour);
    }

    // Helpers
    public function getJourFr()
    {
        // Si le jour est déjà en français, on le retourne directement
        $joursFr = [
            'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'
        ];
        
        if (in_array($this->jour_semaine, $joursFr)) {
            return $this->jour_semaine;
        }
        
        // Sinon on fait la conversion depuis l'anglais
        $jours = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche'
        ];

        return $jours[$this->jour_semaine] ?? $this->jour_semaine;
    }

    public function getEmployesCount()
    {
        return $this->jours()->count();
    }

    public function hasEmploye(Employeur $employeur)
    {
        return $this->jours()->where('employeur_id', $employeur->id)->exists();
    }
}
