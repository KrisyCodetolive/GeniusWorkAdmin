<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tracking extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'employeur_id',
        'presence_id',
        'type',
        'latitude',
        'longitude',
        'adresse',
        'date_heure',
        'methode_pointage',
        'appareil',
        'adresse_ip',
        'meta_donnees',
        'statut',
        'commentaire'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'date_heure' => 'datetime',
        'meta_donnees' => 'json'
    ];

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function presence()
    {
        return $this->belongsTo(Presence::class);
    }

    // Scopes
    public function scopeEntree($query)
    {
        return $query->where('type', 'entree');
    }

    public function scopeSortie($query)
    {
        return $query->where('type', 'sortie');
    }

    public function scopePauseDebut($query)
    {
        return $query->where('type', 'pause_debut');
    }

    public function scopePauseFin($query)
    {
        return $query->where('type', 'pause_fin');
    }

    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }

    public function scopeInvalide($query)
    {
        return $query->where('statut', 'invalide');
    }

    public function scopeSuspect($query)
    {
        return $query->where('statut', 'suspect');
    }

    public function scopeParDate($query, $date)
    {
        return $query->whereDate('date_heure', $date);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_heure', [$debut, $fin]);
    }

    // Helpers
    public function estEntree()
    {
        return $this->type === 'entree';
    }

    public function estSortie()
    {
        return $this->type === 'sortie';
    }

    public function estPauseDebut()
    {
        return $this->type === 'pause_debut';
    }

    public function estPauseFin()
    {
        return $this->type === 'pause_fin';
    }

    public function estValide()
    {
        return $this->statut === 'valide';
    }

    public function estInvalide()
    {
        return $this->statut === 'invalide';
    }

    public function estSuspect()
    {
        return $this->statut === 'suspect';
    }

    public function valider()
    {
        $this->update(['statut' => 'valide']);
    }

    public function invalider($commentaire = null)
    {
        $this->update([
            'statut' => 'invalide',
            'commentaire' => $commentaire
        ]);
    }

    public function marquerSuspect($commentaire = null)
    {
        $this->update([
            'statut' => 'suspect',
            'commentaire' => $commentaire
        ]);
    }

    public function getCoordonnees()
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }

    public function verifierLocalisation($rayon = 100)
    {
        // Logique de vérification de la localisation par rapport aux sites autorisés
        return true;
    }
}
