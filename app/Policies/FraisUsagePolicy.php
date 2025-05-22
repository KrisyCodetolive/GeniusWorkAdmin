<?php

namespace App\Policies;

use App\Models\FraisUsage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FraisUsagePolicy
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
        // Les SuperAdmin et Support peuvent voir tous les frais d'usage
        // Les autres utilisateurs peuvent voir les frais d'usage de leur entreprise
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FraisUsage  $fraisUsage
     * @return bool
     */
    public function view(User $user, FraisUsage $fraisUsage): bool
    {
        // Les SuperAdmin et Support peuvent voir n'importe quel frais d'usage
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les autres utilisateurs peuvent voir uniquement les frais d'usage de leur entreprise
        return $user->entreprise_id === $fraisUsage->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Seuls les SuperAdmin et Support peuvent créer des frais d'usage
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FraisUsage  $fraisUsage
     * @return bool
     */
    public function update(User $user, FraisUsage $fraisUsage): bool
    {
        // Seuls les SuperAdmin et Support peuvent modifier des frais d'usage
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FraisUsage  $fraisUsage
     * @return bool
     */
    public function delete(User $user, FraisUsage $fraisUsage): bool
    {
        // Seuls les SuperAdmin et Support peuvent supprimer des frais d'usage
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FraisUsage  $fraisUsage
     * @return bool
     */
    public function restore(User $user, FraisUsage $fraisUsage): bool
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des frais d'usage
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FraisUsage  $fraisUsage
     * @return bool
     */
    public function forceDelete(User $user, FraisUsage $fraisUsage): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can bill the usage fee.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FraisUsage  $fraisUsage
     * @return bool
     */
    public function facturer(User $user, FraisUsage $fraisUsage): bool
    {
        // Seuls les SuperAdmin et Support peuvent facturer des frais d'usage
        // Et seulement si le frais n'est pas déjà facturé
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        return $fraisUsage->facturation_id === null;
    }
}
