<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskAssignation;
use App\Models\Entreprise;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TaskRoutineService
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Génère toutes les tâches routinières pour toutes les entreprises
     *
     * @param Carbon|null $date La date pour laquelle générer les tâches
     * @return array Statistiques des tâches générées par entreprise
     */
    public function genererToutesLesTachesRoutine(?Carbon $date = null): array
    {
        $date = $date ?? Carbon::today();
        $stats = [];
        
        // Récupérer toutes les entreprises
        $entreprises = Entreprise::all();
        
        foreach ($entreprises as $entreprise) {
            try {
                $tachesGenerees = $this->taskService->genererTachesRoutine($entreprise->id, $date);
                $stats[$entreprise->id] = [
                    'entreprise_nom' => $entreprise->nom,
                    'nombre_taches_generees' => $tachesGenerees->count(),
                    'statut' => 'success'
                ];
            } catch (\Exception $e) {
                Log::error('Erreur lors de la génération des tâches routines pour l\'entreprise', [
                    'message' => $e->getMessage(),
                    'entreprise_id' => $entreprise->id,
                    'entreprise_nom' => $entreprise->nom
                ]);
                
                $stats[$entreprise->id] = [
                    'entreprise_nom' => $entreprise->nom,
                    'nombre_taches_generees' => 0,
                    'statut' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $stats;
    }
    
    /**
     * Crée une nouvelle tâche de routine
     *
     * @param array $data Les données de la tâche
     * @return Task La tâche créée
     */
    public function creerTacheRoutine(array $data): Task
    {
        // S'assurer que c'est une tâche de routine
        $data['est_routine'] = true;
        
        // Valider les données de routine
        $this->validerDonneesRoutine($data);
        
        // Créer la tâche
        return $this->taskService->creerTask($data);
    }
    
    /**
     * Valide les données de routine
     *
     * @param array $data Les données à valider
     * @throws \InvalidArgumentException Si les données sont invalides
     */
    protected function validerDonneesRoutine(array &$data): void
    {
        // Vérifier que la fréquence est valide
        if (!isset($data['frequence_routine']) || !in_array($data['frequence_routine'], [
            Task::FREQUENCE_QUOTIDIENNE,
            Task::FREQUENCE_HEBDOMADAIRE,
            Task::FREQUENCE_MENSUELLE
        ])) {
            throw new \InvalidArgumentException('La fréquence de routine est invalide ou manquante');
        }
        
        // Configurer les jours de routine selon la fréquence
        if ($data['frequence_routine'] === Task::FREQUENCE_QUOTIDIENNE) {
            // Pour les tâches quotidiennes, tous les jours sont inclus
            $data['jour_routine'] = [0, 1, 2, 3, 4, 5, 6]; // Dimanche à Samedi
        } elseif ($data['frequence_routine'] === Task::FREQUENCE_HEBDOMADAIRE) {
            // Pour les tâches hebdomadaires, vérifier que les jours de la semaine sont spécifiés
            if (!isset($data['jour_routine']) || !is_array($data['jour_routine']) || empty($data['jour_routine'])) {
                throw new \InvalidArgumentException('Les jours de la semaine doivent être spécifiés pour une tâche hebdomadaire');
            }
            
            // Valider que tous les jours sont entre 0 et 6
            foreach ($data['jour_routine'] as $jour) {
                if (!is_int($jour) || $jour < 0 || $jour > 6) {
                    throw new \InvalidArgumentException('Les jours de la semaine doivent être des entiers entre 0 (Dimanche) et 6 (Samedi)');
                }
            }
        } elseif ($data['frequence_routine'] === Task::FREQUENCE_MENSUELLE) {
            // Pour les tâches mensuelles, vérifier que les jours du mois sont spécifiés
            if (!isset($data['jour_routine']) || !is_array($data['jour_routine']) || empty($data['jour_routine'])) {
                throw new \InvalidArgumentException('Les jours du mois doivent être spécifiés pour une tâche mensuelle');
            }
            
            // Valider que tous les jours sont entre 1 et 31
            foreach ($data['jour_routine'] as $jour) {
                if (!is_int($jour) || $jour < 1 || $jour > 31) {
                    throw new \InvalidArgumentException('Les jours du mois doivent être des entiers entre 1 et 31');
                }
            }
        }
    }
    
    /**
     * Met à jour une tâche de routine existante
     *
     * @param Task $task La tâche à mettre à jour
     * @param array $data Les nouvelles données
     * @return Task La tâche mise à jour
     */
    public function mettreAJourTacheRoutine(Task $task, array $data): Task
    {
        // Vérifier que c'est bien une tâche de routine
        if (!$task->est_routine) {
            throw new \InvalidArgumentException('Cette tâche n\'est pas une tâche de routine');
        }
        
        // S'assurer que est_routine reste à true
        $data['est_routine'] = true;
        
        // Valider les données de routine si elles sont modifiées
        if (isset($data['frequence_routine']) || isset($data['jour_routine'])) {
            // Fusionner les données existantes avec les nouvelles pour la validation
            $dataComplete = array_merge($task->toArray(), $data);
            $this->validerDonneesRoutine($dataComplete);
            
            // Mettre à jour avec les données validées
            $data['frequence_routine'] = $dataComplete['frequence_routine'];
            $data['jour_routine'] = $dataComplete['jour_routine'];
        }
        
        // Mettre à jour la tâche
        $task->update($data);
        
        return $task;
    }
    
    /**
     * Désactive une tâche de routine
     *
     * @param Task $task La tâche à désactiver
     * @return Task La tâche mise à jour
     */
    public function desactiverTacheRoutine(Task $task): Task
    {
        // Vérifier que c'est bien une tâche de routine
        if (!$task->est_routine) {
            throw new \InvalidArgumentException('Cette tâche n\'est pas une tâche de routine');
        }
        
        // Marquer la tâche comme annulée
        $task->statut = Task::STATUT_ANNULE;
        $task->save();
        
        return $task;
    }
    
    /**
     * Réactive une tâche de routine
     *
     * @param Task $task La tâche à réactiver
     * @return Task La tâche mise à jour
     */
    public function reactiverTacheRoutine(Task $task): Task
    {
        // Vérifier que c'est bien une tâche de routine
        if (!$task->est_routine) {
            throw new \InvalidArgumentException('Cette tâche n\'est pas une tâche de routine');
        }
        
        // Marquer la tâche comme en attente
        $task->statut = Task::STATUT_EN_ATTENTE;
        $task->save();
        
        return $task;
    }
    
    /**
     * Obtient toutes les instances générées à partir d'une tâche de routine
     *
     * @param Task $tacheRoutine La tâche de routine
     * @return Collection Les instances générées
     */
    public function getInstancesTacheRoutine(Task $tacheRoutine): Collection
    {
        // Vérifier que c'est bien une tâche de routine
        if (!$tacheRoutine->est_routine) {
            throw new \InvalidArgumentException('Cette tâche n\'est pas une tâche de routine');
        }
        
        // Récupérer toutes les tâches générées à partir de cette routine
        return Task::where('entreprise_id', $tacheRoutine->entreprise_id)
            ->where('meta_donnees->tache_routine_id', $tacheRoutine->id)
            ->orderBy('date_debut', 'desc')
            ->get();
    }
    
    /**
     * Génère un rapport des tâches de routine pour une période donnée
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @param Carbon $dateDebut La date de début
     * @param Carbon $dateFin La date de fin
     * @return array Le rapport
     */
    public function genererRapportTachesRoutine(string $entrepriseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        // Récupérer toutes les tâches de routine
        $tachesRoutine = Task::where('entreprise_id', $entrepriseId)
            ->where('est_routine', true)
            ->get();
            
        $rapport = [
            'periode' => [
                'debut' => $dateDebut->toDateString(),
                'fin' => $dateFin->toDateString(),
            ],
            'nombre_taches_routine' => $tachesRoutine->count(),
            'taches_generees' => [],
            'statistiques' => [
                'total_generees' => 0,
                'terminees' => 0,
                'en_cours' => 0,
                'en_attente' => 0,
                'en_retard' => 0,
                'annulees' => 0,
            ],
        ];
        
        // Pour chaque tâche de routine, récupérer les instances générées dans la période
        foreach ($tachesRoutine as $tacheRoutine) {
            $instances = Task::where('entreprise_id', $entrepriseId)
                ->where('meta_donnees->tache_routine_id', $tacheRoutine->id)
                ->whereBetween('date_debut', [$dateDebut, $dateFin])
                ->get();
                
            $rapport['taches_generees'][$tacheRoutine->id] = [
                'titre' => $tacheRoutine->titre,
                'frequence' => $tacheRoutine->frequence_routine,
                'nombre_instances' => $instances->count(),
                'statuts' => [
                    'terminees' => $instances->where('statut', Task::STATUT_TERMINE)->count(),
                    'en_cours' => $instances->where('statut', Task::STATUT_EN_COURS)->count(),
                    'en_attente' => $instances->where('statut', Task::STATUT_EN_ATTENTE)->count(),
                    'en_retard' => $instances->where('statut', Task::STATUT_EN_RETARD)->count(),
                    'annulees' => $instances->where('statut', Task::STATUT_ANNULE)->count(),
                ],
            ];
            
            // Mettre à jour les statistiques globales
            $rapport['statistiques']['total_generees'] += $instances->count();
            $rapport['statistiques']['terminees'] += $instances->where('statut', Task::STATUT_TERMINE)->count();
            $rapport['statistiques']['en_cours'] += $instances->where('statut', Task::STATUT_EN_COURS)->count();
            $rapport['statistiques']['en_attente'] += $instances->where('statut', Task::STATUT_EN_ATTENTE)->count();
            $rapport['statistiques']['en_retard'] += $instances->where('statut', Task::STATUT_EN_RETARD)->count();
            $rapport['statistiques']['annulees'] += $instances->where('statut', Task::STATUT_ANNULE)->count();
        }
        
        // Calculer le taux de complétion
        $rapport['statistiques']['taux_completion'] = $rapport['statistiques']['total_generees'] > 0
            ? round(($rapport['statistiques']['terminees'] / $rapport['statistiques']['total_generees']) * 100, 2)
            : 0;
            
        return $rapport;
    }
}
