<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SitePolicy
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
        // Tous les utilisateurs peuvent voir la liste des sites
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Site $site)
    {
        // Vérifier si l'utilisateur est SuperAdmin ou Support
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs ne peuvent voir que les sites de leur entreprise
        return $user->entreprise_id === $site->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        // Seuls les SuperAdmin, Support et les utilisateurs avec le rôle Admin peuvent créer des sites
        return $user->isSuperAdmin() || $user->isSupport() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Site $site)
    {
        // SuperAdmin et Support peuvent modifier n'importe quel site
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent modifier les sites de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $site->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Site $site)
    {
        // SuperAdmin peut supprimer n'importe quel site
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Support peut supprimer des sites
        if ($user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent supprimer les sites de leur entreprise
        // seulement s'ils n'ont pas de pointages associés
        if ($user->isAdmin() && 
            $user->entreprise_id === $site->entreprise_id && 
            $site->pointages()->count() === 0) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Site $site)
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des sites
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Site $site)
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement un site
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage geofencing settings.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function gererGeofencing(User $user, Site $site)
    {
        // SuperAdmin et Support peuvent gérer le geofencing de n'importe quel site
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent gérer le geofencing des sites de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $site->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can change the status of the site.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function changerStatut(User $user, Site $site)
    {
        // SuperAdmin et Support peuvent changer le statut de n'importe quel site
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent changer le statut des sites de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $site->entreprise_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign employees to the site.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assignerEmployes(User $user, Site $site)
    {
        // SuperAdmin et Support peuvent assigner des employés à n'importe quel site
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les utilisateurs avec le rôle Admin peuvent assigner des employés aux sites de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $site->entreprise_id) {
            return true;
        }

        return false;
    }
}
