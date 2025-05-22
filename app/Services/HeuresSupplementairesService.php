<?php

namespace App\Services;

use App\Models\Supplementaire;
use App\Models\Presence;
use App\Models\PlageHoraire;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HeuresSupplementairesService
{
    /**
     * Vérifie et enregistre automatiquement les heures supplémentaires lors d'un pointage de sortie
     *
     * @param Presence $presence Le pointage de sortie
     * @return Supplementaire|null L'heure supplémentaire créée ou null si aucune heure supplémentaire
     */
    public function traiterHeuresSupplementaires(Presence $presence): ?Supplementaire
    {
        // Vérifier que c'est un pointage de sortie
        if ($presence->type !== 'sortie') {
            return null;
        }

        // Récupérer l'employé et sa plage horaire pour ce jour
        $employe = $presence->employeur;
        $plageHoraire = $this->getPlageHoraireEmploye($employe, $presence->date_heure);

        if (!$plageHoraire) {
            return null; // Pas de plage horaire définie pour cet employé à cette date
        }

        // Calculer le dépassement d'horaire
        $minutesSupplementaires = $this->calculerMinutesSupplementaires($presence, $plageHoraire);

        // Si pas de dépassement, ne rien faire
        if ($minutesSupplementaires <= 0) {
            return null;
        }

        // Créer l'enregistrement d'heures supplémentaires
        return $this->creerHeureSupplementaire($employe, $presence, $plageHoraire, $minutesSupplementaires);
    }

    /**
     * Récupère la plage horaire d'un employé pour une date donnée
     *
     * @param Employeur $employe L'employé
     * @param Carbon $date La date du pointage
     * @return PlageHoraire|null La plage horaire ou null si non trouvée
     */
    protected function getPlageHoraireEmploye(Employeur $employe, Carbon $date): ?PlageHoraire
    {
        // Récupérer la plage horaire assignée à l'employé pour cette date
        // Cette logique peut varier selon la structure de votre application
        
        // Récupérer le jour de la semaine (1 = lundi, 7 = dimanche)
        $jourSemaine = $date->dayOfWeekIso;
        
        // Trouver le jour de travail correspondant pour cet employé
        $jourTravail = $employe->joursTravail()->where('jour', $jourSemaine)->first();
        
        if (!$jourTravail) {
            return null;
        }
        
        // Récupérer la dernière plage horaire de la journée (celle qui se termine le plus tard)
        return $jourTravail->plagesHoraires()
            ->where('est_pause', false)
            ->orderBy('heure_fin', 'desc')
            ->first();
    }

    /**
     * Calcule le nombre de minutes supplémentaires effectuées
     *
     * @param Presence $presence Le pointage de sortie
     * @param PlageHoraire $plageHoraire La plage horaire de référence
     * @return int Le nombre de minutes supplémentaires (0 si pas de dépassement)
     */
    protected function calculerMinutesSupplementaires(Presence $presence, PlageHoraire $plageHoraire): int
    {
        // Récupérer l'heure de fin prévue
        $heureFinPrevue = Carbon::parse($presence->date_heure->format('Y-m-d') . ' ' . $plageHoraire->heure_fin);
        
        // Récupérer l'heure de sortie réelle
        $heureSortieReelle = $presence->date_heure;
        
        // Calculer la différence en minutes
        $differenceMinutes = $heureSortieReelle->diffInMinutes($heureFinPrevue, false);
        
        // Si la différence est positive, l'employé est sorti après l'heure prévue
        return max(0, $differenceMinutes);
    }

    /**
     * Crée un enregistrement d'heures supplémentaires
     *
     * @param Employeur $employe L'employé concerné
     * @param Presence $presence Le pointage de sortie
     * @param PlageHoraire $plageHoraire La plage horaire de référence
     * @param int $minutesSupplementaires Le nombre de minutes supplémentaires
     * @return Supplementaire L'enregistrement créé
     */
    protected function creerHeureSupplementaire(
        Employeur $employe, 
        Presence $presence, 
        PlageHoraire $plageHoraire, 
        int $minutesSupplementaires
    ): Supplementaire {
        // Créer l'enregistrement d'heures supplémentaires
        return Supplementaire::create([
            'id' => (string) Str::uuid(),
            'employeur_id' => $employe->id,
            'date' => $presence->date_heure->format('Y-m-d'),
            'heure_debut' => $plageHoraire->heure_fin,
            'heure_fin' => $presence->date_heure->format('H:i:s'),
            'duree_minutes' => $minutesSupplementaires,
            'motif' => 'Dépassement automatique de la plage horaire',
            'statut' => 'en_attente',
            'presence_id' => $presence->id,
            'plage_horaire_id' => $plageHoraire->id,
        ]);
    }

    /**
     * Valide une heure supplémentaire
     *
     * @param Supplementaire $supplementaire L'heure supplémentaire à valider
     * @param string $validateurId L'ID du validateur
     * @param string|null $commentaire Un commentaire optionnel
     * @return bool Succès de l'opération
     */
    public function validerHeureSupplementaire(
        Supplementaire $supplementaire, 
        string $validateurId, 
        ?string $commentaire = null
    ): bool {
        if ($supplementaire->statut !== 'en_attente') {
            return false;
        }

        $supplementaire->statut = 'approuve';
        $supplementaire->validateur_id = $validateurId;
        $supplementaire->date_validation = now();
        
        if ($commentaire) {
            $supplementaire->commentaire = $commentaire;
        }

        return $supplementaire->save();
    }

    /**
     * Rejette une heure supplémentaire
     *
     * @param Supplementaire $supplementaire L'heure supplémentaire à rejeter
     * @param string $validateurId L'ID du validateur
     * @param string $commentaire Un commentaire expliquant le rejet
     * @return bool Succès de l'opération
     */
    public function rejeterHeureSupplementaire(
        Supplementaire $supplementaire, 
        string $validateurId, 
        string $commentaire
    ): bool {
        if ($supplementaire->statut !== 'en_attente') {
            return false;
        }

        $supplementaire->statut = 'rejete';
        $supplementaire->validateur_id = $validateurId;
        $supplementaire->date_validation = now();
        $supplementaire->commentaire = $commentaire;

        return $supplementaire->save();
    }

    /**
     * Récupère le total des heures supplémentaires validées pour un employé sur une période
     *
     * @param Employeur $employe L'employé concerné
     * @param Carbon $dateDebut Date de début de la période
     * @param Carbon $dateFin Date de fin de la période
     * @return int Le total des minutes supplémentaires validées
     */
    public function getTotalMinutesSupplementaires(
        Employeur $employe, 
        Carbon $dateDebut, 
        Carbon $dateFin
    ): int {
        return Supplementaire::where('employeur_id', $employe->id)
            ->where('statut', 'approuve')
            ->whereBetween('date', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
            ->sum('duree_minutes');
    }
}
