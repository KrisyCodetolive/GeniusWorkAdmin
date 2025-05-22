<?php

namespace App\Policies;

use App\Models\Abonnement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbonnementPolicy
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
        // Tous les utilisateurs authentifiés peuvent voir la liste des abonnements
        // Le filtrage par entreprise est géré au niveau du scope global EntrepriseScope
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function view(User $user, Abonnement $abonnement): bool
    {
        // Les SuperAdmin et Support peuvent voir n'importe quel abonnement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent voir uniquement les abonnements de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $abonnement->entreprise_id;
        }
        
        // Les utilisateurs standards ne peuvent pas voir les abonnements
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Seuls les SuperAdmin et Support peuvent créer des abonnements manuellement
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function update(User $user, Abonnement $abonnement): bool
    {
        // Seuls les SuperAdmin et Support peuvent modifier des abonnements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // On ne peut pas modifier un abonnement résilié
        return $abonnement->statut !== 'resilie';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function delete(User $user, Abonnement $abonnement): bool
    {
        // Seuls les SuperAdmin peuvent supprimer des abonnements
        if (!$user->isSuperAdmin()) {
            return false;
        }
        
        // On ne peut pas supprimer un abonnement actif
        return $abonnement->statut !== 'actif';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function restore(User $user, Abonnement $abonnement): bool
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des abonnements
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function forceDelete(User $user, Abonnement $abonnement): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement des abonnements
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can renew an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function renouveler(User $user, Abonnement $abonnement): bool
    {
        // Les SuperAdmin et Support peuvent renouveler n'importe quel abonnement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent renouveler uniquement les abonnements de leur entreprise
        // et seulement si l'abonnement n'est pas résilié
        if ($user->isAdmin() && $user->entreprise_id === $abonnement->entreprise_id) {
            return $abonnement->statut !== 'resilie';
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can activate an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function activer(User $user, Abonnement $abonnement): bool
    {
        // Seuls les SuperAdmin et Support peuvent activer des abonnements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // On ne peut activer qu'un abonnement en attente ou suspendu
        return in_array($abonnement->statut, ['en_attente', 'suspendu']);
    }
    
    /**
     * Determine whether the user can deactivate an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function desactiver(User $user, Abonnement $abonnement): bool
    {
        // Seuls les SuperAdmin et Support peuvent désactiver des abonnements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // On ne peut désactiver qu'un abonnement actif
        return $abonnement->statut === 'actif';
    }
    
    /**
     * Determine whether the user can change the plan of an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function changerPlan(User $user, Abonnement $abonnement): bool
    {
        // Les SuperAdmin et Support peuvent changer le plan de n'importe quel abonnement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return $abonnement->statut !== 'resilie';
        }
        
        // Les Admin peuvent changer le plan uniquement des abonnements de leur entreprise
        // et seulement si l'abonnement est actif
        if ($user->isAdmin() && $user->entreprise_id === $abonnement->entreprise_id) {
            return $abonnement->statut === 'actif';
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can cancel an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function resilier(User $user, Abonnement $abonnement): bool
    {
        // Les SuperAdmin et Support peuvent résilier n'importe quel abonnement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return $abonnement->statut !== 'resilie';
        }
        
        // Les Admin peuvent résilier uniquement les abonnements de leur entreprise
        // et seulement si l'abonnement est actif
        if ($user->isAdmin() && $user->entreprise_id === $abonnement->entreprise_id) {
            return $abonnement->statut === 'actif';
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can generate an invoice for an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function genererFacture(User $user, Abonnement $abonnement): bool
    {
        // Les SuperAdmin et Support peuvent générer une facture pour n'importe quel abonnement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent générer une facture uniquement pour les abonnements de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $abonnement->entreprise_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can view the details of an abonnement.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Abonnement  $abonnement
     * @return bool
     */
    public function viewDetails(User $user, Abonnement $abonnement): bool
    {
        // Les SuperAdmin et Support peuvent voir les détails de n'importe quel abonnement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent voir les détails uniquement des abonnements de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $abonnement->entreprise_id;
        }
        
        return false;
    }
}