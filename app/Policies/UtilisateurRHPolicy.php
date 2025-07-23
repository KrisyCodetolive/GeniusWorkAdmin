<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UtilisateurRHPolicy
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
        // SuperAdmin, Support et Admin peuvent voir la liste des utilisateurs RH
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
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
        // SuperAdmin et Support peuvent voir tous les utilisateurs
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Admin peut voir les utilisateurs admin ou manager de son entreprise
        if ($user->isAdmin() && $model->entreprise_id === $user->entreprise_id) {
            return $model->isAdmin() || $model->isManager();
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // SuperAdmin, Support et Admin peuvent créer des utilisateurs RH
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
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
        // SuperAdmin et Support peuvent modifier tous les utilisateurs
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Empêcher la modification des SuperAdmin et Support par d'autres rôles
        if ($model->isSuperAdmin() || $model->isSupport()) {
            return false;
        }
        
        // Admin peut modifier les utilisateurs admin ou manager de son entreprise
        if ($user->isAdmin() && $model->entreprise_id === $user->entreprise_id) {
            return $model->isAdmin() || $model->isManager();
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
        // SuperAdmin peut supprimer n'importe quel utilisateur
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer tous les utilisateurs sauf les SuperAdmin
        if ($user->isSupport() && !$model->isSuperAdmin()) {
            return true;
        }
        
        // Admin peut supprimer les utilisateurs admin ou manager de son entreprise
        // mais pas lui-même pour éviter de se retrouver sans accès
        if ($user->isAdmin() && $model->entreprise_id === $user->entreprise_id && $user->id !== $model->id) {
            return $model->isAdmin() || $model->isManager();
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
        // SuperAdmin et Support peuvent restaurer n'importe quel utilisateur
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Admin peut restaurer les utilisateurs admin ou manager de son entreprise
        if ($user->isAdmin() && $model->entreprise_id === $user->entreprise_id) {
            return $model->isAdmin() || $model->isManager();
        }
        
        return false;
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
}
