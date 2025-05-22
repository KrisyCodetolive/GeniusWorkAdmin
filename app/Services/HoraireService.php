<?php

namespace App\Services;

use App\Models\PlageHoraire;
use App\Models\Jour;
use App\Models\JourTravail;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HoraireService
{
    /**
     * Crée une nouvelle plage horaire
     *
     * @param array $data
     * @return PlageHoraire
     */
    public function creerPlageHoraire(array $data): PlageHoraire
    {
        return PlageHoraire::create($data);
    }

    /**
     * Met à jour une plage horaire existante
     *
     * @param PlageHoraire $plageHoraire
     * @param array $data
     * @return PlageHoraire
     */
    public function mettreAJourPlageHoraire(PlageHoraire $plageHoraire, array $data): PlageHoraire
    {
        $plageHoraire->update($data);
        return $plageHoraire;
    }

    /**
     * Supprime une plage horaire
     *
     * @param PlageHoraire $plageHoraire
     * @return bool
     */
    public function supprimerPlageHoraire(PlageHoraire $plageHoraire): bool
    {
        // Vérifier si la plage horaire est utilisée
        if ($plageHoraire->jours()->count() > 0) {
            throw new \Exception('Cette plage horaire est utilisée par des employés et ne peut pas être supprimée.');
        }

        return $plageHoraire->delete();
    }

    /**
     * Récupère toutes les plages horaires
     *
     * @return Collection
     */
    public function toutesLesPlagesHoraires(): Collection
    {
        return PlageHoraire::orderBy('heure_debut')->get();
    }

    /**
     * Récupère les plages horaires par type
     *
     * @param string $type
     * @return Collection
     */
    public function plagesHorairesParType(string $type): Collection
    {
        return PlageHoraire::where('type', $type)->orderBy('heure_debut')->get();
    }

    /**
     * Récupère les plages horaires de pause
     *
     * @return Collection
     */
    public function plagesHorairesDePause(): Collection
    {
        return PlageHoraire::pause()->orderBy('heure_debut')->get();
    }

    /**
     * Vérifie si une plage horaire chevauche d'autres plages existantes
     *
     * @param Carbon $heureDebut
     * @param Carbon $heureFin
     * @param string|null $plageHoraireId
     * @return bool
     */
    public function verifierChevauchement(Carbon $heureDebut, Carbon $heureFin, ?string $plageHoraireId = null): bool
    {
        $query = PlageHoraire::chevauche($heureDebut, $heureFin);
        
        if ($plageHoraireId) {
            $query->where('id', '!=', $plageHoraireId);
        }
        
        return $query->exists();
    }

    /**
     * Fusionne deux plages horaires
     *
     * @param PlageHoraire $plageHoraire1
     * @param PlageHoraire $plageHoraire2
     * @return PlageHoraire
     */
    public function fusionnerPlagesHoraires(PlageHoraire $plageHoraire1, PlageHoraire $plageHoraire2): PlageHoraire
    {
        return $plageHoraire1->fusionnerAvec($plageHoraire2);
    }

    /**
     * Attribue une plage horaire à un employé pour un jour spécifique
     *
     * @param Employeur $employeur
     * @param JourTravail $jourTravail
     * @param PlageHoraire $plageHoraire
     * @param bool $estTravaille
     * @param string|null $commentaire
     * @return Jour
     */
    public function attribuerPlageHoraire(
        Employeur $employeur, 
        JourTravail $jourTravail, 
        PlageHoraire $plageHoraire, 
        bool $estTravaille = true, 
        ?string $commentaire = null
    ): Jour {
        // Vérifier si un jour existe déjà pour cet employé et ce jour de travail
        $jour = Jour::where('employeur_id', $employeur->id)
            ->where('jour_travail_id', $jourTravail->id)
            ->first();
        
        if ($jour) {
            // Mettre à jour le jour existant
            $jour->update([
                'plage_horaire_id' => $plageHoraire->id,
                'est_travaille' => $estTravaille,
                'commentaire' => $commentaire
            ]);
        } else {
            // Créer un nouveau jour
            $jour = Jour::create([
                'employeur_id' => $employeur->id,
                'jour_travail_id' => $jourTravail->id,
                'plage_horaire_id' => $plageHoraire->id,
                'est_travaille' => $estTravaille,
                'commentaire' => $commentaire
            ]);
        }
        
        return $jour;
    }

    /**
     * Génère un planning hebdomadaire pour un employé
     *
     * @param Employeur $employeur
     * @param Carbon $debutSemaine
     * @return array
     */
    public function genererPlanningHebdomadaire(Employeur $employeur, Carbon $debutSemaine): array
    {
        $planning = [];
        $debutSemaine = $debutSemaine->startOfWeek(); // Lundi
        
        for ($i = 0; $i < 7; $i++) {
            $date = $debutSemaine->copy()->addDays($i);
            $jourSemaine = $date->format('l'); // Monday, Tuesday, etc.
            
            // Trouver le jour de travail correspondant
            $jourTravail = JourTravail::parJour($jourSemaine)->first();
            
            if (!$jourTravail) {
                // Créer un jour de travail s'il n'existe pas
                $jourTravail = JourTravail::create([
                    'jour' => $jourSemaine,
                    'est_travaille' => $i < 5, // Du lundi au vendredi par défaut
                    'description' => ''
                ]);
            }
            
            // Trouver le jour pour cet employé
            $jour = Jour::where('employeur_id', $employeur->id)
                ->where('jour_travail_id', $jourTravail->id)
                ->first();
            
            $planning[$i] = [
                'date' => $date->format('Y-m-d'),
                'jour_semaine' => $jourSemaine,
                'jour_semaine_fr' => $jourTravail->getJourFr(),
                'est_travaille' => $jour ? $jour->est_travaille : $jourTravail->est_travaille,
                'plage_horaire' => $jour ? $jour->plageHoraire : null,
                'commentaire' => $jour ? $jour->commentaire : null,
                'jour_id' => $jour ? $jour->id : null,
                'jour_travail_id' => $jourTravail->id
            ];
        }
        
        return $planning;
    }

    /**
     * Récupère les statistiques d'horaires pour un employé
     *
     * @param Employeur $employeur
     * @param Carbon $debut
     * @param Carbon $fin
     * @return array
     */
    public function statistiquesHoraires(Employeur $employeur, Carbon $debut, Carbon $fin): array
    {
        $jours = Jour::where('employeur_id', $employeur->id)
            ->whereHas('jourTravail', function ($query) use ($debut, $fin) {
                $query->whereBetween('jour', [
                    $debut->format('l'),
                    $fin->format('l')
                ]);
            })
            ->with('plageHoraire')
            ->get();
        
        $totalMinutes = 0;
        $joursTravailes = 0;
        
        foreach ($jours as $jour) {
            if ($jour->est_travaille && $jour->plageHoraire) {
                $totalMinutes += $jour->getDuree();
                $joursTravailes++;
            }
        }
        
        $totalHeures = round($totalMinutes / 60, 2);
        
        return [
            'total_heures' => $totalHeures,
            'jours_travailles' => $joursTravailes,
            'moyenne_heures_par_jour' => $joursTravailes > 0 ? round($totalHeures / $joursTravailes, 2) : 0
        ];
    }

    /**
     * Copie le planning d'un employé vers un autre
     *
     * @param Employeur $source
     * @param Employeur $destination
     * @return int Nombre de jours copiés
     */
    public function copierPlanning(Employeur $source, Employeur $destination): int
    {
        $joursSource = Jour::where('employeur_id', $source->id)->get();
        $count = 0;
        
        foreach ($joursSource as $jourSource) {
            // Vérifier si un jour existe déjà pour cet employé et ce jour de travail
            $jourDestination = Jour::where('employeur_id', $destination->id)
                ->where('jour_travail_id', $jourSource->jour_travail_id)
                ->first();
            
            if ($jourDestination) {
                // Mettre à jour le jour existant
                $jourDestination->update([
                    'plage_horaire_id' => $jourSource->plage_horaire_id,
                    'est_travaille' => $jourSource->est_travaille,
                    'commentaire' => $jourSource->commentaire
                ]);
            } else {
                // Créer un nouveau jour
                Jour::create([
                    'employeur_id' => $destination->id,
                    'jour_travail_id' => $jourSource->jour_travail_id,
                    'plage_horaire_id' => $jourSource->plage_horaire_id,
                    'est_travaille' => $jourSource->est_travaille,
                    'commentaire' => $jourSource->commentaire
                ]);
            }
            
            $count++;
        }
        
        return $count;
    }

    /**
     * Applique un modèle de planning à un groupe d'employés
     *
     * @param array $plagesHoraires Format: ['Monday' => plageHoraireId, 'Tuesday' => plageHoraireId, ...]
     * @param array $employeurIds Liste des IDs d'employés
     * @return int Nombre d'employés mis à jour
     */
    public function appliquerModele(array $plagesHoraires, array $employeurIds): int
    {
        $count = 0;
        
        foreach ($employeurIds as $employeurId) {
            $employeur = Employeur::find($employeurId);
            
            if (!$employeur) {
                continue;
            }
            
            foreach ($plagesHoraires as $jour => $plageHoraireId) {
                $jourTravail = JourTravail::parJour($jour)->first();
                
                if (!$jourTravail || !$plageHoraireId) {
                    continue;
                }
                
                $plageHoraire = PlageHoraire::find($plageHoraireId);
                
                if (!$plageHoraire) {
                    continue;
                }
                
                $this->attribuerPlageHoraire(
                    $employeur,
                    $jourTravail,
                    $plageHoraire,
                    true
                );
            }
            
            $count++;
        }
        
        return $count;
    }
}
