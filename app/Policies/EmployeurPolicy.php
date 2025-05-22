<?php

namespace App\Policies;

use App\Models\Employeur;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeurPolicy
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
        // Tous les utilisateurs peuvent voir la liste des employés
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Employeur $employeur)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs ne peuvent voir que les employés de leur entreprise
        return $user->entreprise_id === $employeur->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les SuperAdmin, Support et les utilisateurs avec le rôle Admin peuvent créer des employés
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Employeur $employeur)
    {
        // SuperAdmin et Support peuvent modifier n'importe quel employé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent modifier les employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $employeur->entreprise_id) {
            return true;
        }

        // Un employé peut modifier ses propres informations
        if ($user->employeur_id === $employeur->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Employeur $employeur)
    {
        // SuperAdmin peut supprimer n'importe quel employé
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer les employés
        if ($user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent supprimer les employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Employeur $employeur)
    {
        // SuperAdmin et Support peuvent restaurer n'importe quel employé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent restaurer les employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Employeur $employeur)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement un employé
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can rotate the QR code of the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Employeur  $employeur
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function rotateQRCode(User $user, Employeur $employeur)
    {
        // SuperAdmin et Support peuvent régénérer le QR code de n'importe quel employé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent régénérer le QR code des employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $employeur->entreprise_id) {
            return true;
        }

        return false;
    }
}
