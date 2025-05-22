<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Rapport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rapports';

    protected $fillable = [
        'titre',
        'description',
        'type',
        'format',
        'date_debut',
        'date_fin',
        'date_generation',
        'employeur_id',
        'departement_id',
        'user_id',
        'generateur_id',
        'parametres',
        'donnees',
        'fichier_path',
        'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_generation' => 'date',
        'parametres' => 'array',
        'donnees' => 'array',
    ];

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function generateur()
    {
        return $this->belongsTo(User::class, 'generateur_id');
    }

    // Méthodes
    public function getPeriodeFormatteeAttribute()
    {
        return Carbon::parse($this->date_debut)->format('d/m/Y') . ' au ' . Carbon::parse($this->date_fin)->format('d/m/Y');
    }

    public function getDureeJoursAttribute()
    {
        return Carbon::parse($this->date_debut)->diffInDays(Carbon::parse($this->date_fin)) + 1;
    }

    public function getUrlTelechargerAttribute()
    {
        if ($this->fichier_path) {
            return route('rapports.telecharger', $this->id);
        }
        return null;
    }

    // Scopes
    public function scopeFinancier($query)
    {
        return $query->where('type', 'financier');
    }

    public function scopePresence($query)
    {
        return $query->where('type', 'presence');
    }

    public function scopePerformance($query)
    {
        return $query->where('type', 'performance');
    }

    public function scopeAbsence($query)
    {
        return $query->where('type', 'absence');
    }

    public function scopeHeuresSupp($query)
    {
        return $query->where('type', 'heures_supp');
    }

    public function scopeConge($query)
    {
        return $query->where('type', 'conge');
    }

    public function scopePeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_debut', [$debut, $fin])
                     ->orWhereBetween('date_fin', [$debut, $fin])
                     ->orWhere(function($q) use ($debut, $fin) {
                         $q->where('date_debut', '<=', $debut)
                           ->where('date_fin', '>=', $fin);
                     });
    }

    public function scopeParEmployeur($query, $employeurId)
    {
        return $query->where('employeur_id', $employeurId);
    }

    protected static function booted()
    {
        static::creating(function ($rapport) {
            if (!$rapport->date_generation) {
                $rapport->date_generation = Carbon::now()->toDateString();
            }
            if (!$rapport->generateur_id && auth()->check()) {
                $rapport->generateur_id = auth()->id();
            }
        });
    }
}
