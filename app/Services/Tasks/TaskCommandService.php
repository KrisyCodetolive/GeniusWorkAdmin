<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskAssignation;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\Employeur;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskCommandService
{
    protected $taskService;
    protected $taskRoutineService;
    protected $taskNotificationService;

    public function __construct(
        TaskService $taskService,
        TaskRoutineService $taskRoutineService,
        TaskNotificationService $taskNotificationService
    ) {
        $this->taskService = $taskService;
        $this->taskRoutineService = $taskRoutineService;
        $this->taskNotificationService = $taskNotificationService;
    }

    /**
     * Crée une nouvelle tâche et l'assigne selon les paramètres
     *
     * @param array $taskData Les données de la tâche
     * @param array $assignationData Les données d'assignation (type, ids)
     * @param User $user L'utilisateur qui crée la tâche
     * @return array Résultat de l'opération
     */
    public function creerEtAssignerTask(array $taskData, array $assignationData, User $user): array
    {
        DB::beginTransaction();
        
        try {
            // Ajouter l'ID de l'utilisateur comme créateur
            $taskData['createur_id'] = $user->id;
            
            // Créer la tâche
            $task = $this->taskService->creerTask($taskData);
            
            // Assigner la tâche selon le type d'assignation
            $assignations = collect();
            
            switch ($assignationData['type']) {
                case 'departement':
                    $departement = Departement::find($assignationData['id']);
                    if ($departement) {
                        $assignations = $this->taskService->assignerADepartement($task, $departement);
                    }
                    break;
                    
                case 'equipe':
                    $equipe = Equipe::find($assignationData['id']);
                    if ($equipe) {
                        $assignations = $this->taskService->assignerAEquipe($task, $equipe);
                    }
                    break;
                    
                case 'equipes':
                    $equipeIds = $assignationData['ids'] ?? [];
                    $assignations = $this->taskService->assignerAEquipes($task, $equipeIds);
                    break;
                    
                case 'employe':
                    $employe = Employeur::find($assignationData['id']);
                    if ($employe) {
                        $assignation = $this->taskService->assignerAEmploye($task, $employe);
                        $assignations->push($assignation);
                    }
                    break;
                    
                case 'tous':
                    $assignations = $this->taskService->assignerATous($task, $task->entreprise_id);
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Type d'assignation non valide: " . $assignationData['type']);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'task' => $task,
                'assignations' => $assignations,
                'message' => 'Tâche créée et assignée avec succès',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création et assignation de tâche', [
                'message' => $e->getMessage(),
                'task_data' => $taskData,
                'assignation_data' => $assignationData,
                'user_id' => $user->id
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la tâche: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Crée une nouvelle tâche de routine et l'assigne selon les paramètres
     *
     * @param array $taskData Les données de la tâche
     * @param array $assignationData Les données d'assignation (type, ids)
     * @param User $user L'utilisateur qui crée la tâche
     * @return array Résultat de l'opération
     */
    public function creerEtAssignerTaskRoutine(array $taskData, array $assignationData, User $user): array
    {
        DB::beginTransaction();
        
        try {
            // Ajouter l'ID de l'utilisateur comme créateur
            $taskData['createur_id'] = $user->id;
            
            // S'assurer que c'est une tâche de routine
            $taskData['est_routine'] = true;
            
            // Créer la tâche de routine
            $task = $this->taskRoutineService->creerTacheRoutine($taskData);
            
            // Assigner la tâche selon le type d'assignation
            $assignations = collect();
            
            switch ($assignationData['type']) {
                case 'departement':
                    $departement = Departement::find($assignationData['id']);
                    if ($departement) {
                        $assignations = $this->taskService->assignerADepartement($task, $departement);
                    }
                    break;
                    
                case 'equipe':
                    $equipe = Equipe::find($assignationData['id']);
                    if ($equipe) {
                        $assignations = $this->taskService->assignerAEquipe($task, $equipe);
                    }
                    break;
                    
                case 'equipes':
                    $equipeIds = $assignationData['ids'] ?? [];
                    $assignations = $this->taskService->assignerAEquipes($task, $equipeIds);
                    break;
                    
                case 'employe':
                    $employe = Employeur::find($assignationData['id']);
                    if ($employe) {
                        $assignation = $this->taskService->assignerAEmploye($task, $employe);
                        $assignations->push($assignation);
                    }
                    break;
                    
                case 'tous':
                    $assignations = $this->taskService->assignerATous($task, $task->entreprise_id);
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Type d'assignation non valide: " . $assignationData['type']);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'task' => $task,
                'assignations' => $assignations,
                'message' => 'Tâche de routine créée et assignée avec succès',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur lors de la création et assignation de tâche de routine', [
                'message' => $e->getMessage(),
                'task_data' => $taskData,
                'assignation_data' => $assignationData,
                'user_id' => $user->id
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la tâche de routine: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Génère les tâches de routine pour aujourd'hui
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return array Résultat de l'opération
     */
    public function genererTachesRoutineAujourdhui(string $entrepriseId): array
    {
        try {
            $tachesGenerees = $this->taskService->genererTachesRoutine($entrepriseId);
            
            return [
                'success' => true,
                'taches_generees' => $tachesGenerees,
                'nombre' => $tachesGenerees->count(),
                'message' => $tachesGenerees->count() . ' tâches de routine générées avec succès',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des tâches de routine', [
                'message' => $e->getMessage(),
                'entreprise_id' => $entrepriseId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la génération des tâches de routine: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Met à jour le statut des tâches en retard
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return array Résultat de l'opération
     */
    public function mettreAJourTachesEnRetard(string $entrepriseId): array
    {
        try {
            $nombreMisesAJour = $this->taskService->mettreAJourTachesEnRetard($entrepriseId);
            
            return [
                'success' => true,
                'nombre_mises_a_jour' => $nombreMisesAJour,
                'message' => $nombreMisesAJour . ' tâches marquées comme en retard',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des tâches en retard', [
                'message' => $e->getMessage(),
                'entreprise_id' => $entrepriseId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des tâches en retard: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Envoie des notifications pour les tâches en retard
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return array Résultat de l'opération
     */
    public function notifierTachesEnRetard(string $entrepriseId): array
    {
        try {
            $nombreNotifications = $this->taskNotificationService->notifierTachesEnRetard($entrepriseId);
            
            return [
                'success' => true,
                'nombre_notifications' => $nombreNotifications,
                'message' => $nombreNotifications . ' notifications envoyées pour les tâches en retard',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications pour les tâches en retard', [
                'message' => $e->getMessage(),
                'entreprise_id' => $entrepriseId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi des notifications: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Marque une tâche comme terminée
     *
     * @param string $taskId L'ID de la tâche
     * @param User $user L'utilisateur qui effectue l'action
     * @return array Résultat de l'opération
     */
    public function marquerTacheTerminee(string $taskId, User $user): array
    {
        try {
            $task = Task::findOrFail($taskId);
            
            // Vérifier si l'utilisateur a le droit de modifier cette tâche
            // Cette vérification dépendrait de votre logique d'autorisation
            
            $task->marquerCommeTermine();
            
            return [
                'success' => true,
                'task' => $task,
                'message' => 'Tâche marquée comme terminée avec succès',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage de la tâche comme terminée', [
                'message' => $e->getMessage(),
                'task_id' => $taskId,
                'user_id' => $user->id
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors du marquage de la tâche: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Marque une assignation de tâche comme terminée
     *
     * @param string $assignationId L'ID de l'assignation
     * @param User $user L'utilisateur qui effectue l'action
     * @return array Résultat de l'opération
     */
    public function marquerAssignationTerminee(string $assignationId, User $user): array
    {
        try {
            $assignation = TaskAssignation::findOrFail($assignationId);
            
            // Vérifier si l'utilisateur a le droit de modifier cette assignation
            // Cette vérification dépendrait de votre logique d'autorisation
            
            $assignation->marquerCommeTermine();
            
            // Vérifier si toutes les assignations de cette tâche sont terminées
            $task = $assignation->task;
            $toutesTerminees = $task->assignations()
                ->where('statut', '!=', TaskAssignation::STATUT_TERMINE)
                ->where('statut', '!=', TaskAssignation::STATUT_ANNULE)
                ->count() === 0;
                
            if ($toutesTerminees) {
                $task->marquerCommeTermine();
            }
            
            return [
                'success' => true,
                'assignation' => $assignation,
                'task_terminee' => $toutesTerminees,
                'message' => 'Assignation marquée comme terminée avec succès',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage de l\'assignation comme terminée', [
                'message' => $e->getMessage(),
                'assignation_id' => $assignationId,
                'user_id' => $user->id
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors du marquage de l\'assignation: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Met à jour la progression d'une assignation de tâche
     *
     * @param string $assignationId L'ID de l'assignation
     * @param int $progression La nouvelle progression (0-100)
     * @param User $user L'utilisateur qui effectue l'action
     * @return array Résultat de l'opération
     */
    public function mettreAJourProgressionAssignation(string $assignationId, int $progression, User $user): array
    {
        try {
            $assignation = TaskAssignation::findOrFail($assignationId);
            
            // Vérifier si l'utilisateur a le droit de modifier cette assignation
            // Cette vérification dépendrait de votre logique d'autorisation
            
            $assignation->mettreAJourProgression($progression);
            
            return [
                'success' => true,
                'assignation' => $assignation,
                'message' => 'Progression mise à jour avec succès',
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la progression', [
                'message' => $e->getMessage(),
                'assignation_id' => $assignationId,
                'progression' => $progression,
                'user_id' => $user->id
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la progression: ' . $e->getMessage(),
            ];
        }
    }
}
