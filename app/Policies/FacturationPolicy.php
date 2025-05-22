<?php

namespace App\Policies;

use App\Models\Facturation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacturationPolicy
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
        // Tous les utilisateurs authentifiés peuvent voir la liste des facturations
        // Le filtrage par entreprise est géré au niveau du scope global EntrepriseScope
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function view(User $user, Facturation $facturation): bool
    {
        // Les SuperAdmin et Support peuvent voir n'importe quelle facturation
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent voir uniquement les facturations de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $facturation->entreprise_id;
        }
        
        // Les utilisateurs standards peuvent voir uniquement les facturations liées à leur compte
        return $user->id === $facturation->user_id || $user->entreprise_id === $facturation->entreprise_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Seuls les SuperAdmin et Support peuvent créer des facturations manuellement
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function update(User $user, Facturation $facturation): bool
    {
        // Seuls les SuperAdmin et Support peuvent modifier des facturations
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // On ne peut pas modifier une facturation déjà payée
        return $facturation->statut_paiement !== 'payé';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function delete(User $user, Facturation $facturation): bool
    {
        // Seuls les SuperAdmin peuvent supprimer des facturations
        if (!$user->isSuperAdmin()) {
            return false;
        }
        
        // On ne peut pas supprimer une facturation payée ou avec des paiements associés
        return $facturation->statut_paiement !== 'payé' && $facturation->paiements()->count() === 0;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function restore(User $user, Facturation $facturation): bool
    {
        // Seuls les SuperAdmin et Support peuvent restaurer des facturations
        return $user->isSuperAdmin() || $user->isSupport();
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function forceDelete(User $user, Facturation $facturation): bool
    {
        // Seuls les SuperAdmin peuvent supprimer définitivement des facturations
        return $user->isSuperAdmin();
    }
    
    /**
     * Determine whether the user can mark a facturation as paid.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function marquerPaye(User $user, Facturation $facturation): bool
    {
        // Les SuperAdmin et Support peuvent marquer n'importe quelle facturation comme payée
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent marquer comme payées uniquement les facturations de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $facturation->entreprise_id && $facturation->statut_paiement !== 'payé';
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can generate a payment for a facturation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function genererPaiement(User $user, Facturation $facturation): bool
    {
        // Les SuperAdmin et Support peuvent générer un paiement pour n'importe quelle facturation
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent générer des paiements uniquement pour les facturations de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $facturation->entreprise_id && $facturation->statut_paiement !== 'payé';
        }
        
        // Les utilisateurs standards peuvent générer des paiements pour leurs propres facturations
        return ($user->id === $facturation->user_id || $user->entreprise_id === $facturation->entreprise_id) 
               && $facturation->statut_paiement !== 'payé';
    }
    
    /**
     * Determine whether the user can download the invoice.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function telechargerFacture(User $user, Facturation $facturation): bool
    {
        // Les SuperAdmin et Support peuvent télécharger n'importe quelle facture
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent télécharger uniquement les factures de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $facturation->entreprise_id;
        }
        
        // Les utilisateurs standards peuvent télécharger uniquement leurs propres factures
        return $user->id === $facturation->user_id || $user->entreprise_id === $facturation->entreprise_id;
    }
    
    /**
     * Determine whether the user can send the invoice by email.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function envoyerParEmail(User $user, Facturation $facturation): bool
    {
        // Les SuperAdmin et Support peuvent envoyer n'importe quelle facture par email
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }
        
        // Les Admin peuvent envoyer uniquement les factures de leur entreprise
        if ($user->isAdmin()) {
            return $user->entreprise_id === $facturation->entreprise_id;
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can cancel the invoice.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Facturation  $facturation
     * @return bool
     */
    public function annuler(User $user, Facturation $facturation): bool
    {
        // Seuls les SuperAdmin et Support peuvent annuler des factures
        if (!($user->isSuperAdmin() || $user->isSupport())) {
            return false;
        }
        
        // On ne peut pas annuler une facture déjà payée ou avec des paiements associés
        return $facturation->statut_paiement !== 'payé' && $facturation->paiements()->count() === 0;
    }
}
