<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskAssignation;
use App\Models\Employeur;
use App\Models\Departement;
use App\Models\Equipe;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Crée une nouvelle tâche
     *
     * @param array $data Les données de la tâche
     * @return Task La tâche créée
     */
    public function creerTask(array $data): Task
    {
        DB::beginTransaction();
        
        try {
            // Création de la tâche
            $task = Task::create($data);
            
            // Si c'est une tâche de routine, on configure les paramètres supplémentaires
            if ($task->est_routine) {
                // Vérifier si les données de routine sont présentes
                if (empty($task->frequence_routine)) {
                    $task->frequence_routine = Task::FREQUENCE_QUOTIDIENNE;
                }
            }
            
            DB::commit();
            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la tâche', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
    
    /**
     * Assigne une tâche à un département
     *
     * @param Task $task La tâche à assigner
     * @param Departement $departement Le département
     * @return Collection Les assignations créées
     */
    public function assignerADepartement(Task $task, Departement $departement): Collection
    {
        DB::beginTransaction();
        
        try {
            $assignations = collect();
            
            // Récupérer tous les employés du département
            $employes = $departement->employeurs()->actif()->get();
            
            foreach ($employes as $employe) {
                $assignation = $this->creerAssignation($task, $departement, $employe);
                $assignations->push($assignation);
            }
            
            DB::commit();
            return $assignations;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'assignation de la tâche au département', [
                'message' => $e->getMessage(),
                'task_id' => $task->id,
                'departement_id' => $departement->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Assigne une tâche à une équipe
     *
     * @param Task $task La tâche à assigner
     * @param Equipe $equipe L'équipe
     * @return Collection Les assignations créées
     */
    public function assignerAEquipe(Task $task, Equipe $equipe): Collection
    {
        DB::beginTransaction();
        
        try {
            $assignations = collect();
            
            // Récupérer tous les membres actifs de l'équipe
            $membres = $equipe->membres()
                ->wherePivot('est_actif', true)
                ->get();
            
            foreach ($membres as $membre) {
                $assignation = $this->creerAssignation($task, $equipe, $membre);
                $assignations->push($assignation);
            }
            
            DB::commit();
            return $assignations;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'assignation de la tâche à l\'équipe', [
                'message' => $e->getMessage(),
                'task_id' => $task->id,
                'equipe_id' => $equipe->id
            ]);
            throw $e;
        }
    }
    
    /**
     * Assigne une tâche à plusieurs équipes
     *
     * @param Task $task La tâche à assigner
     * @param array $equipeIds Les IDs des équipes
     * @return Collection Les assignations créées
     */
    public function assignerAEquipes(Task $task, array $equipeIds): Collection
    {
        $assignations = collect();
        
        foreach ($equipeIds as $equipeId) {
            $equipe = Equipe::find($equipeId);
            if ($equipe) {
                $assignationsEquipe = $this->assignerAEquipe($task, $equipe);
                $assignations = $assignations->merge($assignationsEquipe);
            }
        }
        
        return $assignations;
    }
    
    /**
     * Assigne une tâche à un employé spécifique
     *
     * @param Task $task La tâche à assigner
     * @param Employeur $employe L'employé
     * @return TaskAssignation L'assignation créée
     */
    public function assignerAEmploye(Task $task, Employeur $employe): TaskAssignation
    {
        return $this->creerAssignation($task, null, $employe);
    }
    
    /**
     * Assigne une tâche à tous les employés de l'entreprise
     *
     * @param Task $task La tâche à assigner
     * @param string $entrepriseId L'ID de l'entreprise
     * @return Collection Les assignations créées
     */
    public function assignerATous(Task $task, string $entrepriseId): Collection
    {
        DB::beginTransaction();
        
        try {
            $assignations = collect();
            
            // Récupérer tous les employés actifs de l'entreprise
            $employes = Employeur::where('entreprise_id', $entrepriseId)
                ->actif()
                ->get();
            
            foreach ($employes as $employe) {
                $assignation = $this->creerAssignation($task, null, $employe, true);
                $assignations->push($assignation);
            }
            
            DB::commit();
            return $assignations;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'assignation de la tâche à tous les employés', [
                'message' => $e->getMessage(),
                'task_id' => $task->id,
                'entreprise_id' => $entrepriseId
            ]);
            throw $e;
        }
    }
    
    /**
     * Crée une assignation de tâche
     *
     * @param Task $task La tâche
     * @param mixed $assignable L'entité assignable (département, équipe, etc.)
     * @param Employeur $employe L'employé
     * @param bool $estGlobale Indique si c'est une assignation globale
     * @return TaskAssignation L'assignation créée
     */
    protected function creerAssignation(Task $task, $assignable, Employeur $employe, bool $estGlobale = false): TaskAssignation
    {
        // Vérifier si une assignation existe déjà pour cette tâche et cet employé
        $assignationExistante = TaskAssignation::where('task_id', $task->id)
            ->where('employeur_id', $employe->id)
            ->first();
            
        if ($assignationExistante) {
            return $assignationExistante;
        }
        
        $data = [
            'entreprise_id' => $task->entreprise_id,
            'task_id' => $task->id,
            'employeur_id' => $employe->id,
            'statut' => TaskAssignation::STATUT_EN_ATTENTE,
            'progression' => 0,
        ];
        
        if ($assignable && !$estGlobale) {
            $data['assignable_type'] = get_class($assignable);
            $data['assignable_id'] = $assignable->id;
        }
        
        return TaskAssignation::create($data);
    }
    
    /**
     * Génère les tâches de routine pour la journée
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @param Carbon|null $date La date pour laquelle générer les tâches (aujourd'hui par défaut)
     * @return Collection Les tâches générées
     */
    public function genererTachesRoutine(string $entrepriseId, ?Carbon $date = null): Collection
    {
        $date = $date ?? Carbon::today();
        $tachesGenerees = collect();
        
        // Récupérer toutes les tâches de routine actives
        $tachesRoutine = Task::where('entreprise_id', $entrepriseId)
            ->where('est_routine', true)
            ->get();
            
        foreach ($tachesRoutine as $tacheRoutine) {
            // Vérifier si la tâche doit être générée aujourd'hui selon sa fréquence
            if ($this->doitGenererTacheRoutine($tacheRoutine, $date)) {
                // Créer une nouvelle instance de la tâche pour aujourd'hui
                $nouvelleTache = $this->creerInstanceTacheRoutine($tacheRoutine, $date);
                
                if ($nouvelleTache) {
                    $tachesGenerees->push($nouvelleTache);
                }
            }
        }
        
        return $tachesGenerees;
    }
    
    /**
     * Vérifie si une tâche de routine doit être générée pour une date donnée
     *
     * @param Task $tacheRoutine La tâche de routine
     * @param Carbon $date La date à vérifier
     * @return bool True si la tâche doit être générée
     */
    protected function doitGenererTacheRoutine(Task $tacheRoutine, Carbon $date): bool
    {
        $jourSemaine = $date->dayOfWeek;
        
        switch ($tacheRoutine->frequence_routine) {
            case Task::FREQUENCE_QUOTIDIENNE:
                return true;
                
            case Task::FREQUENCE_HEBDOMADAIRE:
                // Vérifier si le jour de la semaine est configuré dans jour_routine
                $joursRoutine = $tacheRoutine->jour_routine ?? [];
                if (is_string($joursRoutine)) {
                    $joursRoutine = json_decode($joursRoutine, true) ?? [];
                }
                
                return in_array($jourSemaine, $joursRoutine);
                
            case Task::FREQUENCE_MENSUELLE:
                // Vérifier si le jour du mois est configuré dans jour_routine
                $joursRoutine = $tacheRoutine->jour_routine ?? [];
                if (is_string($joursRoutine)) {
                    $joursRoutine = json_decode($joursRoutine, true) ?? [];
                }
                
                return in_array($date->day, $joursRoutine);
                
            default:
                return false;
        }
    }
    
    /**
     * Crée une instance de tâche à partir d'une tâche de routine
     *
     * @param Task $tacheRoutine La tâche de routine
     * @param Carbon $date La date pour la nouvelle tâche
     * @return Task|null La nouvelle tâche créée ou null en cas d'erreur
     */
    protected function creerInstanceTacheRoutine(Task $tacheRoutine, Carbon $date): ?Task
    {
        // Vérifier si une instance de cette tâche a déjà été créée pour cette date
        $tacheExistante = Task::where('entreprise_id', $tacheRoutine->entreprise_id)
            ->where('meta_donnees->tache_routine_id', $tacheRoutine->id)
            ->where('meta_donnees->date_generation', $date->toDateString())
            ->first();
            
        if ($tacheExistante) {
            return null;
        }
        
        try {
            // Créer une copie des données de la tâche de routine
            $nouvelleTacheData = $tacheRoutine->toArray();
            
            // Supprimer les champs qui ne doivent pas être copiés
            unset(
                $nouvelleTacheData['id'],
                $nouvelleTacheData['created_at'],
                $nouvelleTacheData['updated_at'],
                $nouvelleTacheData['deleted_at']
            );
            
            // Configurer la nouvelle tâche
            $nouvelleTacheData['est_routine'] = false;
            $nouvelleTacheData['date_debut'] = $date->copy()->startOfDay();
            $nouvelleTacheData['date_fin'] = $date->copy()->endOfDay();
            $nouvelleTacheData['statut'] = Task::STATUT_EN_ATTENTE;
            
            // Ajouter des métadonnées pour tracer l'origine
            $metaDonnees = $nouvelleTacheData['meta_donnees'] ?? [];
            if (is_string($metaDonnees)) {
                $metaDonnees = json_decode($metaDonnees, true) ?? [];
            }
            
            $metaDonnees['tache_routine_id'] = $tacheRoutine->id;
            $metaDonnees['date_generation'] = $date->toDateString();
            $nouvelleTacheData['meta_donnees'] = $metaDonnees;
            
            // Créer la nouvelle tâche
            $nouvelleTache = Task::create($nouvelleTacheData);
            
            // Récupérer les assignations existantes de la tâche de routine
            $assignationsRoutine = TaskAssignation::where('task_id', $tacheRoutine->id)->get();
            
            // Créer des assignations similaires pour la nouvelle tâche
            foreach ($assignationsRoutine as $assignationRoutine) {
                $assignationData = $assignationRoutine->toArray();
                
                unset(
                    $assignationData['id'],
                    $assignationData['created_at'],
                    $assignationData['updated_at'],
                    $assignationData['deleted_at'],
                    $assignationData['date_debut_reelle'],
                    $assignationData['date_fin_reelle']
                );
                
                $assignationData['task_id'] = $nouvelleTache->id;
                $assignationData['statut'] = TaskAssignation::STATUT_EN_ATTENTE;
                $assignationData['progression'] = 0;
                
                TaskAssignation::create($assignationData);
            }
            
            return $nouvelleTache;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création d\'une instance de tâche routine', [
                'message' => $e->getMessage(),
                'tache_routine_id' => $tacheRoutine->id,
                'date' => $date->toDateString()
            ]);
            
            return null;
        }
    }
    
    /**
     * Met à jour le statut des tâches en retard
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return int Le nombre de tâches mises à jour
     */
    public function mettreAJourTachesEnRetard(string $entrepriseId): int
    {
        $now = Carbon::now();
        $count = 0;
        
        // Récupérer toutes les tâches qui sont en cours ou en attente et dont la date de fin est passée
        $tachesEnRetard = Task::where('entreprise_id', $entrepriseId)
            ->whereIn('statut', [Task::STATUT_EN_ATTENTE, Task::STATUT_EN_COURS])
            ->where('date_fin', '<', $now)
            ->get();
            
        foreach ($tachesEnRetard as $tache) {
            $tache->statut = Task::STATUT_EN_RETARD;
            $tache->save();
            
            // Mettre également à jour les assignations
            TaskAssignation::where('task_id', $tache->id)
                ->whereIn('statut', [TaskAssignation::STATUT_EN_ATTENTE, TaskAssignation::STATUT_EN_COURS])
                ->update(['statut' => TaskAssignation::STATUT_EN_RETARD]);
                
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Obtient les statistiques des tâches pour un employé
     *
     * @param Employeur $employe L'employé
     * @return array Les statistiques
     */
    public function getStatistiquesEmploye(Employeur $employe): array
    {
        $assignations = TaskAssignation::where('employeur_id', $employe->id)->get();
        
        $total = $assignations->count();
        $enAttente = $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count();
        $enCours = $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count();
        $terminees = $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count();
        $enRetard = $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count();
        
        $tauxCompletion = $total > 0 ? ($terminees / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'en_cours' => $enCours,
            'terminees' => $terminees,
            'en_retard' => $enRetard,
            'taux_completion' => round($tauxCompletion, 2),
        ];
    }
    
    /**
     * Obtient les statistiques des tâches pour un département
     *
     * @param Departement $departement Le département
     * @return array Les statistiques
     */
    public function getStatistiquesDepartement(Departement $departement): array
    {
        $assignations = TaskAssignation::where('assignable_type', Departement::class)
            ->where('assignable_id', $departement->id)
            ->get();
        
        $total = $assignations->count();
        $enAttente = $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count();
        $enCours = $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count();
        $terminees = $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count();
        $enRetard = $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count();
        
        $tauxCompletion = $total > 0 ? ($terminees / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'en_cours' => $enCours,
            'terminees' => $terminees,
            'en_retard' => $enRetard,
            'taux_completion' => round($tauxCompletion, 2),
        ];
    }
    
    /**
     * Obtient les statistiques des tâches pour une équipe
     *
     * @param Equipe $equipe L'équipe
     * @return array Les statistiques
     */
    public function getStatistiquesEquipe(Equipe $equipe): array
    {
        $assignations = TaskAssignation::where('assignable_type', Equipe::class)
            ->where('assignable_id', $equipe->id)
            ->get();
        
        $total = $assignations->count();
        $enAttente = $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE)->count();
        $enCours = $assignations->where('statut', TaskAssignation::STATUT_EN_COURS)->count();
        $terminees = $assignations->where('statut', TaskAssignation::STATUT_TERMINE)->count();
        $enRetard = $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD)->count();
        
        $tauxCompletion = $total > 0 ? ($terminees / $total) * 100 : 0;
        
        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'en_cours' => $enCours,
            'terminees' => $terminees,
            'en_retard' => $enRetard,
            'taux_completion' => round($tauxCompletion, 2),
        ];
    }
}
