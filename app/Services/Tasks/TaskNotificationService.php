<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\TaskAssignation;
use App\Models\User;
use App\Models\Employeur;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Collection;

class TaskNotificationService
{
    /**
     * Envoie des notifications pour les tâches en retard
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return int Le nombre de notifications envoyées
     */
    public function notifierTachesEnRetard(string $entrepriseId): int
    {
        $count = 0;
        
        // Récupérer toutes les tâches en retard
        $tachesEnRetard = Task::where('entreprise_id', $entrepriseId)
            ->where('statut', Task::STATUT_EN_RETARD)
            ->get();
            
        foreach ($tachesEnRetard as $tache) {
            $assignations = $tache->assignations;
            
            foreach ($assignations as $assignation) {
                $employe = $assignation->employeur;
                $user = $employe->user;
                
                if ($user) {
                    try {
                        // Ici, vous pourriez utiliser une notification Laravel
                        // Notification::send($user, new TaskEnRetardNotification($tache, $assignation));
                        
                        // Pour l'instant, nous allons simplement logger l'action
                        Log::info('Notification de tâche en retard envoyée', [
                            'task_id' => $tache->id,
                            'task_titre' => $tache->titre,
                            'employe_id' => $employe->id,
                            'employe_nom' => $employe->nom_complet,
                            'user_id' => $user->id,
                            'date_fin' => $tache->date_fin
                        ]);
                        
                        $count++;
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'envoi de la notification de tâche en retard', [
                            'message' => $e->getMessage(),
                            'task_id' => $tache->id,
                            'employe_id' => $employe->id
                        ]);
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Envoie des notifications pour les tâches à venir
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @param int $joursAvant Nombre de jours avant l'échéance pour notifier
     * @return int Le nombre de notifications envoyées
     */
    public function notifierTachesAVenir(string $entrepriseId, int $joursAvant = 1): int
    {
        $count = 0;
        $dateReference = Carbon::now()->addDays($joursAvant)->startOfDay();
        $dateFin = Carbon::now()->addDays($joursAvant)->endOfDay();
        
        // Récupérer toutes les tâches dont la date d'échéance est dans X jours
        $tachesAVenir = Task::where('entreprise_id', $entrepriseId)
            ->whereIn('statut', [Task::STATUT_EN_ATTENTE, Task::STATUT_EN_COURS])
            ->whereBetween('date_fin', [$dateReference, $dateFin])
            ->get();
            
        foreach ($tachesAVenir as $tache) {
            $assignations = $tache->assignations;
            
            foreach ($assignations as $assignation) {
                $employe = $assignation->employeur;
                $user = $employe->user;
                
                if ($user) {
                    try {
                        // Ici, vous pourriez utiliser une notification Laravel
                        // Notification::send($user, new TaskAVenirNotification($tache, $assignation, $joursAvant));
                        
                        // Pour l'instant, nous allons simplement logger l'action
                        Log::info('Notification de tâche à venir envoyée', [
                            'task_id' => $tache->id,
                            'task_titre' => $tache->titre,
                            'employe_id' => $employe->id,
                            'employe_nom' => $employe->nom_complet,
                            'user_id' => $user->id,
                            'date_fin' => $tache->date_fin,
                            'jours_avant' => $joursAvant
                        ]);
                        
                        $count++;
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'envoi de la notification de tâche à venir', [
                            'message' => $e->getMessage(),
                            'task_id' => $tache->id,
                            'employe_id' => $employe->id
                        ]);
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Notifie les responsables des tâches en retard de leur équipe
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return int Le nombre de notifications envoyées
     */
    public function notifierResponsablesTachesEnRetard(string $entrepriseId): int
    {
        $count = 0;
        
        // Récupérer toutes les assignations de tâches en retard par équipe
        $assignationsParEquipe = TaskAssignation::where('entreprise_id', $entrepriseId)
            ->where('statut', TaskAssignation::STATUT_EN_RETARD)
            ->where('assignable_type', 'App\\Models\\Equipe')
            ->get()
            ->groupBy('assignable_id');
            
        foreach ($assignationsParEquipe as $equipeId => $assignations) {
            // Récupérer l'équipe et son responsable
            $equipe = \App\Models\Equipe::find($equipeId);
            
            if ($equipe && $equipe->responsable) {
                $responsable = $equipe->responsable;
                $user = $responsable->user;
                
                if ($user) {
                    try {
                        // Ici, vous pourriez utiliser une notification Laravel
                        // Notification::send($user, new TaskEquipeEnRetardNotification($equipe, $assignations));
                        
                        // Pour l'instant, nous allons simplement logger l'action
                        Log::info('Notification de tâches en retard pour l\'équipe envoyée au responsable', [
                            'equipe_id' => $equipe->id,
                            'equipe_nom' => $equipe->nom,
                            'responsable_id' => $responsable->id,
                            'responsable_nom' => $responsable->nom_complet,
                            'user_id' => $user->id,
                            'nombre_taches_retard' => $assignations->count()
                        ]);
                        
                        $count++;
                    } catch (\Exception $e) {
                        Log::error('Erreur lors de l\'envoi de la notification au responsable', [
                            'message' => $e->getMessage(),
                            'equipe_id' => $equipe->id,
                            'responsable_id' => $responsable->id
                        ]);
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Envoie un résumé des tâches à un employé
     *
     * @param Employeur $employe L'employé
     * @return bool True si le résumé a été envoyé avec succès
     */
    public function envoyerResumeTaches(Employeur $employe): bool
    {
        $user = $employe->user;
        
        if (!$user) {
            return false;
        }
        
        try {
            // Récupérer les tâches de l'employé
            $assignations = TaskAssignation::where('employeur_id', $employe->id)->get();
            
            $tachesEnAttente = $assignations->where('statut', TaskAssignation::STATUT_EN_ATTENTE);
            $tachesEnCours = $assignations->where('statut', TaskAssignation::STATUT_EN_COURS);
            $tachesTerminees = $assignations->where('statut', TaskAssignation::STATUT_TERMINE);
            $tachesEnRetard = $assignations->where('statut', TaskAssignation::STATUT_EN_RETARD);
            
            // Ici, vous pourriez utiliser une notification Laravel ou un email
            // Notification::send($user, new TaskResumeNotification($tachesEnAttente, $tachesEnCours, $tachesTerminees, $tachesEnRetard));
            
            // Pour l'instant, nous allons simplement logger l'action
            Log::info('Résumé des tâches envoyé à l\'employé', [
                'employe_id' => $employe->id,
                'employe_nom' => $employe->nom_complet,
                'user_id' => $user->id,
                'taches_en_attente' => $tachesEnAttente->count(),
                'taches_en_cours' => $tachesEnCours->count(),
                'taches_terminees' => $tachesTerminees->count(),
                'taches_en_retard' => $tachesEnRetard->count()
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du résumé des tâches', [
                'message' => $e->getMessage(),
                'employe_id' => $employe->id
            ]);
            
            return false;
        }
    }
    
    /**
     * Envoie un résumé des tâches à tous les employés d'une entreprise
     *
     * @param string $entrepriseId L'ID de l'entreprise
     * @return int Le nombre de résumés envoyés avec succès
     */
    public function envoyerResumeTachesATous(string $entrepriseId): int
    {
        $count = 0;
        
        // Récupérer tous les employés actifs de l'entreprise
        $employes = Employeur::where('entreprise_id', $entrepriseId)
            ->where('statut', 'actif')
            ->get();
            
        foreach ($employes as $employe) {
            if ($this->envoyerResumeTaches($employe)) {
                $count++;
            }
        }
        
        return $count;
    }
}
