<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Employeur;
use App\Models\User;
use App\Models\Presence;

class Supplementaire extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'employeur_id',
        'date',
        'heure_debut',
        'heure_fin',
        'nombre_heures',
        'taux_majoration',
        'montant',
        'motif',
        'statut',
        'commentaire',
        'validateur_id',
        'date_validation'
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime',
        'nombre_heures' => 'decimal:2',
        'taux_majoration' => 'decimal:2',
        'montant' => 'decimal:2',
        'date_validation' => 'datetime'
    ];

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }
    
    public function presences()
    {
        return $this->hasMany(Presence::class);
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

    // Helpers
    public function calculerMontant()
    {
        $this->montant = $this->nombre_heures * $this->employeur->salaire_base * (1 + $this->taux_majoration / 100);
        $this->save();
    }

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

    public function getMontantFormate()
    {
        return number_format($this->montant, 2) . ' ' . $this->employeur->entreprise->devise;
    }

    public function getDuree()
    {
        return $this->heure_debut->diffInHours($this->heure_fin);
    }
}
