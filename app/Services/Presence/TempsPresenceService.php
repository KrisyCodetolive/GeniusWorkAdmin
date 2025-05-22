<?php

namespace App\Services\Presence;

use App\Models\Presence;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\Site;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TempsPresenceService
{
    /**
     * Calcule le temps de travail pour chaque employé d'une entreprise sur une période donnée
     *
     * @param Carbon $dateDebut Date de début de la période
     * @param Carbon $dateFin Date de fin de la période
     * @param int $entrepriseId ID de l'entreprise
     * @param int|null $siteId ID du site (optionnel)
     * @return Collection Collection d'objets contenant les données de temps de travail par employé
     */
    public function calculerTempsPresenceParEmploye(Carbon $dateDebut, Carbon $dateFin, int $entrepriseId, ?int $siteId = null): Collection
    {
        // Récupérer les présences sur la période
        $query = Presence::query()
            ->whereHas('employeur', function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_heure_sortie', [$dateDebut, $dateFin])
                    ->orWhere(function ($query) use ($dateDebut, $dateFin) {
                        $query->where('date_heure_entree', '<', $dateDebut)
                            ->where('date_heure_sortie', '>', $dateFin);
                    });
            })
            ->whereNotNull('date_heure_entree')
            ->whereNotNull('date_heure_sortie')
            ->where('statut', '!=', 'annule');
        
        // Filtrer par site si spécifié
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        // Récupérer les présences avec les relations nécessaires
        $presences = $query->with(['employeur', 'site'])->get();
        
        // Regrouper les présences par employé
        $presencesParEmploye = $presences->groupBy('employeur_id');
        
        // Récupérer tous les employés actifs de l'entreprise
        $employesQuery = Employeur::where('entreprise_id', $entrepriseId)
            ->where('statut', 'actif');
        
        // Filtrer par site si spécifié
        if ($siteId) {
            $employesQuery->where('site_id', $siteId);
        }
        
        $employes = $employesQuery->get();
        
        // Préparer les résultats
        $resultats = collect();
        
        foreach ($employes as $employe) {
            $presencesEmploye = $presencesParEmploye->get($employe->id, collect());
            
            // Calculer le temps total
            $tempsTotal = $this->calculerTempsTotal($presencesEmploye);
            
            // Calculer le temps moyen par jour
            $nombreJours = $this->calculerNombreJoursTravailles($presencesEmploye);
            $tempsMoyenParJour = $nombreJours > 0 ? $tempsTotal / $nombreJours : 0;
            
            // Calculer les retards
            $tempsRetard = $this->calculerTempsRetard($presencesEmploye);
            
            // Calculer les heures supplémentaires
            $heuresSupplementaires = $this->calculerHeuresSupplementaires($presencesEmploye, $employe);
            
            // Ajouter les résultats à la collection
            $resultats->push([
                'employe' => $employe,
                'temps_total_minutes' => $tempsTotal,
                'temps_total_formate' => $this->formaterTemps($tempsTotal),
                'jours_travailles' => $nombreJours,
                'temps_moyen_par_jour_minutes' => $tempsMoyenParJour,
                'temps_moyen_par_jour_formate' => $this->formaterTemps($tempsMoyenParJour),
                'retard_total_minutes' => $tempsRetard,
                'retard_total_formate' => $this->formaterTemps($tempsRetard),
                'heures_supplementaires_minutes' => $heuresSupplementaires,
                'heures_supplementaires_formate' => $this->formaterTemps($heuresSupplementaires),
                'presences' => $presencesEmploye,
                'presences_par_jour' => $this->regrouperPresencesParJour($presencesEmploye),
            ]);
        }
        
        // Trier par temps total décroissant
        return $resultats->sortByDesc('temps_total_minutes')->values();
    }
    
    /**
     * Calcule les statistiques globales de temps de présence pour une entreprise
     *
     * @param Carbon $dateDebut Date de début de la période
     * @param Carbon $dateFin Date de fin de la période
     * @param int $entrepriseId ID de l'entreprise
     * @param int|null $siteId ID du site (optionnel)
     * @return array Tableau des statistiques
     */
    public function calculerStatistiquesTempsPresence(Carbon $dateDebut, Carbon $dateFin, int $entrepriseId, ?int $siteId = null): array
    {
        // Récupérer les données de temps de présence par employé
        $donneesEmployes = $this->calculerTempsPresenceParEmploye($dateDebut, $dateFin, $entrepriseId, $siteId);
        
        // Calculer le nombre total d'employés
        $totalEmployes = $donneesEmployes->count();
        
        // Calculer le temps total de présence
        $tempsTotalMinutes = $donneesEmployes->sum('temps_total_minutes');
        
        // Calculer le temps moyen par employé
        $tempsMoyenParEmployeMinutes = $totalEmployes > 0 ? $tempsTotalMinutes / $totalEmployes : 0;
        
        // Calculer le temps total de retard
        $tempsRetardTotalMinutes = $donneesEmployes->sum('retard_total_minutes');
        
        // Calculer le temps total d'heures supplémentaires
        $heuresSupplementairesTotalMinutes = $donneesEmployes->sum('heures_supplementaires_minutes');
        
        // Calculer le nombre total de jours travaillés
        $joursTravaillesTotal = $donneesEmployes->sum('jours_travailles');
        
        // Calculer le nombre moyen de jours travaillés par employé
        $joursTravaillesMoyenParEmploye = $totalEmployes > 0 ? $joursTravaillesTotal / $totalEmployes : 0;
        
        // Calculer le temps moyen par jour travaillé
        $tempsMoyenParJourMinutes = $joursTravaillesTotal > 0 ? $tempsTotalMinutes / $joursTravaillesTotal : 0;
        
        // Préparer les statistiques par jour de la semaine
        $statParJourSemaine = $this->calculerStatistiquesParJourSemaine($donneesEmployes);
        
        // Préparer les statistiques par site
        $statParSite = $this->calculerStatistiquesParSite($donneesEmployes);
        
        // Retourner les statistiques
        return [
            'total_employes' => $totalEmployes,
            'temps_total_minutes' => $tempsTotalMinutes,
            'temps_total_formate' => $this->formaterTemps($tempsTotalMinutes),
            'temps_moyen_par_employe_minutes' => $tempsMoyenParEmployeMinutes,
            'temps_moyen_par_employe_formate' => $this->formaterTemps($tempsMoyenParEmployeMinutes),
            'retard_total_minutes' => $tempsRetardTotalMinutes,
            'retard_total_formate' => $this->formaterTemps($tempsRetardTotalMinutes),
            'heures_supplementaires_total_minutes' => $heuresSupplementairesTotalMinutes,
            'heures_supplementaires_total_formate' => $this->formaterTemps($heuresSupplementairesTotalMinutes),
            'jours_travailles_total' => $joursTravaillesTotal,
            'jours_travailles_moyen_par_employe' => round($joursTravaillesMoyenParEmploye, 1),
            'temps_moyen_par_jour_minutes' => $tempsMoyenParJourMinutes,
            'temps_moyen_par_jour_formate' => $this->formaterTemps($tempsMoyenParJourMinutes),
            'statistiques_par_jour_semaine' => $statParJourSemaine,
            'statistiques_par_site' => $statParSite,
            'employes' => $donneesEmployes,
        ];
    }
    
    /**
     * Calcule le temps total de présence pour une collection de présences
     *
     * @param Collection $presences Collection de présences
     * @return int Temps total en minutes
     */
    private function calculerTempsTotal(Collection $presences): int
    {
        return $presences->sum(function ($presence) {
            // Si la durée effective est déjà calculée, l'utiliser
            if (!empty($presence->duree_effective)) {
                return $presence->duree_effective;
            }
            
            // Sinon, calculer la durée à partir des dates d'entrée et de sortie
            if ($presence->date_heure_entree && $presence->date_heure_sortie) {
                return $presence->date_heure_entree->diffInMinutes($presence->date_heure_sortie);
            }
            
            return 0;
        });
    }
    

    
    /**
     * Récupère les informations de présence pour un employé spécifique sur une période donnée
     *
     * @param Carbon $dateDebut Date de début de la période
     * @param Carbon $dateFin Date de fin de la période
     * @param string $employeurId ID de l'employeur
     * @param string $entrepriseId ID de l'entreprise
     * @return array Données de présence pour l'employé
     */
    public function getPresenceDataForEmploye(Carbon $dateDebut, Carbon $dateFin, string $employeurId, string $entrepriseId): array
    {
        // Récupérer les présences sur la période pour cet employé spécifique
        $query = Presence::query()
            ->where('employeur_id', $employeurId)
            ->whereHas('employeur', function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_heure_sortie', [$dateDebut, $dateFin])
                    ->orWhere(function ($query) use ($dateDebut, $dateFin) {
                        $query->where('date_heure_entree', '<', $dateDebut)
                            ->where('date_heure_sortie', '>', $dateFin);
                    });
            })
            ->whereNotNull('date_heure_entree')
            ->whereNotNull('date_heure_sortie')
            ->where('statut', '!=', 'annule');
        
        $presences = $query->with(['employeur', 'site'])->get();
        
        // Si aucune présence n'est trouvée, retourner des valeurs par défaut
        if ($presences->isEmpty()) {
            return [
                'temps_total' => '0h 0m',
                'jours_travailles' => 0,
                'temps_moyen_par_jour' => '0h 0m',
                'retard_total' => '0h 0m',
                'heures_supplementaires' => '0h 0m'
            ];
        }
        
        // Récupérer l'employé
        $employe = \App\Models\Employeur::find($employeurId);
        
        if (!$employe) {
            return [
                'temps_total' => '0h 0m',
                'jours_travailles' => 0,
                'temps_moyen_par_jour' => '0h 0m',
                'retard_total' => '0h 0m',
                'heures_supplementaires' => '0h 0m'
            ];
        }
        
        // Calculer le temps total
        $tempsTotal = $this->calculerTempsTotal($presences);
        
        // Calculer le nombre de jours travaillés
        $nombreJours = $this->calculerNombreJoursTravailles($presences);
        
        // Calculer le temps moyen par jour
        $tempsMoyenParJour = $nombreJours > 0 ? $tempsTotal / $nombreJours : 0;
        
        // Calculer les retards
        $tempsRetard = $this->calculerTempsRetard($presences);
        
        // Calculer les heures supplémentaires
        $heuresSupplementaires = $this->calculerHeuresSupplementaires($presences, $employe);
        
        // Retourner les données formatées
        return [
            'temps_total' => $this->formaterTemps($tempsTotal),
            'jours_travailles' => $nombreJours,
            'temps_moyen_par_jour' => $this->formaterTemps($tempsMoyenParJour),
            'retard_total' => $this->formaterTemps($tempsRetard),
            'heures_supplementaires' => $this->formaterTemps($heuresSupplementaires)
        ];
    }
    
    /**
     * Calcule le nombre de jours travaillés pour une collection de présences
     *
     * @param Collection $presences Collection de présences
     * @return int Nombre de jours travaillés
     */
    private function calculerNombreJoursTravailles(Collection $presences): int
    {
        // Regrouper les présences par jour
        $presencesParJour = $this->regrouperPresencesParJour($presences);
        
        // Compter le nombre de jours uniques
        return $presencesParJour->count();
    }
    
    /**
     * Regroupe les présences par jour
     *
     * @param Collection $presences Collection de présences
     * @return Collection Collection de présences regroupées par jour
     */
    private function regrouperPresencesParJour(Collection $presences): Collection
    {
        return $presences->groupBy(function ($presence) {
            return $presence->date_heure_entree->format('Y-m-d');
        });
    }
    
    /**
     * Calcule le temps total de retard pour une collection de présences
     *
     * @param Collection $presences Collection de présences
     * @return int Temps total de retard en minutes
     */
    private function calculerTempsRetard(Collection $presences): int
    {
        return $presences->sum(function ($presence) {
            return $presence->retard ?? 0;
        });
    }
    
    /**
     * Calcule les heures supplémentaires pour une collection de présences
     *
     * @param Collection $presences Collection de présences
     * @param Employeur $employe Employé concerné
     * @return int Heures supplémentaires en minutes
     */
    private function calculerHeuresSupplementaires(Collection $presences, Employeur $employe): int
    {
        // Durée standard de travail par jour en minutes (8 heures par défaut)
        $dureeStandardParJour = 8 * 60;
        
        // Récupérer les présences par jour
        $presencesParJour = $this->regrouperPresencesParJour($presences);
        
        // Calculer les heures supplémentaires pour chaque jour
        $heuresSupplementaires = 0;
        
        foreach ($presencesParJour as $jour => $presencesJour) {
            // Calculer le temps total travaillé ce jour
            $tempsTravailleJour = $this->calculerTempsTotal($presencesJour);
            
            // Si le temps travaillé dépasse la durée standard, ajouter aux heures supplémentaires
            if ($tempsTravailleJour > $dureeStandardParJour) {
                $heuresSupplementaires += ($tempsTravailleJour - $dureeStandardParJour);
            }
        }
        
        return $heuresSupplementaires;
    }
    
    /**
     * Calcule les statistiques de temps de présence par jour de la semaine
     *
     * @param Collection $donneesEmployes Données de temps de présence par employé
     * @return array Statistiques par jour de la semaine
     */
    private function calculerStatistiquesParJourSemaine(Collection $donneesEmployes): array
    {
        $statParJour = [
            'Monday' => ['total_minutes' => 0, 'count' => 0],
            'Tuesday' => ['total_minutes' => 0, 'count' => 0],
            'Wednesday' => ['total_minutes' => 0, 'count' => 0],
            'Thursday' => ['total_minutes' => 0, 'count' => 0],
            'Friday' => ['total_minutes' => 0, 'count' => 0],
            'Saturday' => ['total_minutes' => 0, 'count' => 0],
            'Sunday' => ['total_minutes' => 0, 'count' => 0],
        ];
        
        // Parcourir les données de chaque employé
        foreach ($donneesEmployes as $donnees) {
            foreach ($donnees['presences'] as $presence) {
                if ($presence->date_heure_entree && $presence->date_heure_sortie) {
                    $jourSemaine = $presence->date_heure_entree->format('l');
                    $duree = $presence->duree_effective ?? $presence->date_heure_entree->diffInMinutes($presence->date_heure_sortie);
                    
                    $statParJour[$jourSemaine]['total_minutes'] += $duree;
                    $statParJour[$jourSemaine]['count']++;
                }
            }
        }
        
        // Calculer les moyennes et formater les résultats
        $resultat = [];
        
        foreach ($statParJour as $jour => $stat) {
            $moyenne = $stat['count'] > 0 ? $stat['total_minutes'] / $stat['count'] : 0;
            
            $resultat[$jour] = [
                'total_minutes' => $stat['total_minutes'],
                'total_formate' => $this->formaterTemps($stat['total_minutes']),
                'nombre_presences' => $stat['count'],
                'moyenne_minutes' => $moyenne,
                'moyenne_formate' => $this->formaterTemps($moyenne),
                'jour_traduit' => $this->traduireJourSemaine($jour),
            ];
        }
        
        return $resultat;
    }
    
    /**
     * Calcule les statistiques de temps de présence par site
     *
     * @param Collection $donneesEmployes Données de temps de présence par employé
     * @return array Statistiques par site
     */
    private function calculerStatistiquesParSite(Collection $donneesEmployes): array
    {
        $statParSite = [];
        
        // Parcourir les données de chaque employé
        foreach ($donneesEmployes as $donnees) {
            foreach ($donnees['presences'] as $presence) {
                if ($presence->site && $presence->date_heure_entree && $presence->date_heure_sortie) {
                    $siteId = $presence->site->id;
                    $siteNom = $presence->site->nom;
                    $duree = $presence->duree_effective ?? $presence->date_heure_entree->diffInMinutes($presence->date_heure_sortie);
                    
                    if (!isset($statParSite[$siteId])) {
                        $statParSite[$siteId] = [
                            'id' => $siteId,
                            'nom' => $siteNom,
                            'total_minutes' => 0,
                            'count' => 0,
                        ];
                    }
                    
                    $statParSite[$siteId]['total_minutes'] += $duree;
                    $statParSite[$siteId]['count']++;
                }
            }
        }
        
        // Calculer les moyennes et formater les résultats
        $resultat = [];
        
        foreach ($statParSite as $siteId => $stat) {
            $moyenne = $stat['count'] > 0 ? $stat['total_minutes'] / $stat['count'] : 0;
            
            $resultat[$siteId] = [
                'id' => $stat['id'],
                'nom' => $stat['nom'],
                'total_minutes' => $stat['total_minutes'],
                'total_formate' => $this->formaterTemps($stat['total_minutes']),
                'nombre_presences' => $stat['count'],
                'moyenne_minutes' => $moyenne,
                'moyenne_formate' => $this->formaterTemps($moyenne),
            ];
        }
        
        // Trier par temps total décroissant
        return collect($resultat)->sortByDesc('total_minutes')->values()->all();
    }
    
    /**
     * Formate un temps en minutes en une chaîne lisible (HH:MM)
     *
     * @param int $minutes Temps en minutes
     * @return string Temps formaté
     */
    private function formaterTemps(int $minutes): string
    {
        $heures = floor($minutes / 60);
        $minutesRestantes = $minutes % 60;
        
        return sprintf('%02d:%02d', $heures, $minutesRestantes);
    }
    
    /**
     * Traduit le nom d'un jour de la semaine en français
     *
     * @param string $jour Jour en anglais
     * @return string Jour traduit en français
     */
    private function traduireJourSemaine(string $jour): string
    {
        $traductions = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];
        
        return $traductions[$jour] ?? $jour;
    }
    
    /**
     * Génère un rapport détaillé des heures de travail pour une période donnée
     *
     * @param Carbon $dateDebut Date de début de la période
     * @param Carbon $dateFin Date de fin de la période
     * @param int $entrepriseId ID de l'entreprise
     * @param int|null $siteId ID du site (optionnel)
     * @return array Données du rapport
     */
    public function genererRapportHeuresTravail(Carbon $dateDebut, Carbon $dateFin, int $entrepriseId, ?int $siteId = null): array
    {
        // Récupérer l'entreprise
        $entreprise = Entreprise::find($entrepriseId);
        
        // Récupérer le site si spécifié
        $site = $siteId ? Site::find($siteId) : null;
        
        // Calculer les statistiques globales
        $statistiques = $this->calculerStatistiquesTempsPresence($dateDebut, $dateFin, $entrepriseId, $siteId);
        
        // Préparer les données du rapport
        return [
            'entreprise' => $entreprise,
            'site' => $site,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'statistiques' => $statistiques,
        ];
    }
}
