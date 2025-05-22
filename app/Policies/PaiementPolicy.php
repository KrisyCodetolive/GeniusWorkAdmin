<?php

namespace App\Policies;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaiementPolicy
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
        // Tous les utilisateurs authentifiés peuvent voir la liste des paiements
        // Le filtrage par entreprise est géré au niveau du scope global EntrepriseScope
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function view(User $user, Paiement $paiement): bool
    {
        // Les SuperAdmin et Support peuvent voir n'importe quel paiement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent voir uniquement les paiements de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $paiement->entreprise_id;
        }
        
        // Les utilisateurs standards peuvent voir uniquement les paiements qu'ils ont initiés
        return $user->id === $paiement->initiateur_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Seuls les SuperAdmin et Support peuvent créer des paiements manuellement
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function update(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin et Support peuvent modifier des paiements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // On ne peut pas modifier un paiement complété, remboursé ou annulé
        return !in_array($paiement->statut, [
            Paiement::STATUT_COMPLETE,
            Paiement::STATUT_REMBOURSE,
            Paiement::STATUT_ANNULE
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function delete(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin peuvent supprimer des paiements
        if (!$user->isSuperAdmin()) {
            return false;
        }
        
        // On ne peut pas supprimer un paiement complété
        return $paiement->statut !== Paiement::STATUT_COMPLETE;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function restore(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des paiements
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function forceDelete(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement des paiements
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can validate a payment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function valider(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin et Support peuvent valider des paiements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // Le paiement doit nécessiter une validation et ne pas être déjà traité
        return $paiement->necessiteValidation() && 
               !in_array($paiement->statut, [
                   Paiement::STATUT_COMPLETE,
                   Paiement::STATUT_REMBOURSE,
                   Paiement::STATUT_ANNULE,
                   Paiement::STATUT_REJETE
               ]);
    }
    
    /**
     * Determine whether the user can reject a payment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function rejeter(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin et Support peuvent rejeter des paiements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // Le paiement doit nécessiter une validation et ne pas être déjà traité
        return $paiement->necessiteValidation() && 
               !in_array($paiement->statut, [
                   Paiement::STATUT_COMPLETE,
                   Paiement::STATUT_REMBOURSE,
                   Paiement::STATUT_ANNULE,
                   Paiement::STATUT_REJETE
               ]);
    }
    
    /**
     * Determine whether the user can cancel a payment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function annuler(User $user, Paiement $paiement): bool
    {
        // Les SuperAdmin et Support peuvent annuler n'importe quel paiement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            // Mais seulement si le paiement n'est pas déjà traité
            return !in_array($paiement->statut, [
                Paiement::STATUT_COMPLETE,
                Paiement::STATUT_REMBOURSE,
                Paiement::STATUT_ANNULE,
                Paiement::STATUT_REJETE
            ]);
        }
        
        // Les Admin peuvent annuler les paiements de leur entreprise
        if ($user->isAdmin() && $user->entreprise_id === $paiement->entreprise_id) {
            return $paiement->estEnAttente() || $paiement->statut === Paiement::STATUT_TRAITEMENT;
        }
        
        // Les utilisateurs standards peuvent annuler uniquement les paiements qu'ils ont initiés
        if ($user->id === $paiement->initiateur_id) {
            return $paiement->estEnAttente() || $paiement->statut === Paiement::STATUT_TRAITEMENT;
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can refund a payment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function rembourser(User $user, Paiement $paiement): bool
    {
        // Seuls les SuperAdmin et Support peuvent rembourser des paiements
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // Le paiement doit être complété pour pouvoir être remboursé
        return $paiement->estComplete();
    }
    
    /**
     * Determine whether the user can view payment details.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Paiement  $paiement
     * @return bool
     */
    public function viewDetails(User $user, Paiement $paiement): bool
    {
        // Les SuperAdmin et Support peuvent voir les détails de n'importe quel paiement
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent voir les détails des paiements de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $paiement->entreprise_id;
        }
        
        // Les utilisateurs standards peuvent voir les détails des paiements qu'ils ont initiés
        return $user->id === $paiement->initiateur_id;
    }
}
