<?php

namespace App\Policies;

use App\Models\TypeConge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TypeCongePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // SuperAdmin et Support peuvent voir tous les types de congé
        // Les autres utilisateurs peuvent voir les types de congé, mais ils seront filtrés par entreprise
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TypeConge $typeConge)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs ne peuvent voir que les types de congé de leur entreprise
        return $user->entreprise_id === $typeConge->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les SuperAdmin, Support et les utilisateurs avec le rôle Admin peuvent créer des types de congé
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TypeConge $typeConge)
    {
        // SuperAdmin et Support peuvent modifier n'importe quel type de congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent modifier les types de congé de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $typeConge->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TypeConge $typeConge)
    {
        // SuperAdmin peut supprimer n'importe quel type de congé
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer des types de congé
        if ($user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent supprimer les types de congé de leur entreprise
        // seulement s'ils n'ont pas de congés associés
        if ($user->isAdmin() && 
            $user->entreprise_id === $typeConge->entreprise_id && 
            $typeConge->conges()->count() === 0) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TypeConge $typeConge)
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des types de congé
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TypeConge $typeConge)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement un type de congé
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can change the status of the type de congé.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function changerStatut(User $user, TypeConge $typeConge)
    {
        // SuperAdmin et Support peuvent changer le statut de n'importe quel type de congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent changer le statut des types de congé de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $typeConge->entreprise_id) {
            return true;
        }

        return false;
    }
}
