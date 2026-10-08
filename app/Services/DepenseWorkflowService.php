<?php

namespace App\Services;

use App\Models\DemandeDepense;
use App\Models\User;
use App\Models\ValidationDepense;
use App\Notifications\DepenseNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Seul point d'entrée des changements de statut d'une demande de dépense.
 * Chaque étape vérifie les droits (DemandeDepensePolicy), verrouille la demande,
 * enregistre une ligne dans le journal validations_depense puis prévient les personnes concernées.
 */
class DepenseWorkflowService
{
    public const DISQUE = 'local';

    public function soumettre(DemandeDepense $demande, User $user): DemandeDepense
    {
        $demande = $this->transition($demande, $user, 'soumettre', function (DemandeDepense $demande) use ($user) {
            $demande->update(['statut' => DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_SOUMISSION, ValidationDepense::DECISION_EFFECTUE);
        });

        $this->notifier($this->comptables($demande), $demande, 'a_valider_comptable');

        return $demande;
    }

    public function validerComptabilite(DemandeDepense $demande, User $user, string $signature, ?string $commentaire = null): DemandeDepense
    {
        $demande = $this->transition($demande, $user, 'validerComptabilite', function (DemandeDepense $demande) use ($user, $signature, $commentaire) {
            $demande->update([
                'statut' => $demande->necessiteValidationCeo()
                    ? DemandeDepense::STATUT_EN_ATTENTE_CEO
                    : DemandeDepense::STATUT_APPROUVEE,
            ]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_COMPTABILITE, ValidationDepense::DECISION_APPROUVE, $commentaire, $signature);
        });

        if ($demande->statut === DemandeDepense::STATUT_EN_ATTENTE_CEO) {
            $this->notifier($demande->parametres()->ceos(), $demande, 'a_valider_ceo');
        } else {
            $this->notifierApprobation($demande);
        }

        return $demande;
    }

    public function validerCeo(DemandeDepense $demande, User $user, string $signature, ?string $commentaire = null): DemandeDepense
    {
        $demande = $this->transition($demande, $user, 'validerCeo', function (DemandeDepense $demande) use ($user, $signature, $commentaire) {
            $demande->update(['statut' => DemandeDepense::STATUT_APPROUVEE]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_CEO, ValidationDepense::DECISION_APPROUVE, $commentaire, $signature);
        });

        $this->notifierApprobation($demande);

        return $demande;
    }

    public function rejeter(DemandeDepense $demande, User $user, string $motif): DemandeDepense
    {
        return $this->refuser($demande, $user, $motif, DemandeDepense::STATUT_REJETEE, ValidationDepense::DECISION_REJETE, 'rejetee');
    }

    /**
     * Renvoie la demande au demandeur, qui peut la corriger puis la soumettre à nouveau.
     */
    public function renvoyer(DemandeDepense $demande, User $user, string $motif): DemandeDepense
    {
        return $this->refuser($demande, $user, $motif, DemandeDepense::STATUT_BROUILLON, ValidationDepense::DECISION_RENVOYE, 'renvoyee');
    }

    /**
     * Décharge signée par le demandeur après approbation : sans elle, pas de décaissement.
     */
    public function signerParDemandeur(DemandeDepense $demande, User $user, string $signature): DemandeDepense
    {
        $demande = $this->transition($demande, $user, 'signerApprobation', function (DemandeDepense $demande) use ($user, $signature) {
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_SIGNATURE_DEMANDEUR, ValidationDepense::DECISION_EFFECTUE, null, $signature);
        });

        $this->notifier($this->comptables($demande), $demande, 'a_payer');

        return $demande;
    }

