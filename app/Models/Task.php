<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class Task extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'titre',
        'active',
        'description',
        'date_debut',
        'date_fin',
        'timing',
        'livrable',
        'statut',
        'type',
        'priorite',
        'etiquette',
        'est_routine',
        'frequence_routine',
        'jour_routine',
        'createur_id',
        'meta_donnees',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'est_routine' => 'boolean',
        'meta_donnees' => 'json',
    ];

    // Constantes pour les statuts
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_TERMINE = 'termine';
    const STATUT_ANNULE = 'annule';
    const STATUT_EN_RETARD = 'en_retard';

    // Constantes pour les priorités
    const PRIORITE_BASSE = 'basse';
    const PRIORITE_MOYENNE = 'moyenne';
    const PRIORITE_HAUTE = 'haute';
    const PRIORITE_URGENTE = 'urgente';

    // Constantes pour les types
    const TYPE_STANDARD = 'standard';
    const TYPE_PROJET = 'projet';
    const TYPE_ROUTINE = 'routine';
    const TYPE_FORMATION = 'formation';
    const TYPE_REUNION = 'reunion';
    const TYPE_AUTRE = 'autre';

    // Constantes pour les fréquences de routine
    const FREQUENCE_QUOTIDIENNE = 'quotidienne';
    const FREQUENCE_HEBDOMADAIRE = 'hebdomadaire';
    const FREQUENCE_MENSUELLE = 'mensuelle';
    const FREQUENCE_TRIMESTRIELLE = 'trimestrielle';
    const FREQUENCE_ANNUELLE = 'annuelle';

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function assignations()
    {
        return $this->hasMany(TaskAssignation::class);
    }

    public function commentaires()
    {
        return $this->hasMany(TaskCommentaire::class);
    }

    public function fichiers()
    {
        return $this->hasMany(TaskFichier::class);
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', self::STATUT_EN_COURS);
    }

    public function scopeTermine($query)
    {
        return $query->where('statut', self::STATUT_TERMINE);
    }

    public function scopeEnRetard($query)
    {
        return $query->where('statut', self::STATUT_EN_RETARD);
    }

    public function scopeRoutine($query)
    {
        return $query->where('est_routine', true);
    }

    public function scopeNonRoutine($query)
    {
        return $query->where('est_routine', false);
    }

    public function scopeParPriorite($query, $priorite)
    {
        return $query->where('priorite', $priorite);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAVenir($query)
    {
        return $query->where('date_debut', '>', now());
    }

    public function scopeEnCours2($query)
    {
        return $query->where('date_debut', '<=', now())
            ->where('date_fin', '>=', now());
    }

    // Méthodes
    public function estEnRetard()
    {
        return $this->date_fin && $this->date_fin < now() && $this->statut !== self::STATUT_TERMINE;
    }

    public function marquerCommeTermine()
    {
        $this->statut = self::STATUT_TERMINE;
        $this->save();
    }

    public function marquerCommeEnCours()
    {
        $this->statut = self::STATUT_EN_COURS;
        $this->save();
    }

    public function marquerCommeAnnule()
    {
        $this->statut = self::STATUT_ANNULE;
        $this->save();
    }

    public function getProgressionAttribute()
    {
        $total = $this->assignations()->count();
        if ($total === 0) {
            return 0;
        }

        $terminees = $this->assignations()
            ->where('statut', TaskAssignation::STATUT_TERMINE)
            ->count();

        return ($terminees / $total) * 100;
    }

    public function getTempsRestantAttribute()
    {
        if (!$this->date_fin) {
            return null;
        }

        return now()->diffForHumans($this->date_fin, ['parts' => 2]);
    }

    public function getEstEnRetardAttribute()
    {
        return $this->estEnRetard();
    }

    public function getMeta($key, $default = null)
    {
        return ($this->meta_donnees ?? [])[$key] ?? $default;
    }

    public function setMeta($key, $value)
    {
        $meta = $this->meta_donnees ?? [];
        $meta[$key] = $value;
        $this->meta_donnees = $meta;
        $this->save();
    }
}
