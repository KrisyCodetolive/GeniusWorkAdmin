<?php

namespace App\Policies;

use App\Models\PlanAbonnement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanAbonnementPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function view(User $user, PlanAbonnement $planAbonnement): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function update(User $user, PlanAbonnement $planAbonnement): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function delete(User $user, PlanAbonnement $planAbonnement): bool
    {
        // Vérifier si le plan a des abonnements actifs
        if ($planAbonnement->abonnements()->count() > 0) {
            // Seuls les SuperAdmin peuvent supprimer un plan avec des abonnements
            return $user->isSuperAdmin();
        }
        
        // Les SuperAdmin et Support peuvent supprimer les plans sans abonnements
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function restore(User $user, PlanAbonnement $planAbonnement): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function forceDelete(User $user, PlanAbonnement $planAbonnement): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can change the status of a plan.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function changeStatus(User $user, PlanAbonnement $planAbonnement): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }
    
    /**
     * Determine whether the user can duplicate a plan.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PlanAbonnement  $planAbonnement
     * @return bool
     */
    public function duplicate(User $user, PlanAbonnement $planAbonnement): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }
}