    public function decaisser(DemandeDepense $demande, User $user, array $paiement): DemandeDepense
    {
        if (! array_key_exists($paiement['mode_paiement'] ?? null, DemandeDepense::MODES_PAIEMENT)) {
            throw new InvalidArgumentException('Mode de paiement invalide.');
        }

        $demande = $this->transition($demande, $user, 'decaisser', function (DemandeDepense $demande) use ($user, $paiement) {
            $preuve = $paiement['preuve_paiement'] ?? null;
            if ($preuve instanceof UploadedFile) {
                $preuve = $preuve->store("depenses/{$demande->id}/paiement", self::DISQUE);
            }

            $demande->update([
                'statut' => DemandeDepense::STATUT_PAYEE,
                'mode_paiement' => $paiement['mode_paiement'],
                'reference_paiement' => $paiement['reference_paiement'] ?? null,
                'date_paiement' => $paiement['date_paiement'] ?? now()->toDateString(),
                'payee_par_user_id' => $user->id,
                'preuve_paiement' => $preuve,
            ]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_DECAISSEMENT, ValidationDepense::DECISION_EFFECTUE, $paiement['commentaire'] ?? null);
        });

        // Archive du bon de sortie tel qu'il était au moment du paiement.
        $chemin = "depenses/{$demande->id}/bon-sortie-{$demande->reference}.pdf";
        Storage::disk(self::DISQUE)->put($chemin, $this->bonDeSortie($demande)->output());
        $demande->update(['pdf_bon_sortie' => $chemin]);

        $this->notifier($this->demandeurs($demande), $demande, 'payee');

        return $demande;
    }

    /**
     * Le demandeur a joint ses reçus : la justification part en vérification comptable.
     */
    public function soumettreJustification(DemandeDepense $demande, User $user): DemandeDepense
    {
        $demande = $this->transition($demande, $user, 'justifier', function (DemandeDepense $demande) {
            if (! $demande->justificatifs()->where('type', 'recu')->exists()) {
                throw new InvalidArgumentException('Ajoutez au moins un reçu avant de soumettre la justification.');
            }

            $demande->update(['statut' => DemandeDepense::STATUT_JUSTIFICATION_SOUMISE]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_JUSTIFICATION, ValidationDepense::DECISION_EFFECTUE);
        });

        $this->notifier($this->comptables($demande), $demande, 'justification_a_verifier');

        return $demande;
    }

    /**
     * Le comptable a vérifié les reçus : la demande est clôturée.
     */
    public function validerJustification(DemandeDepense $demande, User $user, ?string $commentaire = null): DemandeDepense
    {
        $demande = $this->transition($demande, $user, 'validerJustification', function (DemandeDepense $demande) use ($user, $commentaire) {
            $demande->update(['statut' => DemandeDepense::STATUT_CLOTUREE]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_JUSTIFICATION, ValidationDepense::DECISION_APPROUVE, $commentaire);
        });

        $this->notifier($this->demandeurs($demande), $demande, 'cloturee');

        return $demande;
    }

    /**
     * Reçus insuffisants ou illisibles : retour à « payée », le demandeur doit compléter.
     */
    public function renvoyerJustification(DemandeDepense $demande, User $user, string $motif): DemandeDepense
    {
        if (trim($motif) === '') {
            throw new InvalidArgumentException('Le motif est obligatoire.');
        }

        $demande = $this->transition($demande, $user, 'validerJustification', function (DemandeDepense $demande) use ($user, $motif) {
            $demande->update(['statut' => DemandeDepense::STATUT_PAYEE]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_JUSTIFICATION, ValidationDepense::DECISION_RENVOYE, $motif);
        });

        $this->notifier($this->demandeurs($demande), $demande, 'justification_renvoyee', $motif);

        return $demande;
    }

    public function annuler(DemandeDepense $demande, User $user, ?string $motif = null): DemandeDepense
    {
        return $this->transition($demande, $user, 'annuler', function (DemandeDepense $demande) use ($user, $motif) {
            $demande->update(['statut' => DemandeDepense::STATUT_ANNULEE]);
            $this->journaliser($demande, $user, ValidationDepense::ETAPE_ANNULATION, ValidationDepense::DECISION_EFFECTUE, $motif);
        });
    }

    /**
     * Bon de sortie PDF : montant en chiffres et en lettres, signatures, paiement, empreinte.
     */
    public function bonDeSortie(DemandeDepense $demande): \Barryvdh\DomPDF\PDF
    {
        $demande->loadMissing(['entreprise', 'categorie', 'departement', 'demandeur', 'creePar', 'payeePar', 'validations.user']);

        $signature = function (?ValidationDepense $validation): ?string {
            if (! $validation?->signature_path || ! Storage::disk(self::DISQUE)->exists($validation->signature_path)) {
                return null;
            }

            return 'data:image/png;base64,'.base64_encode(Storage::disk(self::DISQUE)->get($validation->signature_path));
        };

        $comptable = $demande->approbation(ValidationDepense::ETAPE_COMPTABILITE);
        $ceo = $demande->approbation(ValidationDepense::ETAPE_CEO);
        $demandeur = $demande->signatureDemandeur();

        return Pdf::loadView('pdf.bon-sortie', [
            'demande' => $demande,
            'comptable' => $comptable,
            'ceo' => $ceo,
            'validationDemandeur' => $demandeur,
            'signatureComptable' => $signature($comptable),
            'signatureCeo' => $signature($ceo),
            'signatureDemandeur' => $signature($demandeur),
            'hash' => $demande->hashDocument(),
        ])->setPaper('a4');
    }

