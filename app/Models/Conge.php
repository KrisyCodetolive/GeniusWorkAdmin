<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Conge extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'employeur_id',
        'type_conge_id',
        'date_debut',
        'date_fin',
        'duree_jours',
        'motif',
        'justificatif',
        'statut',
        'validateur_id',
        'date_validation',
        'commentaire_validation',
        'est_paye',
        'meta_donnees'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_validation' => 'datetime',
        'est_paye' => 'boolean',
        'meta_donnees' => 'json'
    ];

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function typeConge()
    {
        return $this->belongsTo(TypeConge::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }

    public function scopeApprouve($query)
    {
        return $query->where('statut', 'approuve');
    }

    public function scopeRejete($query)
    {
        return $query->where('statut', 'rejete');
    }

    public function scopeAnnule($query)
    {
        return $query->where('statut', 'annule');
    }

    public function scopePeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_debut', [$debut, $fin])
                    ->orWhereBetween('date_fin', [$debut, $fin]);
    }

    // Helpers
    public function approuver(User $validateur, $commentaire = null)
    {
        $this->update([
            'statut' => 'approuve',
            'validateur_id' => $validateur->id,
            'date_validation' => now(),
            'commentaire_validation' => $commentaire
        ]);
    }

    public function rejeter(User $validateur, $commentaire)
    {
        $this->update([
            'statut' => 'rejete',
            'validateur_id' => $validateur->id,
            'date_validation' => now(),
            'commentaire_validation' => $commentaire
        ]);
    }

    public function annuler($commentaire = null)
    {
        $this->update([
            'statut' => 'annule',
            'commentaire_validation' => $commentaire
        ]);
    }

    public function estEnAttente()
    {
        return $this->statut === 'en_attente';
    }

    public function estApprouve()
    {
        return $this->statut === 'approuve';
    }

    public function estRejete()
    {
        return $this->statut === 'rejete';
    }

    public function estAnnule()
    {
        return $this->statut === 'annule';
    }

    public function estPaye()
    {
        return $this->est_paye;
    }

    public function getDuree()
    {
        return $this->duree_jours;
    }

    public function chevauchePeriode($debut, $fin)
    {
        return $this->date_fin >= $debut && $this->date_debut <= $fin;
    }
}
