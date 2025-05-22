<?php

namespace App\Policies;

use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EntreprisePolicy
{
    use HandlesAuthorization;

    /**
     * Détermine si l'utilisateur peut voir la liste des entreprises.
     */
    public function viewAny(User $user): bool
    {
        // Seuls les SuperAdmin et Support peuvent voir la liste complète des entreprises
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Détermine si l'utilisateur peut voir une entreprise spécifique.
     */
    public function view(User $user, Entreprise $entreprise): bool
    {
        // Les SuperAdmin et Support peuvent voir n'importe quelle entreprise
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les autres utilisateurs peuvent uniquement voir leur propre entreprise
        return $user->entreprise_id === $entreprise->id;
    }

    /**
     * Détermine si l'utilisateur peut créer des entreprises.
     */
    public function create(User $user): bool
    {
        // Seuls les SuperAdmin et Support peuvent créer des entreprises
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour une entreprise.
     */
    public function update(User $user, Entreprise $entreprise): bool
    {
        // Les SuperAdmin et Support peuvent mettre à jour n'importe quelle entreprise
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les administrateurs d'entreprise peuvent mettre à jour leur propre entreprise
        if ($user->isAdmin() && $user->entreprise_id === $entreprise->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer une entreprise.
     */
    public function delete(User $user, Entreprise $entreprise): bool
    {
        // Seuls les SuperAdmin et Support peuvent supprimer des entreprises
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Détermine si l'utilisateur peut restaurer une entreprise supprimée.
     */
    public function restore(User $user, Entreprise $entreprise): bool
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des entreprises
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Détermine si l'utilisateur peut supprimer définitivement une entreprise.
     */
    public function forceDelete(User $user, Entreprise $entreprise): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement des entreprises
        return $user->isSuperAdmin();
    }

    /**
     * Détermine si l'utilisateur peut voir sa propre entreprise (MonEntreprise).
     */
    public function viewOwn(User $user): bool
    {
        // Tous les utilisateurs connectés avec une entreprise associée peuvent voir leur propre entreprise
        return $user->entreprise_id !== null;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour sa propre entreprise (MonEntreprise).
     */
    public function updateOwn(User $user, Entreprise $entreprise): bool
    {
        // Les SuperAdmin et Support peuvent mettre à jour n'importe quelle entreprise
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        // Les administrateurs peuvent mettre à jour leur propre entreprise
        if ($user->isAdmin() && $user->entreprise_id === $entreprise->id) {
            return true;
        }

        return false;
    }
}
