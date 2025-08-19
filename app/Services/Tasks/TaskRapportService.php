<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskAssignation;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\Employeur;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskRapportService
{
    /**
     * Génère un rapport global des tâches pour une entreprise
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @param Carbon $dateDebut La date de début
     * @param Carbon $dateFin La date de fin
     * @return array Le rapport
     */
    public function genererRapportGlobal(string $entrepriseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        // Récupérer toutes les tâches de la période
        $taches = Task::where('entreprise_id', $entrepriseId)
            ->whereBetween('date_debut', [$dateDebut, $dateFin])
            ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
            ->get();
            
        // Statistiques globales
        $statistiques = [
            'total' => $taches->count(),
            'terminees' => $taches->where('statut', Task::STATUT_TERMINE)->count(),
            'en_cours' => $taches->where('statut', Task::STATUT_EN_COURS)->count(),
            'en_attente' => $taches->where('statut', Task::STATUT_EN_ATTENTE)->count(),
            'en_retard' => $taches->where('statut', Task::STATUT_EN_RETARD)->count(),
            'annulees' => $taches->where('statut', Task::STATUT_ANNULE)->count(),
            'taux_completion' => $taches->count() > 0 
                ? round(($taches->where('statut', Task::STATUT_TERMINE)->count() / $taches->count()) * 100, 2)
                : 0,
        ];
        
        // Statistiques par type
        $statistiquesParType = [];
        $types = $taches->pluck('type')->unique();
        
        foreach ($types as $type) {
            $tachesType = $taches->where('type', $type);
            $statistiquesParType[$type] = [
                'total' => $tachesType->count(),
                'terminees' => $tachesType->where('statut', Task::STATUT_TERMINE)->count(),
                'en_cours' => $tachesType->where('statut', Task::STATUT_EN_COURS)->count(),
                'en_attente' => $tachesType->where('statut', Task::STATUT_EN_ATTENTE)->count(),
                'en_retard' => $tachesType->where('statut', Task::STATUT_EN_RETARD)->count(),
                'annulees' => $tachesType->where('statut', Task::STATUT_ANNULE)->count(),
                'taux_completion' => $tachesType->count() > 0 
                    ? round(($tachesType->where('statut', Task::STATUT_TERMINE)->count() / $tachesType->count()) * 100, 2)
                    : 0,
            ];
        }
        
        // Statistiques par priorité
        $statistiquesParPriorite = [];
        $priorites = $taches->pluck('priorite')->unique();
        
        foreach ($priorites as $priorite) {
            $tachesPriorite = $taches->where('priorite', $priorite);
            $statistiquesParPriorite[$priorite] = [
                'total' => $tachesPriorite->count(),
                'terminees' => $tachesPriorite->where('statut', Task::STATUT_TERMINE)->count(),
                'en_cours' => $tachesPriorite->where('statut', Task::STATUT_EN_COURS)->count(),
                'en_attente' => $tachesPriorite->where('statut', Task::STATUT_EN_ATTENTE)->count(),
                'en_retard' => $tachesPriorite->where('statut', Task::STATUT_EN_RETARD)->count(),
                'annulees' => $tachesPriorite->where('statut', Task::STATUT_ANNULE)->count(),
                'taux_completion' => $tachesPriorite->count() > 0 
                    ? round(($tachesPriorite->where('statut', Task::STATUT_TERMINE)->count() / $tachesPriorite->count()) * 100, 2)
                    : 0,
            ];
        }
        
        // Statistiques par département
        $statistiquesParDepartement = [];
        $assignationsParDepartement = TaskAssignation::where('entreprise_id', $entrepriseId)
            ->where('assignable_type', Departement::class)
            ->whereHas('task', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin]);
            })
            ->get()
            ->groupBy('assignable_id');
            
        foreach ($assignationsParDepartement as $departementId => $assignations) {
            $departement = Departement::find($departementId);
            if ($departement) {
                $statistiquesParDepartement[$departementId] = [
                    'nom' => $departement->nom,
                    'total' => $assignations->count(),
                    'terminees' => $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
                    'en_cours' => $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
                    'en_attente' => $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
                    'en_retard' => $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
                    'annulees' => $assignations->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
                    'taux_completion' => $assignations->count() > 0 
                        ? round(($assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignations->count()) * 100, 2)
                        : 0,
                ];
            }
        }
        
        // Statistiques par équipe
        $statistiquesParEquipe = [];
        $assignationsParEquipe = TaskAssignation::where('entreprise_id', $entrepriseId)
            ->where('assignable_type', Equipe::class)
            ->whereHas('task', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin]);
            })
            ->get()
            ->groupBy('assignable_id');
            
        foreach ($assignationsParEquipe as $equipeId => $assignations) {
            $equipe = Equipe::find($equipeId);
            if ($equipe) {
                $statistiquesParEquipe[$equipeId] = [
                    'nom' => $equipe->nom,
                    'total' => $assignations->count(),
                    'terminees' => $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
                    'en_cours' => $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
                    'en_attente' => $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
                    'en_retard' => $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
                    'annulees' => $assignations->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
                    'taux_completion' => $assignations->count() > 0 
                        ? round(($assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignations->count()) * 100, 2)
                        : 0,
                ];
            }
        }
        
        // Top 10 des employés les plus performants (taux de complétion)
        $topEmployes = [];
        $assignationsParEmploye = TaskAssignation::where('entreprise_id', $entrepriseId)
            ->whereHas('task', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin]);
            })
            ->get()
            ->groupBy('employeur_id');
            
        $performanceEmployes = [];
        
        foreach ($assignationsParEmploye as $employeId => $assignations) {
            $employe = Employeur::find($employeId);
            if ($employe && $assignations->count() >= 5) { // Minimum 5 tâches pour être considéré
                $tauxCompletion = $assignations->count() > 0 
                    ? ($assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignations->count()) * 100
                    : 0;
                    
                $performanceEmployes[] = [
                    'id' => $employe->id,
                    'nom' => $employe->nom_complet,
                    'total_taches' => $assignations->count(),
                    'taches_terminees' => $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
                    'taux_completion' => round($tauxCompletion, 2),
                ];
            }
        }
        
        // Trier par taux de complétion décroissant
        usort($performanceEmployes, function ($a, $b) {
            return $b['taux_completion'] <=> $a['taux_completion'];
        });
        
        // Prendre les 10 premiers
        $topEmployes = array_slice($performanceEmployes, 0, 10);
        
        // Construire le rapport final
        return [
            'periode' => [
                'debut' => $dateDebut->toDateString(),
                'fin' => $dateFin->toDateString(),
            ],
            'statistiques_globales' => $statistiques,
            'statistiques_par_type' => $statistiquesParType,
            'statistiques_par_priorite' => $statistiquesParPriorite,
            'statistiques_par_departement' => $statistiquesParDepartement,
            'statistiques_par_equipe' => $statistiquesParEquipe,
            'top_employes' => $topEmployes,
        ];
    }
    
    /**
     * Génère un rapport détaillé pour un département
     *
     * @param Departement $departement Le département
     * @param Carbon $dateDebut La date de début
     * @param Carbon $dateFin La date de fin
     * @return array Le rapport
     */
    public function genererRapportDepartement(Departement $departement, Carbon $dateDebut, Carbon $dateFin): array
    {
        // Récupérer toutes les assignations du département
        $assignations = TaskAssignation::where('assignable_type', Departement::class)
            ->where('assignable_id', $departement->id)
            ->whereHas('task', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin]);
            })
            ->get();
            
        // Statistiques globales
        $statistiques = [
            'total' => $assignations->count(),
            'terminees' => $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
            'en_cours' => $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
            'en_attente' => $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
            'en_retard' => $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
            'annulees' => $assignations->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
            'taux_completion' => $assignations->count() > 0 
                ? round(($assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignations->count()) * 100, 2)
                : 0,
        ];
        
        // Statistiques par employé
        $statistiquesParEmploye = [];
        $assignationsParEmploye = $assignations->groupBy('employeur_id');
        
        foreach ($assignationsParEmploye as $employeId => $assignationsEmploye) {
            $employe = Employeur::find($employeId);
            if ($employe) {
                $statistiquesParEmploye[$employeId] = [
                    'nom' => $employe->nom_complet,
                    'total' => $assignationsEmploye->count(),
                    'terminees' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
                    'en_cours' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
                    'en_attente' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
                    'en_retard' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
                    'annulees' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
                    'taux_completion' => $assignationsEmploye->count() > 0 
                        ? round(($assignationsEmploye->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignationsEmploye->count()) * 100, 2)
                        : 0,
                ];
            }
        }
        
        // Statistiques par type de tâche
        $statistiquesParType = [];
        $taches = $assignations->pluck('task')->unique('id');
        $typesUniques = $taches->pluck('type')->unique();
        
        foreach ($typesUniques as $type) {
            $tachesType = $taches->where('type', $type);
            $assignationsType = $assignations->filter(function ($assignation) use ($tachesType) {
                return $tachesType->contains('id', $assignation->task_id);
            });
            
            $statistiquesParType[$type] = [
                'total' => $assignationsType->count(),
                'terminees' => $assignationsType->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
                'en_cours' => $assignationsType->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
                'en_attente' => $assignationsType->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
                'en_retard' => $assignationsType->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
                'annulees' => $assignationsType->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
                'taux_completion' => $assignationsType->count() > 0 
                    ? round(($assignationsType->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignationsType->count()) * 100, 2)
                    : 0,
            ];
        }
        
        // Construire le rapport final
        return [
            'departement' => [
                'id' => $departement->id,
                'nom' => $departement->nom,
                'code' => $departement->code,
                'responsable' => $departement->responsable ? $departement->responsable->nom_complet : null,
            ],
            'periode' => [
                'debut' => $dateDebut->toDateString(),
                'fin' => $dateFin->toDateString(),
            ],
            'statistiques_globales' => $statistiques,
            'statistiques_par_employe' => $statistiquesParEmploye,
            'statistiques_par_type' => $statistiquesParType,
        ];
    }
    
    /**
     * Génère un rapport détaillé pour une équipe
     *
     * @param Equipe $equipe L'équipe
     * @param Carbon $dateDebut La date de début
     * @param Carbon $dateFin La date de fin
     * @return array Le rapport
     */
    public function genererRapportEquipe(Equipe $equipe, Carbon $dateDebut, Carbon $dateFin): array
    {
        // Récupérer toutes les assignations de l'équipe
        $assignations = TaskAssignation::where('assignable_type', Equipe::class)
            ->where('assignable_id', $equipe->id)
            ->whereHas('task', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin]);
            })
            ->get();
            
        // Statistiques globales
        $statistiques = [
            'total' => $assignations->count(),
            'terminees' => $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
            'en_cours' => $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
            'en_attente' => $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
            'en_retard' => $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
            'annulees' => $assignations->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
            'taux_completion' => $assignations->count() > 0 
                ? round(($assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignations->count()) * 100, 2)
                : 0,
        ];
        
        // Statistiques par membre de l'équipe
        $statistiquesParMembre = [];
        $assignationsParEmploye = $assignations->groupBy('employeur_id');
        
        foreach ($assignationsParEmploye as $employeId => $assignationsEmploye) {
            $employe = Employeur::find($employeId);
            if ($employe) {
                $statistiquesParMembre[$employeId] = [
                    'nom' => $employe->nom_complet,
                    'total' => $assignationsEmploye->count(),
                    'terminees' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
                    'en_cours' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
                    'en_attente' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
                    'en_retard' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
                    'annulees' => $assignationsEmploye->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
                    'taux_completion' => $assignationsEmploye->count() > 0 
                        ? round(($assignationsEmploye->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignationsEmploye->count()) * 100, 2)
                        : 0,
                ];
            }
        }
        
        // Construire le rapport final
        return [
            'equipe' => [
                'id' => $equipe->id,
                'nom' => $equipe->nom,
                'responsable' => $equipe->responsable ? $equipe->responsable->nom_complet : null,
                'nombre_membres' => $equipe->membres()->count(),
            ],
            'periode' => [
                'debut' => $dateDebut->toDateString(),
                'fin' => $dateFin->toDateString(),
            ],
            'statistiques_globales' => $statistiques,
            'statistiques_par_membre' => $statistiquesParMembre,
        ];
    }
    
    /**
     * Génère un rapport détaillé pour un employé
     *
     * @param Employeur $employe L'employé
     * @param Carbon $dateDebut La date de début
     * @param Carbon $dateFin La date de fin
     * @return array Le rapport
     */
    public function genererRapportEmploye(Employeur $employe, Carbon $dateDebut, Carbon $dateFin): array
    {
        // Récupérer toutes les assignations de l'employé
        $assignations = TaskAssignation::where('employeur_id', $employe->id)
            ->whereHas('task', function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                    ->orWhereBetween('date_fin', [$dateDebut, $dateFin]);
            })
            ->get();
            
        // Statistiques globales
        $statistiques = [
            'total' => $assignations->count(),
            'terminees' => $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count(),
            'en_cours' => $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count(),
            'en_attente' => $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count(),
            'en_retard' => $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count(),
            'annulees' => $assignations->where('statut', TaskAssignation::STATUT_ANNULE)->count(),
            'taux_completion' => $assignations->count() > 0 
                ? round(($assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count() / $assignations->count()) * 100, 2)
                : 0,
            'temps_moyen_completion' => $this->calculerTempsMoyenCompletion($assignations),
        ];
        
        // Statistiques par source d'assignation
        $statistiquesParSource = [
            'departement' => [
                'total' => 0,
                'terminees' => 0,
                'en_cours' => 0,
                'en_attente' => 0,
                'en_retard' => 0,
                'annulees' => 0,
                'taux_completion' => 0,
            ],
            'equipe' => [
                'total' => 0,
                'terminees' => 0,
                'en_cours' => 0,
                'en_attente' => 0,
                'en_retard' => 0,
                'annulees' => 0,
                'taux_completion' => 0,
            ],
            'individuelle' => [
                'total' => 0,
                'terminees' => 0,
                'en_cours' => 0,
                'en_attente' => 0,
                'en_retard' => 0,
                'annulees' => 0,
                'taux_completion' => 0,
            ],
            'globale' => [
                'total' => 0,
                'terminees' => 0,
                'en_cours' => 0,
                'en_attente' => 0,
                'en_retard' => 0,
                'annulees' => 0,
                'taux_completion' => 0,
            ],
        ];
        
        foreach ($assignations as $assignation) {
            $source = 'individuelle';
            
            if ($assignation->assignable_type === Departement::class) {
                $source = 'departement';
            } elseif ($assignation->assignable_type === Equipe::class) {
                $source = 'equipe';
            } elseif ($assignation->assignable_type === null && $assignation->assignable_id === null) {
                $source = 'globale';
            }
            
            $statistiquesParSource[$source]['total']++;
            
            switch ($assignation->statut) {
                case TaskAssignation::STATUT_TERMINE:
                    $statistiquesParSource[$source]['terminees']++;
                    break;
                case TaskAssignation::STATUT_EN_COURS:
                    $statistiquesParSource[$source]['en_cours']++;
                    break;
                case TaskAssignation::STATUT_EN_ATTENTE:
                    $statistiquesParSource[$source]['en_attente']++;
                    break;
                case TaskAssignation::STATUT_EN_RETARD:
                    $statistiquesParSource[$source]['en_retard']++;
                    break;
                case TaskAssignation::STATUT_ANNULE:
                    $statistiquesParSource[$source]['annulees']++;
                    break;
            }
        }
        
        // Calculer les taux de complétion par source
        foreach ($statistiquesParSource as $source => &$stats) {
            $stats['taux_completion'] = $stats['total'] > 0 
                ? round(($stats['terminees'] / $stats['total']) * 100, 2)
                : 0;
        }
        
        // Construire le rapport final
        return [
            'employe' => [
                'id' => $employe->id,
                'nom' => $employe->nom_complet,
                'departement' => $employe->departement ? $employe->departement->nom : null,
                'poste' => $employe->poste,
            ],
            'periode' => [
                'debut' => $dateDebut->toDateString(),
                'fin' => $dateFin->toDateString(),
            ],
            'statistiques_globales' => $statistiques,
            'statistiques_par_source' => $statistiquesParSource,
        ];
    }
    
    /**
     * Calcule le temps moyen de complétion des tâches
     *
     * @param Collection $assignations Collection d'assignations
     * @return float|null Le temps moyen en heures ou null si aucune donnée
     */
    protected function calculerTempsMoyenCompletion(Collection $assignations): ?float
    {
        $assignationsTerminees = $assignations->where('statut', TaskAssignation::STATUT_TERMINE)
            ->filter(function ($assignation) {
                return $assignation->date_debut_reelle && $assignation->date_fin_reelle;
            });
            
        if ($assignationsTerminees->isEmpty()) {
            return null;
        }
        
        $totalHeures = 0;
        
        foreach ($assignationsTerminees as $assignation) {
            $debut = Carbon::parse($assignation->date_debut_reelle);
            $fin = Carbon::parse($assignation->date_fin_reelle);
            $totalHeures += $debut->diffInHours($fin);
        }
        
        return round($totalHeures / $assignationsTerminees->count(), 2);
    }
    
    /**
     * Exporte les données de rapport au format Excel
     *
     * @param array $rapport Les données du rapport
     * @return string Le chemin vers le fichier Excel généré
     */
    public function exporterRapportExcel(array $rapport): string
    {
        // Cette méthode serait implémentée avec une bibliothèque comme PhpSpreadsheet
        // Pour l'instant, nous allons simplement simuler l'export
        
        return 'chemin/vers/rapport_' . time() . '.xlsx';
    }
}
