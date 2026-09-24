<?php

namespace App\Policies;

use App\Models\DemandeDepense;
use App\Models\User;
use App\Models\ValidationDepense;

/**
 * Règles de contrôle interne des sorties d'argent :
 * - personne ne valide sa propre demande ;
 * - le comptable et le CEO d'une même demande sont deux personnes différentes ;
 * - une demande soumise n'est plus modifiable.
 */
class DemandeDepensePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DemandeDepense $demande): bool
    {
        if ($user->isSuperAdmin() || $user->isSupport()) {
            return true;
        }

        if (! $this->memeEntreprise($user, $demande)) {
            return false;
        }

        return $this->estAuteur($user, $demande)
            || $user->isComptable()
            || $user->isAdmin()
            || $demande->parametres()->estCeo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DemandeDepense $demande): bool
    {
        return $demande->estModifiable()
            && ($this->estAuteur($user, $demande) || $user->isSuperAdmin());
    }

    public function delete(User $user, DemandeDepense $demande): bool
    {
        return $this->update($user, $demande);
    }

    public function soumettre(User $user, DemandeDepense $demande): bool
    {
        return $this->update($user, $demande);
    }

    public function annuler(User $user, DemandeDepense $demande): bool
    {
        return in_array($demande->statut, [
            DemandeDepense::STATUT_BROUILLON,
            DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE,
            DemandeDepense::STATUT_EN_ATTENTE_CEO,
        ]) && $this->estAuteur($user, $demande);
    }

    public function validerComptabilite(User $user, DemandeDepense $demande): bool
    {
        return $demande->statut === DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE
            && ($user->isComptable() || $user->isSuperAdmin())
            && $this->peutValider($user, $demande);
    }

    public function validerCeo(User $user, DemandeDepense $demande): bool
    {
        if ($demande->statut !== DemandeDepense::STATUT_EN_ATTENTE_CEO) {
            return false;
        }

        if (! ($demande->parametres()->estCeo($user) || $user->isSuperAdmin())) {
            return false;
        }

        // Le CEO ne peut pas être la personne qui a déjà signé côté comptabilité.
        $comptable = $demande->approbation(ValidationDepense::ETAPE_COMPTABILITE);

        return $this->peutValider($user, $demande)
            && (! $comptable || $comptable->user_id !== $user->id);
    }

    /**
     * Rejeter ou renvoyer : réservé au validateur de l'étape en cours.
     */
    public function rejeter(User $user, DemandeDepense $demande): bool
    {
        return $this->validerComptabilite($user, $demande) || $this->validerCeo($user, $demande);
    }

    public function decaisser(User $user, DemandeDepense $demande): bool
    {
        return $demande->statut === DemandeDepense::STATUT_APPROUVEE
            && ($user->isComptable() || $user->isSuperAdmin())
            && $this->memeEntreprise($user, $demande);
    }

    public function telechargerBon(User $user, DemandeDepense $demande): bool
    {
        return in_array($demande->statut, [DemandeDepense::STATUT_APPROUVEE, DemandeDepense::STATUT_PAYEE])
            && $this->view($user, $demande);
    }

    private function peutValider(User $user, DemandeDepense $demande): bool
    {
        return $this->memeEntreprise($user, $demande) && ! $this->estAuteur($user, $demande);
    }

    private function memeEntreprise(User $user, DemandeDepense $demande): bool
    {
        return $user->isSuperAdmin() || $user->entreprise_id === $demande->entreprise_id;
    }

    private function estAuteur(User $user, DemandeDepense $demande): bool
    {
        return $demande->cree_par_user_id === $user->id
            || ($user->employeur_id !== null && $user->employeur_id === $demande->demandeur_id);
    }
}
