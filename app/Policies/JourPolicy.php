<?php

namespace App\Policies;

use App\Models\Jour;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JourPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent voir la liste des jours
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Jour $jour): bool
    {
        // SuperAdmin et Support peuvent voir tous les jours
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent voir que les jours de leur entreprise
        return $user->entreprise_id === $jour->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent créer des jours
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Jour $jour): bool
    {
        // SuperAdmin et Support peuvent modifier tous les jours
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent modifier que les jours de leur entreprise
        return $user->entreprise_id === $jour->entreprise_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Jour $jour): bool
    {
        // SuperAdmin et Support peuvent supprimer tous les jours
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent supprimer que les jours de leur entreprise
        return $user->entreprise_id === $jour->entreprise_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Jour $jour): bool
    {
        // SuperAdmin et Support peuvent restaurer tous les jours
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent restaurer que les jours de leur entreprise
        return $user->entreprise_id === $jour->entreprise_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Jour $jour): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement des jours
        return $user->isSuperAdmin();
    }
}
