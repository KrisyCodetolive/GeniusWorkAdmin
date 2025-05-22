<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Facturation;
use App\Models\Abonnement;
use App\Models\User;
use App\Models\Entreprise;
use App\Notifications\PaiementSuccessNotification;
use App\Notifications\PaiementFailedNotification;
use App\Notifications\PaiementPendingNotification;
use App\Traits\BelongsToEntreprise;

class Paiement extends Model
{
    use HasFactory, SoftDeletes, HasUuids, BelongsToEntreprise;

    protected $fillable = [
        'facturation_id',
        'abonnement_id',
        'entreprise_id',
        'initiateur_id',
        'validateur_id',
        'reference',
        'reference_externe',
        'montant',
        'devise',
        'methode',
        'passerelle',
        'statut',
        'date_paiement',
        'date_validation',
        'meta_donnees',
        'commentaire'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'datetime',
        'date_validation' => 'datetime',
        'meta_donnees' => 'json'
    ];

    /**
     * Les statuts possibles pour un paiement
     */
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_TRAITEMENT = 'en_traitement';
    const STATUT_COMPLETE = 'complete';
    const STATUT_ECHOUE = 'echoue';
    const STATUT_REMBOURSE = 'rembourse';
    const STATUT_ANNULE = 'annule';
    const STATUT_REJETE = 'rejete';

    /**
     * Les méthodes de paiement disponibles
     */
    const METHODE_CARTE = 'card';
    const METHODE_MOBILE_MONEY = 'mobile_money';
    const METHODE_VIREMENT = 'virement';
    const METHODE_ESPECES = 'especes';
    const METHODE_CHEQUE = 'cheque';
    const METHODE_AUTRE = 'autre';

    /**
     * Les passerelles de paiement disponibles
     */
    const PASSERELLE_PAYSTACK = 'paystack';
    const PASSERELLE_STRIPE = 'stripe';
    const PASSERELLE_MANUEL = 'manuel';
    const PASSERELLE_AUTRE = 'autre';

    /**
     * Relations
     */
    public function facturation()
    {
        return $this->belongsTo(Facturation::class);
    }

    public function abonnement()
    {
        return $this->belongsTo(Abonnement::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    public function initiateur()
    {
        return $this->belongsTo(User::class, 'initiateur_id');
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    /**
     * Scopes
     */
    public function scopeEnAttente($query)
    {
        return $query->where('statut', self::STATUT_EN_ATTENTE);
    }

    public function scopeComplete($query)
    {
        return $query->where('statut', self::STATUT_COMPLETE);
    }

    public function scopeEchoue($query)
    {
        return $query->where('statut', self::STATUT_ECHOUE);
    }

    public function scopeParEntreprise($query, $entrepriseId)
    {
        return $query->where('entreprise_id', $entrepriseId);
    }

    public function scopeParMethode($query, $methode)
    {
        return $query->where('methode', $methode);
    }

    public function scopeParPasserelle($query, $passerelle)
    {
        return $query->where('passerelle', $passerelle);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_paiement', [$debut, $fin]);
    }

    /**
     * Méthodes
     */
    public function valider($validateurId, $commentaire = null)
    {
        $this->update([
            'statut' => self::STATUT_COMPLETE,
            'validateur_id' => $validateurId,
            'date_validation' => now(),
            'commentaire' => $commentaire ?? $this->commentaire
        ]);

        // Mettre à jour le statut de la facturation
        if ($this->facturation) {
            $this->facturation->update([
                'statut_paiement' => 'payé'
            ]);
        }

        // Mettre à jour le statut de l'abonnement si nécessaire
        if ($this->abonnement && $this->abonnement->statut !== 'actif') {
            $this->abonnement->update([
                'statut' => 'actif'
            ]);
        }

        // Envoyer une notification de succès
        $this->sendPaymentNotification('success');

        // Compléter le workflow après paiement validé
        app(\App\Services\WorkflowService::class)->completeWorkflowAfterPayment($this);

        return $this;
    }

    public function rejeter($validateurId, $commentaire)
    {
        $this->update([
            'statut' => self::STATUT_REJETE,
            'validateur_id' => $validateurId,
            'date_validation' => now(),
            'commentaire' => $commentaire
        ]);

        // Envoyer une notification d'échec
        $this->sendPaymentNotification('failed', $commentaire);

        return $this;
    }

    public function annuler($commentaire = null)
    {
        $this->update([
            'statut' => self::STATUT_ANNULE,
            'commentaire' => $commentaire ?? $this->commentaire
        ]);

        // Envoyer une notification d'échec
        $this->sendPaymentNotification('failed', $commentaire ?? 'Paiement annulé');

        return $this;
    }

    public function rembourser($commentaire = null)
    {
        $this->update([
            'statut' => self::STATUT_REMBOURSE,
            'commentaire' => $commentaire ?? $this->commentaire
        ]);

        return $this;
    }

    /**
     * Envoyer une notification selon le statut du paiement
     *
     * @param string $type Type de notification (success, failed, pending)
     * @param string $message Message d'erreur optionnel pour les notifications d'échec
     * @return void
     */
    public function sendPaymentNotification($type, $message = '')
    {
        // Si pas d'initiateur, on ne peut pas envoyer de notification
        if (!$this->initiateur) {
            return;
        }

        switch ($type) {
            case 'success':
                $this->initiateur->notify(new PaiementSuccessNotification($this));
                break;
            case 'failed':
                $this->initiateur->notify(new PaiementFailedNotification($this, $message));
                break;
            case 'pending':
                $this->initiateur->notify(new PaiementPendingNotification($this));
                break;
        }

        // Si l'entreprise a un administrateur différent de l'initiateur, on le notifie aussi
        if ($this->entreprise && $this->entreprise->admin_id && $this->entreprise->admin_id !== $this->initiateur_id) {
            $admin = User::find($this->entreprise->admin_id);
            if ($admin) {
                switch ($type) {
                    case 'success':
                        $admin->notify(new PaiementSuccessNotification($this));
                        break;
                    case 'failed':
                        $admin->notify(new PaiementFailedNotification($this, $message));
                        break;
                    case 'pending':
                        $admin->notify(new PaiementPendingNotification($this));
                        break;
                }
            }
        }
    }

    public function estEnAttente()
    {
        return $this->statut === self::STATUT_EN_ATTENTE;
    }

    public function estComplete()
    {
        return $this->statut === self::STATUT_COMPLETE;
    }

    public function estEchoue()
    {
        return $this->statut === self::STATUT_ECHOUE;
    }

    public function estRejete()
    {
        return $this->statut === self::STATUT_REJETE;
    }

    public function estAnnule()
    {
        return $this->statut === self::STATUT_ANNULE;
    }

    public function estRembourse()
    {
        return $this->statut === self::STATUT_REMBOURSE;
    }

    public function necessiteValidation()
    {
        return $this->passerelle === self::PASSERELLE_MANUEL && $this->estEnAttente();
    }

    /**
     * Générer une référence unique pour le paiement
     */
    public static function genererReference()
    {
        $prefix = 'PAY';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }
}