    private function refuser(DemandeDepense $demande, User $user, string $motif, string $statut, string $decision, string $evenement): DemandeDepense
    {
        if (trim($motif) === '') {
            throw new InvalidArgumentException('Le motif est obligatoire.');
        }

        $demande = $this->transition($demande, $user, 'rejeter', function (DemandeDepense $demande) use ($user, $motif, $statut, $decision) {
            $etape = $demande->statut === DemandeDepense::STATUT_EN_ATTENTE_CEO
                ? ValidationDepense::ETAPE_CEO
                : ValidationDepense::ETAPE_COMPTABILITE;

            $demande->update(['statut' => $statut]);
            $this->journaliser($demande, $user, $etape, $decision, $motif);
        });

        $this->notifier($this->demandeurs($demande), $demande, $evenement, $motif);

        return $demande;
    }

    /**
     * Verrouille la demande, revérifie les droits sur son état à jour, puis applique le changement.
     * Deux validateurs qui cliquent en même temps ne peuvent donc pas faire avancer la demande deux fois.
     */
    private function transition(DemandeDepense $demande, User $user, string $ability, callable $changement): DemandeDepense
    {
        return DB::transaction(function () use ($demande, $user, $ability, $changement) {
            $demande = DemandeDepense::withoutGlobalScopes()->lockForUpdate()->findOrFail($demande->getKey());

            Gate::forUser($user)->authorize($ability, $demande);

            $changement($demande);

            return $demande->fresh();
        });
    }

    private function journaliser(DemandeDepense $demande, User $user, string $etape, string $decision, ?string $commentaire = null, ?string $signature = null): ValidationDepense
    {
        return ValidationDepense::create([
            'demande_depense_id' => $demande->id,
            'etape' => $etape,
            'decision' => $decision,
            'user_id' => $user->id,
            'commentaire' => $commentaire,
            'signature_path' => $signature !== null ? $this->enregistrerSignature($demande, $signature) : null,
            'hash_document' => $demande->hashDocument(),
            'ip' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 250, ''),
            'signe_le' => now(),
        ]);
    }

    /**
     * Enregistre la signature dessinée (data URL PNG) sur le disque privé.
     */
    private function enregistrerSignature(DemandeDepense $demande, string $signature): string
    {
        if (! preg_match('#^data:image/png;base64,(.+)$#', $signature, $matches)
            || ($png = base64_decode($matches[1], true)) === false
            || ! str_starts_with($png, "\x89PNG")) {
            throw new InvalidArgumentException('La signature est obligatoire.');
        }

        $chemin = "depenses/{$demande->id}/signatures/".Str::uuid().'.png';
        Storage::disk(self::DISQUE)->put($chemin, $png);

        return $chemin;
    }

    /**
     * La demande est approuvée : le demandeur doit signer sa décharge, la comptabilité
     * n'est prévenue (« a_payer ») qu'après sa signature — voir signerParDemandeur().
     */
    private function notifierApprobation(DemandeDepense $demande): void
    {
        $this->notifier($this->demandeurs($demande), $demande, 'a_signer');
    }

    private function comptables(DemandeDepense $demande)
    {
        return User::where('entreprise_id', $demande->entreprise_id)
            ->where('role', 'comptable')
            ->where('statut', 'actif')
            ->get();
    }

    private function demandeurs(DemandeDepense $demande)
    {
        return collect([$demande->creePar, $demande->demandeur?->user])->filter()->unique('id');
    }

    /**
     * Un e-mail qui ne part pas ne doit jamais bloquer une validation déjà enregistrée.
     */
    private function notifier($destinataires, DemandeDepense $demande, string $evenement, ?string $motif = null): void
    {
        try {
            Notification::send($destinataires, new DepenseNotification($demande, $evenement, $motif));
        } catch (\Throwable $e) {
            Log::warning('Notification de dépense non envoyée', [
                'demande' => $demande->reference,
                'evenement' => $evenement,
                'erreur' => $e->getMessage(),
            ]);
        }
    }
}
