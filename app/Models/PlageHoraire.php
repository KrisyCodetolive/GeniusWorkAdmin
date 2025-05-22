<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;
use App\Traits\BelongsToEntreprise;

class PlageHoraire extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'entreprise_id',
        'nom',
        'heure_debut',
        'heure_fin',
        'duree_pause',
        'est_standard',
        'est_flexible',
        'marge_retard',
        'marge_depart',
        'pauses',
        'description',
        'configuration',
        'jours_travail',
        'statut'
    ];

    protected $casts = [
        'est_standard' => 'boolean',
        'est_flexible' => 'boolean',
        'pauses' => 'json',
        'configuration' => 'json',
        'jours_travail' => 'json',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime'
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function jours()
    {
        return $this->hasMany(Jour::class);
    }

    public function employes()
    {
        return $this->hasManyThrough(Employeur::class, Jour::class, 'plage_horaire_id', 'id', 'id', 'employeur_id');
    }

    public function permutationsDepart()
    {
        return $this->hasMany(Permutation::class, 'plage_horaire_1_id');
    }

    public function permutationsArrivee()
    {
        return $this->hasMany(Permutation::class, 'plage_horaire_2_id');
    }

    // Scopes
    public function scopeStandard($query)
    {
        return $query->where('est_standard', true);
    }

    public function scopeFlexible($query)
    {
        return $query->where('est_flexible', true);
    }

    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    public function scopeInactif($query)
    {
        return $query->where('statut', 'inactif');
    }

    public function scopeParDuree($query, $minutes, $operateur = '=')
    {
        return $query->whereRaw("TIME_TO_SEC(TIMEDIFF(heure_fin, heure_debut))/60 {$operateur} ?", [$minutes]);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->where(function($q) use ($debut, $fin) {
            $q->where('heure_debut', '>=', $debut)
              ->where('heure_debut', '<=', $fin);
        })->orWhere(function($q) use ($debut, $fin) {
            $q->where('heure_fin', '>=', $debut)
              ->where('heure_fin', '<=', $fin);
        });
    }

    public function scopeChevauche($query, $debut, $fin)
    {
        return $query->where(function($q) use ($debut, $fin) {
            $q->where('heure_debut', '<=', $debut)
              ->where('heure_fin', '>=', $debut);
        })->orWhere(function($q) use ($debut, $fin) {
            $q->where('heure_debut', '<=', $fin)
              ->where('heure_fin', '>=', $fin);
        })->orWhere(function($q) use ($debut, $fin) {
            $q->where('heure_debut', '>=', $debut)
              ->where('heure_fin', '<=', $fin);
        });
    }

    // Helpers
    public function getDureeMinutes()
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return 0;
        }
        
        $debut = Carbon::parse($this->heure_debut);
        $fin = Carbon::parse($this->heure_fin);
        
        return $debut->diffInMinutes($fin);
    }

    public function getDureeFormatee()
    {
        $minutes = $this->getDureeMinutes();
        $heures = floor($minutes / 60);
        $minutesRestantes = $minutes % 60;
        
        return sprintf('%02d:%02d', $heures, $minutesRestantes);
    }

    public function getHeureDebutFormatee($format = 'H:i')
    {
        return $this->heure_debut ? Carbon::parse($this->heure_debut)->format($format) : null;
    }

    public function getHeureFinFormatee($format = 'H:i')
    {
        return $this->heure_fin ? Carbon::parse($this->heure_fin)->format($format) : null;
    }

    public function getPlageFormatee($format = 'H:i')
    {
        return $this->getHeureDebutFormatee($format) . ' - ' . $this->getHeureFinFormatee($format);
    }

    public function estChevauche(Carbon $debut, Carbon $fin)
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return false;
        }

        // Normaliser les heures pour comparer uniquement les heures/minutes/secondes
        $debutNormalise = Carbon::today()->setTimeFrom($debut);
        $finNormalise = Carbon::today()->setTimeFrom($fin);
        $heureDebutNormalise = Carbon::today()->setTimeFrom(Carbon::parse($this->heure_debut));
        $heureFinNormalise = Carbon::today()->setTimeFrom(Carbon::parse($this->heure_fin));

        return ($heureDebutNormalise <= $debutNormalise && $heureFinNormalise > $debutNormalise) ||
               ($heureDebutNormalise < $finNormalise && $heureFinNormalise >= $finNormalise) ||
               ($heureDebutNormalise >= $debutNormalise && $heureFinNormalise <= $finNormalise);
    }

    public function estDansPlage(Carbon $dateTime)
    {
        if (!$this->heure_debut || !$this->heure_fin) {
            return false;
        }

        // Normaliser les heures pour comparer uniquement les heures/minutes/secondes
        $timeNormalise = Carbon::today()->setTimeFrom($dateTime);
        $heureDebutNormalise = Carbon::today()->setTimeFrom(Carbon::parse($this->heure_debut));
        $heureFinNormalise = Carbon::today()->setTimeFrom(Carbon::parse($this->heure_fin));

        return $timeNormalise >= $heureDebutNormalise && $timeNormalise <= $heureFinNormalise;
    }

    public function getJoursTravailCount()
    {
        $joursTravail = $this->jours_travail ?? [];
        if (is_string($joursTravail)) {
            $joursTravail = json_decode($joursTravail, true) ?? [];
        }
        return count(array_filter($joursTravail, fn($jour) => $jour['est_travaille'] ?? false));
    }
    
    public function getEmployesCount()
    {
        return $this->jours()->distinct('employeur_id')->count('employeur_id');
    }
    
    public function assignerAEmploye($employeurId, $jourSemaine, $entrepriseId = null)
    {
        if (!$entrepriseId) {
            $entrepriseId = $this->entreprise_id;
        }
        
        // Vérifier si l'association n'existe pas déjà
        $jourExistant = Jour::where('employeur_id', $employeurId)
            ->where('plage_horaire_id', $this->id)
            ->where('jour_semaine', $jourSemaine)
            ->first();
        
        if (!$jourExistant) {
            // Récupérer les informations du jour de travail correspondant
            $joursTravail = $this->jours_travail ?? [];
            if (is_string($joursTravail)) {
                $joursTravail = json_decode($joursTravail, true) ?? [];
            }
            
            $jourTravailInfo = null;
            foreach ($joursTravail as $jour) {
                if (isset($jour['jour_semaine']) && $jour['jour_semaine'] === $jourSemaine) {
                    $jourTravailInfo = $jour;
                    break;
                }
            }
            
            return Jour::create([
                'employeur_id' => $employeurId,
                'plage_horaire_id' => $this->id,
                'jour_semaine' => $jourSemaine,
                'entreprise_id' => $entrepriseId,
                'est_travaille' => $jourTravailInfo['est_travaille'] ?? true,
            ]);
        }
        
        return $jourExistant;
    }

    public function fusionnerAvec(PlageHoraire $autrePlage)
    {
        // Déterminer la plage horaire englobante
        $heureDebut = Carbon::parse($this->heure_debut)->lt(Carbon::parse($autrePlage->heure_debut)) ? 
            $this->heure_debut : $autrePlage->heure_debut;
        
        $heureFin = Carbon::parse($this->heure_fin)->gt(Carbon::parse($autrePlage->heure_fin)) ? 
            $this->heure_fin : $autrePlage->heure_fin;
        
        // Mettre à jour cette plage horaire
        $this->update([
            'heure_debut' => $heureDebut,
            'heure_fin' => $heureFin,
            'description' => $this->description . ' (fusionné avec ' . $autrePlage->nom . ')'
        ]);
        
        // Mettre à jour les références dans les jours
        $autrePlage->jours()->update(['plage_horaire_id' => $this->id]);
        
        // Supprimer l'autre plage
        $autrePlage->delete();
        
        return $this;
    }
    
    public function assignerAuxEmployes(array $employeurIds)
    {
        foreach ($employeurIds as $employeurId) {
            // Créer une entrée dans la table Jour pour chaque employé
            Jour::create([
                'employeur_id' => $employeurId,
                'plage_horaire_id' => $this->id,
                'est_travaille' => true,
            ]);
        }
    }
    
    /**
     * Récupère les informations d'un jour de travail spécifique
     */
    public function getJourTravailInfo($jourSemaine)
    {
        $joursTravail = $this->jours_travail ?? [];
        if (is_string($joursTravail)) {
            $joursTravail = json_decode($joursTravail, true) ?? [];
        }
        
        foreach ($joursTravail as $jour) {
            if (isset($jour['jour_semaine']) && $jour['jour_semaine'] === $jourSemaine) {
                return $jour;
            }
        }
        
        // Valeur par défaut si le jour n'est pas trouvé
        return [
            'jour_semaine' => $jourSemaine,
            'est_travaille' => in_array($jourSemaine, ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi']),
            'est_ferie' => false,
            'heure_debut' => $this->heure_debut,
            'heure_fin' => $this->heure_fin,
        ];
    }
}
