<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class EntrepriseScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // N'appliquer le scope que si l'utilisateur est authentifié et a une entreprise associée
        // Mais ne pas l'appliquer pour les SuperAdmin et Support qui ont accès à toutes les données
        if (Auth::check() && Auth::user()->entreprise_id 
            && !Auth::user()->isSuperAdmin() 
            && !Auth::user()->isSupport()) {
            $builder->where($model->getTable() . '.entreprise_id', Auth::user()->entreprise_id);
        }
    }
}
