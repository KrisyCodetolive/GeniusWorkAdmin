<?php

namespace App\Policies;

use App\Models\PlageHoraire;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlageHorairePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent voir la liste des plages horaires
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PlageHoraire $plageHoraire): bool
    {
        // SuperAdmin et Support peuvent voir toutes les plages horaires
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent voir que les plages horaires de leur entreprise
        return $user->entreprise_id === $plageHoraire->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Tous les utilisateurs authentifiés peuvent créer des plages horaires
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PlageHoraire $plageHoraire): bool
    {
        // SuperAdmin et Support peuvent modifier toutes les plages horaires
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent modifier que les plages horaires de leur entreprise
        return $user->entreprise_id === $plageHoraire->entreprise_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PlageHoraire $plageHoraire): bool
    {
        // SuperAdmin et Support peuvent supprimer toutes les plages horaires
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent supprimer que les plages horaires de leur entreprise
        // et seulement si elles ne sont pas utilisées dans des jours
        if ($user->entreprise_id === $plageHoraire->entreprise_id) {
            // Vérifier si la plage horaire est utilisée
            return $plageHoraire->jours()->count() === 0;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PlageHoraire $plageHoraire): bool
    {
        // SuperAdmin et Support peuvent restaurer toutes les plages horaires
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs standards ne peuvent restaurer que les plages horaires de leur entreprise
        return $user->entreprise_id === $plageHoraire->entreprise_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PlageHoraire $plageHoraire): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement des plages horaires
        return $user->isSuperAdmin();
    }
}
