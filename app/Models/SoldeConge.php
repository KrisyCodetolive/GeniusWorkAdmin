<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SoldeConge extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'employeur_id',
        'type_conge_id',
        'annee',
        'solde_initial',
        'solde_acquis',
        'solde_pris',
        'solde_restant',
        'date_derniere_maj',
        'commentaire',
        'meta_donnees'
    ];

    protected $casts = [
        'annee' => 'integer',
        'solde_initial' => 'decimal:2',
        'solde_acquis' => 'decimal:2',
        'solde_pris' => 'decimal:2',
        'solde_restant' => 'decimal:2',
        'date_derniere_maj' => 'datetime',
        'meta_donnees' => 'json'
    ];

    // Relations
    public function employeur()
    {
        return $this->belongsTo(Employeur::class);
    }

    public function typeConge()
    {
        return $this->belongsTo(TypeConge::class);
    }

    // Scopes
    public function scopeParAnnee($query, $annee)
    {
        return $query->where('annee', $annee);
    }

    public function scopeParTypeConge($query, $typeCongeId)
    {
        return $query->where('type_conge_id', $typeCongeId);
    }

    public function scopeAvecSoldePositif($query)
    {
        return $query->where('solde_restant', '>', 0);
    }

    // Helpers
    public function ajouterSolde($jours, $commentaire = null)
    {
        $this->solde_acquis += $jours;
        $this->solde_restant += $jours;
        $this->date_derniere_maj = now();
        $this->commentaire = $commentaire ? $this->commentaire . "\n" . $commentaire : $this->commentaire;
        $this->save();

        return $this;
    }

    public function deduireSolde($jours, $commentaire = null)
    {
        if ($this->solde_restant < $jours) {
            throw new \Exception("Solde insuffisant pour ce type de congé");
        }

        $this->solde_pris += $jours;
        $this->solde_restant -= $jours;
        $this->date_derniere_maj = now();
        $this->commentaire = $commentaire ? $this->commentaire . "\n" . $commentaire : $this->commentaire;
        $this->save();

        return $this;
    }

    public function reinitialiserSolde($nouveauSolde, $commentaire = null)
    {
        $this->solde_initial = $nouveauSolde;
        $this->solde_acquis = $nouveauSolde;
        $this->solde_pris = 0;
        $this->solde_restant = $nouveauSolde;
        $this->date_derniere_maj = now();
        $this->commentaire = $commentaire ? $commentaire . "\n" . $this->commentaire : $this->commentaire;
        $this->save();

        return $this;
    }

    public function getSoldeDisponible()
    {
        return $this->solde_restant;
    }

    public function getSoldePris()
    {
        return $this->solde_pris;
    }

    public function getSoldeTotal()
    {
        return $this->solde_initial + $this->solde_acquis;
    }

    public function verifierDisponibilite($jours)
    {
        return $this->solde_restant >= $jours;
    }
}
