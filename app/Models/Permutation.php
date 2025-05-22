<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Permutation extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'employeur_1_id',
        'employeur_2_id',
        'plage_horaire_1_id',
        'plage_horaire_2_id',
        'date',
        'motif',
        'statut',
        'commentaire',
        'validateur_id',
        'date_validation'
    ];

    protected $casts = [
        'date' => 'date',
        'date_validation' => 'datetime',
    ];

    // Relations
    public function employeur1()
    {
        return $this->belongsTo(Employeur::class, 'employeur_1_id');
    }

    public function employeur2()
    {
        return $this->belongsTo(Employeur::class, 'employeur_2_id');
    }

    public function plageHoraire1()
    {
        return $this->belongsTo(PlageHoraire::class, 'plage_horaire_1_id');
    }

    public function plageHoraire2()
    {
        return $this->belongsTo(PlageHoraire::class, 'plage_horaire_2_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    // Scopes
    public function scopeApprouve($query)
    {
        return $query->where('statut', 'approuve');
    }

    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeRejete($query)
    {
        return $query->where('statut', 'rejete');
    }

    public function scopePeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date', [$debut, $fin]);
    }

    public function scopeValide($query)
    {
        return $query->whereNotNull('validateur_id');
    }

    public function scopeParEmployeur($query, $employeur_id)
    {
        return $query->where('employeur_1_id', $employeur_id)
                    ->orWhere('employeur_2_id', $employeur_id);
    }

    // Helpers
    public function valider(User $validateur, $commentaire = null)
    {
        $this->update([
            'validateur_id' => $validateur->id,
            'date_validation' => now(),
            'statut' => 'approuve',
            'commentaire' => $commentaire
        ]);
    }

    public function rejeter(User $validateur, $commentaire)
    {
        $this->update([
            'validateur_id' => $validateur->id,
            'date_validation' => now(),
            'statut' => 'rejete',
            'commentaire' => $commentaire
        ]);
    }

    public function annuler($commentaire = null)
    {
        $this->update([
            'statut' => 'annule',
            'commentaire' => $commentaire
        ]);
    }

    public function isValide()
    {
        return !is_null($this->validateur_id);
    }

    public function concerne(Employeur $employeur)
    {
        return $this->employeur_1_id === $employeur->id || 
               $this->employeur_2_id === $employeur->id;
    }
}
