<?php

namespace App\Traits;

use App\Models\Entreprise;
use App\Scopes\EntrepriseScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToEntreprise
{
    /**
     * Boot the trait.
     * 
     * @return void
     */
    protected static function bootBelongsToEntreprise()
    {
        static::addGlobalScope(new EntrepriseScope);
    }

    /**
     * Get the entreprise that owns the model.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Scope a query to only include records for the current user's entreprise.
     * Les SuperAdmin et Support ont accès à toutes les données.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrentEntreprise(Builder $query)
    {
        if (Auth::check() && Auth::user()->entreprise_id 
            && !Auth::user()->isSuperAdmin() 
            && !Auth::user()->isSupport()) {
            return $query->where('entreprise_id', Auth::user()->entreprise_id);
        }
        
        return $query;
    }

    /**
     * Scope a query to only include records for a specific entreprise.
     * Les SuperAdmin et Support peuvent spécifier une entreprise spécifique si nécessaire.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $entrepriseId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntreprise(Builder $query, $entrepriseId)
    {
        // Si l'utilisateur n'est pas SuperAdmin ou Support, on force l'utilisation de son entreprise
        if (Auth::check() && !Auth::user()->isSuperAdmin() && !Auth::user()->isSupport()) {
            return $query->where('entreprise_id', Auth::user()->entreprise_id);
        }
        
        // Sinon, on utilise l'entreprise spécifiée
        return $query->where('entreprise_id', $entrepriseId);
    }
}
