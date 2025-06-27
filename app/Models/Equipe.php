<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\BelongsToEntreprise;

class Equipe extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'description',
        'responsable_id',
        'statut',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'json',
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function responsable()
    {
        return $this->belongsTo(Employeur::class, 'responsable_id');
    }

    public function membres()
    {
        return $this->belongsToMany(Employeur::class, 'equipe_employeur')
            ->withTimestamps()
            ->withPivot(['est_actif', 'date_debut', 'date_fin']);
    }

    public function plagesHoraires()
    {
        return $this->belongsToMany(PlageHoraire::class, 'equipe_plage_horaire')
            ->withTimestamps()
            ->withPivot(['est_actif']);
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

    // Méthodes
    public function getMembresCount()
    {
        return $this->membres()->count();
    }

    public function getPlagesHorairesCount()
    {
        return $this->plagesHoraires()->count();
    }

    /**
     * Assigne une plage horaire à tous les membres de l'équipe
     */
    public function assignerPlageHoraireAuxMembres(PlageHoraire $plageHoraire)
    {
        $membres = $this->membres()->where('equipe_employeur.est_actif', true)->get();
        
        foreach ($membres as $membre) {
            // Assigner pour tous les jours travaillés de la plage horaire
            $joursTravail = $plageHoraire->jours_travail ?? [];
            if (is_string($joursTravail)) {
                $joursTravail = json_decode($joursTravail, true) ?? [];
            }
            
            foreach ($joursTravail as $jour) {
                if (isset($jour['jour_semaine']) && ($jour['est_travaille'] ?? false)) {
                    $plageHoraire->assignerAEmploye($membre->id, $jour['jour_semaine'], $this->entreprise_id);
                }
            }
        }
        
        return true;
    }

    /**
     * Synchronise les horaires pour tous les membres de l'équipe
     */
    public function synchroniserHoraires()
    {
        // Ne récupérer que la plage horaire active la plus récente
        $plageHoraire = $this->plagesHoraires()
            ->where('equipe_plage_horaire.est_actif', true)
            ->orderBy('equipe_plage_horaire.updated_at', 'desc')
            ->first();
        
        if ($plageHoraire) {
            $this->assignerPlageHoraireAuxMembres($plageHoraire);
        }
        
        return true;
    }
}
