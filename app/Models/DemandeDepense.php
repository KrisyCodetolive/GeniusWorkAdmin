<?php

namespace App\Models;

use App\Traits\BelongsToEntreprise;
use App\Traits\Synchronisable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeDepense extends Model
{
    use BelongsToEntreprise, HasUuids, SoftDeletes, Synchronisable;

    public const STATUT_BROUILLON = 'brouillon';

    public const STATUT_EN_ATTENTE_COMPTABLE = 'en_attente_comptable';

    public const STATUT_EN_ATTENTE_CEO = 'en_attente_ceo';

    public const STATUT_APPROUVEE = 'approuvee';

    public const STATUT_PAYEE = 'payee';

    public const STATUT_JUSTIFICATION_SOUMISE = 'justification_soumise';

    public const STATUT_CLOTUREE = 'cloturee';

    public const STATUT_REJETEE = 'rejetee';

    public const STATUT_ANNULEE = 'annulee';

    public const STATUTS = [
        self::STATUT_BROUILLON => 'Brouillon',
        self::STATUT_EN_ATTENTE_COMPTABLE => 'En attente comptabilité',
        self::STATUT_EN_ATTENTE_CEO => 'En attente CEO',
        self::STATUT_APPROUVEE => 'Approuvée (à payer)',
        self::STATUT_PAYEE => 'Payée (à justifier)',
        self::STATUT_JUSTIFICATION_SOUMISE => 'Justification à vérifier',
        self::STATUT_CLOTUREE => 'Clôturée',
        self::STATUT_REJETEE => 'Rejetée',
        self::STATUT_ANNULEE => 'Annulée',
    ];

    public const COULEURS_STATUT = [
        self::STATUT_BROUILLON => 'gray',
        self::STATUT_EN_ATTENTE_COMPTABLE => 'warning',
        self::STATUT_EN_ATTENTE_CEO => 'warning',
        self::STATUT_APPROUVEE => 'info',
        self::STATUT_PAYEE => 'info',
        self::STATUT_JUSTIFICATION_SOUMISE => 'warning',
        self::STATUT_CLOTUREE => 'success',
        self::STATUT_REJETEE => 'danger',
        self::STATUT_ANNULEE => 'gray',
    ];

    public const ETAPES_SUIVI = ['Envoi', 'Validation', 'Signature', 'Paiement', 'Justification'];

    public const MODES_PAIEMENT = [
        'especes' => 'Espèces',
        'virement' => 'Virement bancaire',
        'cheque' => 'Chèque',
        'mobile_money' => 'Mobile Money',
    ];

    protected $table = 'demandes_depense';

    protected $fillable = [
        'entreprise_id',
        'reference',
        'demandeur_id',
        'cree_par_user_id',
        'categorie_depense_id',
        'departement_id',
        'objet',
        'description',
        'montant',
        'devise',
        'beneficiaire',
        'date_besoin',
        'statut',
        'mode_paiement',
        'reference_paiement',
        'date_paiement',
        'payee_par_user_id',
        'preuve_paiement',
        'pdf_bon_sortie',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_besoin' => 'date',
        'date_paiement' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (DemandeDepense $demande) {
            $demande->statut ??= self::STATUT_BROUILLON;

            $parametres = ParametreDepense::pour($demande->entreprise_id);
            $demande->devise ??= $parametres->devise;
            $demande->reference ??= static::prochaineReference($demande->entreprise_id, $parametres->prefixe_reference);
        });
    }

    /**
     * Référence séquentielle par entreprise et par année : DEP-2026-0001.
     * L'index unique (entreprise_id, reference) empêche tout doublon en cas de création simultanée.
     */
    public static function prochaineReference(string $entrepriseId, string $prefixe): string
    {
        $debut = sprintf('%s-%s-', $prefixe, now()->year);

        $derniere = static::withoutGlobalScopes()
            ->withTrashed()
            ->where('entreprise_id', $entrepriseId)
            ->where('reference', 'like', $debut.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $numero = $derniere ? ((int) substr($derniere, strlen($debut))) + 1 : 1;

        return $debut.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
    }

    // Relations

    public function demandeur()
    {
        return $this->belongsTo(Employeur::class, 'demandeur_id');
    }

    public function creePar()
    {
        return $this->belongsTo(User::class, 'cree_par_user_id');
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieDepense::class, 'categorie_depense_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class);
    }

    public function payeePar()
    {
        return $this->belongsTo(User::class, 'payee_par_user_id');
    }

    public function justificatifs()
    {
        return $this->hasMany(JustificatifDepense::class);
    }

    public function validations()
    {
        return $this->hasMany(ValidationDepense::class)->orderBy('signe_le');
    }

    // Helpers

    public function parametres(): ParametreDepense
    {
        return ParametreDepense::pour($this->entreprise_id);
    }

    public function necessiteValidationCeo(): bool
    {
        return (float) $this->montant > (float) $this->parametres()->seuil_validation_ceo;
    }

    /**
     * Dernière approbation signée d'une étape (comptabilité ou CEO), depuis la dernière soumission.
     */
    public function approbation(string $etape): ?ValidationDepense
    {
        return $this->validations()
            ->where('etape', $etape)
            ->where('decision', ValidationDepense::DECISION_APPROUVE)
            ->reorder('signe_le', 'desc')
            ->first();
    }

    /**
     * Signature de décharge du demandeur, requise après approbation pour autoriser le paiement.
     */
    public function signatureDemandeur(): ?ValidationDepense
    {
        return $this->validations()
            ->where('etape', ValidationDepense::ETAPE_SIGNATURE_DEMANDEUR)
            ->reorder('signe_le', 'desc')
            ->first();
    }

    /**
     * Empreinte du contenu engagé par la signature. Le statut et le paiement n'en font pas partie :
     * ils changent au fil du circuit sans modifier ce qui a été signé.
     */
    public function hashDocument(): string
    {
        return hash('sha256', json_encode([
            'id' => $this->id,
            'entreprise_id' => $this->entreprise_id,
            'reference' => $this->reference,
            'demandeur_id' => $this->demandeur_id,
            'categorie_depense_id' => $this->categorie_depense_id,
            'objet' => $this->objet,
            'description' => $this->description,
            'montant' => number_format((float) $this->montant, 2, '.', ''),
            'devise' => $this->devise,
            'beneficiaire' => $this->beneficiaire,
        ]));
    }

    public function montantEnLettres(): string
    {
        $formatter = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);

        return ucfirst($formatter->format((int) round((float) $this->montant))).' '.$this->devise;
    }

    public function nomDemandeur(): string
    {
        return $this->demandeur?->nom_complet ?? $this->creePar?->name ?? '—';
    }

    public function estModifiable(): bool
    {
        return $this->statut === self::STATUT_BROUILLON;
    }

    public function estAuteur(User $user): bool
    {
        return $this->cree_par_user_id === $user->id
            || ($user->employeur_id !== null && $user->employeur_id === $this->demandeur_id);
    }

    /**
     * La comptabilité a mené elle-même une étape du demandeur (envoi, décharge, justification) :
     * elle ne peut alors plus valider cette demande, pour garder deux personnes distinctes.
     */
    public function aAgiPourLeDemandeur(User $user): bool
    {
        return ! $this->estAuteur($user)
            && $this->validations()
                ->where('user_id', $user->id)
                ->where('decision', ValidationDepense::DECISION_EFFECTUE)
                ->whereIn('etape', [
                    ValidationDepense::ETAPE_SOUMISSION,
                    ValidationDepense::ETAPE_SIGNATURE_DEMANDEUR,
                    ValidationDepense::ETAPE_JUSTIFICATION,
                ])
                ->exists();
    }

    /**
     * Position dans ETAPES_SUIVI (égale au nombre d'étapes si clôturée), null si la demande est sortie du circuit.
     */
    public function etapeSuivi(): ?int
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 0,
            self::STATUT_EN_ATTENTE_COMPTABLE, self::STATUT_EN_ATTENTE_CEO => 1,
            self::STATUT_APPROUVEE => 2,
            self::STATUT_PAYEE => 3,
            self::STATUT_JUSTIFICATION_SOUMISE => 4,
            self::STATUT_CLOTUREE => count(self::ETAPES_SUIVI),
            default => null,
        };
    }

    /**
     * Ce que le demandeur doit faire lui-même à ce stade, null s'il n'a qu'à attendre.
     */
    public function actionRequise(): ?string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'À envoyer',
            self::STATUT_APPROUVEE => 'À signer',
            self::STATUT_PAYEE => 'Reçus à joindre',
            default => null,
        };
    }

    /**
     * Phrase de suivi : où en est la demande et qui doit agir.
     */
    public function messageSuivi(): string
    {
        return match ($this->statut) {
            self::STATUT_BROUILLON => 'Pas encore envoyée à la comptabilité.',
            self::STATUT_EN_ATTENTE_COMPTABLE => 'La comptabilité examine votre demande.',
            self::STATUT_EN_ATTENTE_CEO => 'Validée par la comptabilité, en attente du CEO.',
            self::STATUT_APPROUVEE => 'Approuvée : signez votre décharge pour autoriser le paiement.',
            self::STATUT_PAYEE => 'Argent remis : joignez vos reçus pour justifier la dépense.',
            self::STATUT_JUSTIFICATION_SOUMISE => 'La comptabilité vérifie vos reçus.',
            self::STATUT_CLOTUREE => 'Dossier clôturé, justificatifs validés.',
            self::STATUT_REJETEE => 'Demande rejetée.',
            self::STATUT_ANNULEE => 'Demande annulée.',
            default => '',
        };
    }
}
