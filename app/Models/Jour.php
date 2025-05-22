<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Employeur;
use App\Models\PlageHoraire;
use App\Models\Presence;
use App\Models\Permutation;
use App\Traits\BelongsToEntreprise;
use Carbon\Carbon;

class Jour extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'employeur_id',
        'plage_horaire_id',
        'entreprise_id',
    ];

 
    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function plageHoraire()
    {
        return $this->belongsTo(PlageHoraire::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function permutations()
    {
        return $this->hasMany(Permutation::class, 'jour_id');
    }

    public function permutationsRecu()
    {
        return $this->hasMany(Permutation::class, 'jour_cible_id');
    }

    // Méthodes
    public function getDuree()
    {
        if (!$this->plageHoraire) {
            return 0;
        }
        
        return $this->plageHoraire->getDuree();
    }
  

}
