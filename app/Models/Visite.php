<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class Visite extends Model
{
    use HasFactory, SoftDeletes, BelongsToEntreprise, HasUuids;
    
    // La génération d'UUID est maintenant gérée par le trait HasUuids

    /**
     * Les attributs qui sont mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'visiteur_id',
        'entreprise_id',
        'site_id',
        'date_arrivee',
        'date_depart',
        'motif_visite',
        'personne_a_rencontrer',
        'departement_a_visiter',
        'commentaires',
        'statut',
        'badge_visiteur'
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_arrivee' => 'datetime',
        'date_depart' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Les relations qui doivent être chargées automatiquement.
     *
     * @var array
     */
    protected $with = ['visiteur'];

    /**
     * Obtenir le visiteur associé à la visite.
     */
    public function visiteur()
    {
        return $this->belongsTo(Visiteur::class, 'visiteur_id', 'id');
    }

    /**
     * Obtenir l'entreprise associée à la visite.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class, 'entreprise_id', 'id');
    }

    /**
     * Obtenir le site associé à la visite.
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    /**
     * Vérifie si la visite est en cours.
     */
    public function estEnCours()
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Vérifie si la visite est terminée.
     */
    public function estTerminee()
    {
        return $this->statut === 'terminee';
    }

    /**
     * Vérifie si la visite est annulée.
     */
    public function estAnnulee()
    {
        return $this->statut === 'annulee';
    }

    /**
     * Termine la visite.
     */
    public function terminer()
    {
        $this->date_depart = now();
        $this->statut = 'terminee';
        return $this->save();
    }

    /**
     * Annule la visite.
     */
    public function annuler()
    {
        $this->statut = 'annulee';
        return $this->save();
    }

    /**
     * Scope pour filtrer les visites en cours.
     */
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    /**
     * Scope pour filtrer les visites terminées.
     */
    public function scopeTerminees($query)
    {
        return $query->where('statut', 'terminee');
    }

    /**
     * Scope pour filtrer les visites annulées.
     */
    public function scopeAnnulees($query)
    {
        return $query->where('statut', 'annulee');
    }

    /**
     * Scope pour filtrer les visites par entreprise.
     */
    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    /**
     * Scope pour filtrer les visites par site.
     */
    public function scopeParSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }

    /**
     * Scope pour filtrer les visites par visiteur.
     */
    public function scopeParVisiteur($query, $visiteurId)
    {
        return $query->where('visiteur_id', $visiteurId);
    }

    /**
     * Scope pour filtrer les visites par date.
     */
    public function scopeParDate($query, $date)
    {
        return $query->whereDate('date_arrivee', $date);
    }

    /**
     * Scope pour filtrer les visites par période.
     */
    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_arrivee', [$debut, $fin]);
    }
}
