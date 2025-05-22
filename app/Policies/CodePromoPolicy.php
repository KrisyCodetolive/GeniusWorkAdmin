<?php

namespace App\Policies;

use App\Models\CodePromo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CodePromoPolicy
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
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function view(User $user, CodePromo $codePromo): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function update(User $user, CodePromo $codePromo): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function delete(User $user, CodePromo $codePromo): bool
    {
        // Vérifier si le code promo a déjà été utilisé
        if ($codePromo->nombre_utilisations > 0) {
            // Seuls les SuperAdmin peuvent supprimer un code promo déjà utilisé
            return $user->isSuperAdmin();
        }
        
        // Les SuperAdmin et Support peuvent supprimer les codes promo non utilisés
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function restore(User $user, CodePromo $codePromo): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function forceDelete(User $user, CodePromo $codePromo): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can reset usage count.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function resetUsage(User $user, CodePromo $codePromo): bool
    {
        // Seuls les SuperAdmin peuvent réinitialiser le compteur d'utilisation
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can duplicate a code promo.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function duplicate(User $user, CodePromo $codePromo): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }
    
    /**
     * Determine whether the user can activate or deactivate a code promo.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CodePromo  $codePromo
     * @return bool
     */
    public function toggleActive(User $user, CodePromo $codePromo): bool
    {
        return $user->isSuperAdmin() || $user->isSupport();
    }
}
