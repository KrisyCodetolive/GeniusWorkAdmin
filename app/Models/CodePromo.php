<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodePromo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'description',
        'reduction',
        'date_debut',
        'date_expiration',
        'nombre_utilisations_max',
        'nombre_utilisations',
        'actif',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_expiration' => 'datetime',
        'reduction' => 'decimal:2',
        'actif' => 'boolean',
    ];

    /**
     * Vérifie si le code promo est valide à la date actuelle
     *
     * @return bool
     */
    public function estValide(): bool
    {
        // Vérifier si le code est actif
        if (!$this->actif) {
            return false;
        }

        $now = now();

        // Vérifier si le code est dans sa période de validité
        if ($now->lt($this->date_debut) || $now->gt($this->date_expiration)) {
            return false;
        }

        // Vérifier si le code n'a pas dépassé son nombre maximum d'utilisations
        if ($this->nombre_utilisations_max !== null && $this->nombre_utilisations >= $this->nombre_utilisations_max) {
            return false;
        }

        return true;
    }

    /**
     * Incrémente le compteur d'utilisations du code promo
     *
     * @return void
     */
    public function incrementerUtilisations(): void
    {
        $this->nombre_utilisations++;
        $this->save();
    }

    /**
     * Relation avec les abonnements qui ont utilisé ce code promo
     */
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }
}
