<?php

namespace App\Policies;

use App\Models\SoldeConge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SoldeCongePolicy
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
        // SuperAdmin et Support peuvent voir tous les soldes de congé
        // Les autres utilisateurs peuvent voir les soldes de congé, mais ils seront filtrés:
        // - Les Admin ne verront que les soldes des employés de leur entreprise
        // - Les utilisateurs normaux ne verront que leurs propres soldes
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, SoldeConge $soldeConge)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs peuvent voir leurs propres soldes de congé
        if ($user->id === $soldeConge->employeur_id) {
            return true;
        }

        // Les Admin peuvent voir les soldes des employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $soldeConge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les SuperAdmin, Support et Admin peuvent créer des soldes de congé
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, SoldeConge $soldeConge)
    {
        // SuperAdmin et Support peuvent modifier n'importe quel solde de congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les Admin peuvent modifier les soldes des employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $soldeConge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, SoldeConge $soldeConge)
    {
        // SuperAdmin peut supprimer n'importe quel solde de congé
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer des soldes de congé
        if ($user->isSupport()) {
            return true;
        }

        // Les Admin peuvent supprimer les soldes des employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $soldeConge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, SoldeConge $soldeConge)
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des soldes de congé
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, SoldeConge $soldeConge)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement un solde de congé
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can add balance to the leave balance.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function ajouterSolde(User $user, SoldeConge $soldeConge)
    {
        // SuperAdmin et Support peuvent ajouter du solde à n'importe quel utilisateur
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les Admin peuvent ajouter du solde aux employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $soldeConge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can deduct from the leave balance.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function deduireSolde(User $user, SoldeConge $soldeConge)
    {
        // SuperAdmin et Support peuvent déduire du solde à n'importe quel utilisateur
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les Admin peuvent déduire du solde aux employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $soldeConge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reset the leave balance.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reinitialiserSolde(User $user, SoldeConge $soldeConge)
    {
        // Seuls les SuperAdmin et Support peuvent réinitialiser un solde de congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les Admin peuvent réinitialiser les soldes des employés de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $soldeConge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }
}
