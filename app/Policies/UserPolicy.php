<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function view(User $user, User $model): bool
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
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        // Les SuperAdmin et Support peuvent modifier tous les utilisateurs
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Empêcher la modification des SuperAdmin et Support par d'autres rôles
        if ($model->isSuperAdmin() || $model->isSupport()) {
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        // Les SuperAdmin peuvent supprimer n'importe quel utilisateur
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Les Support peuvent supprimer tous les utilisateurs sauf les SuperAdmin
        if ($user->isSupport() && !$model->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can impersonate another user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return bool
     */
    public function impersonate(User $user, User $model): bool
    {
        // Seuls les SuperAdmin et Support peuvent se connecter en tant qu'un autre utilisateur
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }

        // Empêcher l'impersonation d'un SuperAdmin
        if ($model->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
