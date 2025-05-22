<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Abonnement;
use App\Models\Entreprise;

class PlanAbonnement extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'nom',
        'description',
        'prix_mensuel',
        'prix_annuel',
        'duree_essai',
        'nombre_employes_min',
        'nombre_employes_max',
        'cout_par_employe',
        'fonctionnalites',
        'statut',
        'devise',
        'priorite',
        'periode_facturation'
    ];

    protected $casts = [
        'fonctionnalites' => 'json',
        'prix_mensuel' => 'decimal:2',
        'prix_annuel' => 'decimal:2',
        'cout_par_employe' => 'decimal:2'
    ];

    // Relations
    public function abonnements()
    {
        return $this->hasMany(Abonnement::class);
    }

    public function entreprisesActives()
    {
        return $this->hasManyThrough(
            Entreprise::class,
            Abonnement::class,
            'plan_abonnement_id',
            'id',
            'id',
            'entreprise_id'
        )->where('abonnements.statut', 'actif');
    }

    // Scopes
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopeParPriorite($query)
    {
        return $query->orderBy('priorite');
    }

    // Helpers
    public function hasFonctionnalite($fonctionnalite)
    {
        return in_array($fonctionnalite, $this->fonctionnalites);
    }

    public function getPrixMensuelFormate()
    {
        return number_format($this->prix_mensuel, 2) . ' ' . $this->devise;
    }

    public function getPrixAnnuelFormate()
    {
        return number_format($this->prix_annuel, 2) . ' ' . $this->devise;
    }

    public function getEconomieAnnuelle()
    {
        return ($this->prix_mensuel * 12) - $this->prix_annuel;
    }

    public function isAdapteForEmployeCount($count)
    {
        return $count >= $this->nombre_employes_min && $count <= $this->nombre_employes_max;
    }
}
