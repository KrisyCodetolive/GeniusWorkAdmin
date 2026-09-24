<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Une étape du circuit (validation, rejet, renvoi, décaissement).
 * Journal en ajout seul : une ligne enregistrée ne peut plus être modifiée ni supprimée.
 */
class ValidationDepense extends Model
{
    use HasUuids;

    public const ETAPE_SOUMISSION = 'soumission';

    public const ETAPE_COMPTABILITE = 'comptabilite';

    public const ETAPE_CEO = 'ceo';

    public const ETAPE_DECAISSEMENT = 'decaissement';

    public const ETAPE_ANNULATION = 'annulation';

    public const DECISION_APPROUVE = 'approuve';

    public const DECISION_REJETE = 'rejete';

    public const DECISION_RENVOYE = 'renvoye';

    public const DECISION_EFFECTUE = 'effectue';

    public const ETAPES = [
        self::ETAPE_SOUMISSION => 'Soumission',
        self::ETAPE_COMPTABILITE => 'Comptabilité',
        self::ETAPE_CEO => 'CEO',
        self::ETAPE_DECAISSEMENT => 'Décaissement',
        self::ETAPE_ANNULATION => 'Annulation',
    ];

    public const DECISIONS = [
        self::DECISION_APPROUVE => 'Approuvé',
        self::DECISION_REJETE => 'Rejeté',
        self::DECISION_RENVOYE => 'Renvoyé pour correction',
        self::DECISION_EFFECTUE => 'Effectué',
    ];

    protected $table = 'validations_depense';

    protected $fillable = [
        'demande_depense_id',
        'etape',
        'decision',
        'user_id',
        'commentaire',
        'signature_path',
        'hash_document',
        'ip',
        'user_agent',
        'signe_le',
    ];

    protected $casts = [
        'signe_le' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Une étape de validation ne peut pas être modifiée.'));
        static::deleting(fn () => throw new LogicException('Une étape de validation ne peut pas être supprimée.'));
    }

    public function demande()
    {
        return $this->belongsTo(DemandeDepense::class, 'demande_depense_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * La demande a-t-elle changé depuis cette signature ?
     */
    public function hashValide(): bool
    {
        return $this->hash_document !== null
            && hash_equals($this->hash_document, $this->demande->hashDocument());
    }
}
