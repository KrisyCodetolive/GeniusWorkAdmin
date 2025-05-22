<?php

namespace App\Policies;

use App\Models\Filiale;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilialePolicy
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
        // Tous les utilisateurs peuvent voir la liste des filiales
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Filiale $filiale)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs ne peuvent voir que les filiales de leur entreprise
        return $user->entreprise_id === $filiale->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les SuperAdmin, Support et les utilisateurs avec le rôle Admin peuvent créer des filiales
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Filiale $filiale)
    {
        // SuperAdmin et Support peuvent modifier n'importe quelle filiale
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent modifier les filiales de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $filiale->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Filiale $filiale)
    {
        // SuperAdmin peut supprimer n'importe quelle filiale
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer les filiales qui ne sont pas des sièges sociaux
        if ($user->isSupport() && !$filiale->estSiegeSocial()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent supprimer les filiales de leur entreprise
        // seulement si elles peuvent être supprimées en toute sécurité
        if ($user->isAdmin() && 
            $user->entreprise_id === $filiale->entreprise_id && 
            $filiale->peutEtreSupprimee()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Filiale $filiale)
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des filiales
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Filiale $filiale)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement une filiale
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can assign a responsable to the filiale.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assignerResponsable(User $user, Filiale $filiale)
    {
        // SuperAdmin et Support peuvent assigner des responsables à n'importe quelle filiale
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent assigner des responsables aux filiales de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $filiale->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can terminate a responsable's mandate.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function terminerMandat(User $user, Filiale $filiale)
    {
        // Même règle que pour assigner un responsable
        return $this->assignerResponsable($user, $filiale);
    }

    /**
     * Determine whether the user can update the status of the filiale.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateStatut(User $user, Filiale $filiale)
    {
        // SuperAdmin et Support peuvent changer le statut de n'importe quelle filiale
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent changer le statut des filiales de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $filiale->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can merge filiales.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Filiale  $filiale
     * @param  \App\Models\Filiale  $autreFiliale
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function fusionner(User $user, Filiale $filiale, Filiale $autreFiliale = null)
    {
        // SuperAdmin peut fusionner n'importe quelles filiales
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut fusionner des filiales si elles sont dans la même entreprise
        if ($user->isSupport()) {
            if ($autreFiliale === null || $filiale->entreprise_id === $autreFiliale->entreprise_id) {
                return true;
            }
        }

        // Les utilisateurs avec le rôle Admin peuvent fusionner des filiales de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $filiale->entreprise_id) {
            if ($autreFiliale === null || $filiale->entreprise_id === $autreFiliale->entreprise_id) {
                return true;
            }
        }

        return false;
    }
}
