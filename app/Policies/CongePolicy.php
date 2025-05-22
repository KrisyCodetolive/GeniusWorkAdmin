<?php

namespace App\Policies;

use App\Models\Conge;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CongePolicy
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
        // SuperAdmin et Support peuvent voir tous les congés
        // Les autres utilisateurs peuvent voir les congés, mais ils seront filtrés:
        // - Les Admin ne verront que les congés des employés de leur entreprise
        // - Les utilisateurs normaux ne verront que leurs propres congés
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Conge $conge)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs peuvent voir leurs propres congés
        if ($user->employeur_id === $conge->employeur_id) {
            return true;
        }

        // Les managers peuvent voir les congés des employés qu'ils gèrent
        if ($user->isAdmin() && $conge->employeur && $user->entreprise_id === $conge->employeur->entreprise_id) {
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
        // Tous les utilisateurs peuvent créer des demandes de congé
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Conge $conge)
    {
        // SuperAdmin et Support peuvent modifier n'importe quel congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs peuvent modifier leurs propres congés s'ils sont encore en attente
        if ($user->employeur_id === $conge->employeur_id && $conge->estEnAttente()) {
            return true;
        }

        // Les managers peuvent modifier les congés des employés qu'ils gèrent
        if ($user->isAdmin() && $conge->employeur && $user->entreprise_id === $conge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Conge $conge)
    {
        // SuperAdmin peut supprimer n'importe quel congé
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer des congés
        if ($user->isSupport()) {
            return true;
        }

        // Les utilisateurs peuvent supprimer leurs propres congés s'ils sont encore en attente
        if ($user->employeur_id === $conge->employeur_id && $conge->estEnAttente()) {
            return true;
        }

        // Les managers peuvent supprimer les congés des employés qu'ils gèrent
        if ($user->isAdmin() && $conge->employeur && $user->entreprise_id === $conge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Conge $conge)
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des congés
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Conge $conge)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement un congé
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can approve the leave request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approuver(User $user, Conge $conge)
    {
        // SuperAdmin et Support peuvent approuver n'importe quel congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les managers peuvent approuver les congés des employés qu'ils gèrent
        if ($user->isAdmin() && $conge->employeur && $user->entreprise_id === $conge->employeur->entreprise_id) {
            return true;
        }

        // Un utilisateur ne peut pas approuver sa propre demande de congé
        if ($user->employeur_id === $conge->employeur_id) {
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can reject the leave request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function rejeter(User $user, Conge $conge)
    {
        // Mêmes règles que pour approuver
        return $this->approuver($user, $conge);
    }

    /**
     * Determine whether the user can cancel the leave request.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function annuler(User $user, Conge $conge)
    {
        // SuperAdmin et Support peuvent annuler n'importe quel congé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs peuvent annuler leurs propres congés s'ils ne sont pas déjà annulés ou rejetés
        if ($user->employeur_id === $conge->employeur_id && 
            !$conge->estAnnule() && 
            !$conge->estRejete()) {
            return true;
        }

        // Les managers peuvent annuler les congés des employés qu'ils gèrent
        if ($user->isAdmin() && $conge->employeur && $user->entreprise_id === $conge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can mark the leave as paid.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function marquerPaye(User $user, Conge $conge)
    {
        // Seuls les SuperAdmin, Support et Admin peuvent marquer un congé comme payé
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les managers peuvent marquer comme payé les congés des employés qu'ils gèrent
        if ($user->isAdmin() && $conge->employeur && $user->entreprise_id === $conge->employeur->entreprise_id) {
            return true;
        }

        return false;
    }
}
