<?php

namespace App\Policies;

use App\Models\Departement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartementPolicy
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
        // Tous les utilisateurs peuvent voir la liste des départements
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Departement $departement)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs ne peuvent voir que les départements de leur entreprise
        return $user->entreprise_id === $departement->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les SuperAdmin, Support et les utilisateurs avec le rôle Admin peuvent créer des départements
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Departement $departement)
    {
        // SuperAdmin et Support peuvent modifier n'importe quel département
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent modifier les départements de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $departement->entreprise_id) {
            return true;
        }

        // Les responsables de département peuvent modifier leur propre département
        if ($departement->responsable_id === $user->employeur_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Departement $departement)
    {
        // SuperAdmin peut supprimer n'importe quel département
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer les départements
        if ($user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent supprimer les départements de leur entreprise
        // seulement s'ils peuvent être supprimés en toute sécurité
        if ($user->isAdmin() && 
            $user->entreprise_id === $departement->entreprise_id && 
            $departement->peutEtreSupprime()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Departement $departement)
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des départements
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Departement $departement)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement un département
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can move the department to a new parent.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deplacer(User $user, Departement $departement)
    {
        // SuperAdmin et Support peuvent déplacer n'importe quel département
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent déplacer les départements de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $departement->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can transfer employees to another department.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function transfererEmployes(User $user, Departement $departement)
    {
        // SuperAdmin et Support peuvent transférer des employés
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent transférer des employés dans leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $departement->entreprise_id) {
            return true;
        }

        // Les responsables de département peuvent transférer des employés de leur département
        if ($departement->responsable_id === $user->employeur_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can merge departments.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @param  \App\Models\Departement  $autreDepartement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function fusionner(User $user, Departement $departement, Departement $autreDepartement = null)
    {
        // SuperAdmin peut fusionner n'importe quels départements
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut fusionner des départements
        if ($user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent fusionner des départements de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $departement->entreprise_id) {
            if ($autreDepartement === null || $departement->entreprise_id === $autreDepartement->entreprise_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can assign a responsable to the department.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Departement  $departement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assignerResponsable(User $user, Departement $departement)
    {
        // SuperAdmin et Support peuvent assigner des responsables
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent assigner des responsables dans leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $departement->entreprise_id) {
            return true;
        }

        return false;
    }
}
