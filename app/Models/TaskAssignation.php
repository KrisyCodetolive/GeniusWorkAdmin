<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class TaskAssignation extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'task_id',
        'assignable_type',
        'assignable_id',
        'employeur_id',
        'statut',
        'progression',
        'date_debut_reelle',
        'date_fin_reelle',
        'commentaire',
        'meta_donnees',
    ];

    protected $casts = [
        'date_debut_reelle' => 'datetime',
        'date_fin_reelle' => 'datetime',
        'progression' => 'integer',
        'meta_donnees' => 'json',
    ];

    // Constantes pour les statuts
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_EN_COURS = 'en_cours';
    const STATUT_TERMINE = 'termine';
    const STATUT_ANNULE = 'annule';
    const STATUT_EN_RETARD = 'en_retard';

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function assignable()
    {
        return $this->morphTo();
    }

    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
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

    public function scopeParEmployeur($query, $employeurId)
    {
        return $query->where('employeur_id', $employeurId);
    }

    public function scopeParDepartement($query, $departementId)
    {
        return $query->where('assignable_type', Departement::class)
            ->where('assignable_id', $departementId);
    }

    public function scopeParEquipe($query, $equipeId)
    {
        return $query->where('assignable_type', Equipe::class)
            ->where('assignable_id', $equipeId);
    }

    public function scopeGlobale($query)
    {
        return $query->whereNull('assignable_type')
            ->whereNull('assignable_id');
    }

    // Méthodes
    public function marquerCommeTermine()
    {
        $this->statut = self::STATUT_TERMINE;
        $this->progression = 100;
        $this->date_fin_reelle = now();
        $this->save();
    }

    public function marquerCommeEnCours()
    {
        $this->statut = self::STATUT_EN_COURS;
        if (!$this->date_debut_reelle) {
            $this->date_debut_reelle = now();
        }
        $this->save();
    }

    public function marquerCommeAnnule()
    {
        $this->statut = self::STATUT_ANNULE;
        $this->save();
    }

    public function mettreAJourProgression($progression)
    {
        $this->progression = min(100, max(0, $progression));
        
        if ($this->progression == 100) {
            $this->marquerCommeTermine();
        } elseif ($this->progression > 0 && $this->statut == self::STATUT_EN_ATTENTE) {
            $this->marquerCommeEnCours();
        }
        
        $this->save();
    }

    public function estEnRetard()
    {
        $task = $this->task;
        return $task && $task->date_fin && $task->date_fin < now() && $this->statut !== self::STATUT_TERMINE;
    }

    public function getTempsRestantAttribute()
    {
        if (!$this->task || !$this->task->date_fin) {
            return null;
        }

        return now()->diffForHumans($this->task->date_fin, ['parts' => 2]);
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
