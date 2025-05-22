<?php

namespace App\Services\Paiement;

use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\Refund;

class StripeService implements PasserelleInterface
{
    protected $baseUrl;
    protected $secretKey;
    protected $publicKey;
    protected $webhookSecret;

    public function __construct()
    {
        $this->baseUrl = config('paiement.stripe.base_url', 'https://api.stripe.com');
        $this->secretKey = config('paiement.stripe.secret_key');
        $this->publicKey = config('paiement.stripe.public_key');
        $this->webhookSecret = config('paiement.stripe.webhook_secret');
        
        // Initialiser Stripe avec la clé secrète
        Stripe::setApiKey($this->secretKey);
    }

    /**
     * Initialiser un paiement avec Stripe
     *
     * @param Facturation $facturation
     * @param User $initiateur
     * @param array $options
     * @return array
     */
    public function initialiser(Facturation $facturation, User $initiateur, array $options = []): array
    {
        try {
            // Créer un enregistrement de paiement
            $paiement = Paiement::create([
                'facturation_id' => $facturation->id,
                'abonnement_id' => $facturation->abonnement_id,
                'entreprise_id' => $facturation->entreprise_id,
                'initiateur_id' => $initiateur->id,
                'reference' => Paiement::genererReference(),
                'montant' => $facturation->montant_ttc,
                'devise' => $facturation->devise,
                'methode' => $options['methode'] ?? Paiement::METHODE_CARTE,
                'passerelle' => Paiement::PASSERELLE_STRIPE,
                'statut' => Paiement::STATUT_EN_ATTENTE,
                'date_paiement' => now(),
                'meta_donnees' => [
                    'email' => $initiateur->email,
                    'nom' => $initiateur->name,
                    'telephone' => $initiateur->telephone ?? null,
                    'facturation_numero' => $facturation->numero_facture,
                    'options' => $options
                ]
            ]);

            // Préparer les données pour Stripe
            $amount = $this->convertirEnCentimes($facturation->montant_ttc, $facturation->devise);
            $currency = $this->convertirDevise($facturation->devise);
            
            // Créer un PaymentIntent avec Stripe
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => $currency,
                'description' => 'Facture #' . $facturation->numero_facture,
                'metadata' => [
                    'facturation_id' => $facturation->id,
                    'paiement_id' => $paiement->id,
                    'reference' => $paiement->reference,
                    'entreprise_id' => $facturation->entreprise_id,
                    'entreprise_nom' => $facturation->entreprise->nom ?? 'N/A'
                ],
                'receipt_email' => $initiateur->email,
                'statement_descriptor' => substr('GENIUS WORK: ' . ($facturation->entreprise->nom ?? 'Abonnement'), 0, 22)
            ]);

            // Mettre à jour le paiement avec les données de Stripe
            $paiement->update([
                'reference_externe' => $paymentIntent->id,
                'statut' => Paiement::STATUT_TRAITEMENT,
                'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                    'stripe_payment_intent' => [
                        'id' => $paymentIntent->id,
                        'client_secret' => $paymentIntent->client_secret,
                        'status' => $paymentIntent->status
                    ]
                ])
            ]);

            return [
                'success' => true,
                'message' => 'Paiement initialisé avec succès',
                'paiement' => $paiement,
                'client_secret' => $paymentIntent->client_secret,
                'public_key' => $this->publicKey,
                'reference' => $paiement->reference
            ];
        } catch (ApiErrorException $e) {
            // Gérer les erreurs spécifiques à l'API Stripe
            if ($paiement ?? null) {
                $paiement->update([
                    'statut' => Paiement::STATUT_ECHOUE,
                    'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                        'stripe_error' => [
                            'type' => $e->getStripeCode(),
                            'message' => $e->getMessage(),
                            'code' => $e->getHttpStatus()
                        ]
                    ]),
                    'commentaire' => 'Erreur Stripe: ' . $e->getMessage()
                ]);
            }

            Log::error('Erreur Stripe API', [
                'message' => $e->getMessage(),
                'code' => $e->getStripeCode(),
                'http_status' => $e->getHttpStatus(),
                'facturation_id' => $facturation->id
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation du paiement: ' . $e->getMessage(),
                'paiement' => $paiement ?? null
            ];
        } catch (\Exception $e) {
            // Gérer les autres exceptions
            Log::error('Exception lors de l\'initialisation du paiement Stripe', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'facturation_id' => $facturation->id
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Vérifier le statut d'un paiement
     *
     * @param Paiement $paiement
     * @return array
     */
    public function verifierStatut(Paiement $paiement): array
    {
        try {
            $paymentIntentId = $paiement->reference_externe;
            
            if (!$paymentIntentId) {
                return [
                    'success' => false,
                    'message' => 'Référence externe manquante pour ce paiement',
                    'paiement' => $paiement
                ];
            }

            // Récupérer le PaymentIntent depuis Stripe
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            // Mettre à jour le statut du paiement
            $newStatus = $this->mapStripeStatus($paymentIntent->status);
            
            $paiement->update([
                'statut' => $newStatus,
                'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                    'stripe_verification' => [
                        'status' => $paymentIntent->status,
                        'amount_received' => $paymentIntent->amount_received,
                        'date_verification' => now()->toIso8601String()
                    ]
                ])
            ]);

            // Si le paiement est complet, finaliser le paiement
            if ($newStatus === Paiement::STATUT_COMPLETE) {
                $this->finaliserPaiement($paiement);
            }

            return [
                'success' => true,
                'message' => 'Statut vérifié avec succès',
                'paiement' => $paiement,
                'status' => $newStatus,
                'stripe_status' => $paymentIntent->status
            ];
        } catch (ApiErrorException $e) {
            Log::error('Erreur Stripe API lors de la vérification du statut', [
                'message' => $e->getMessage(),
                'code' => $e->getStripeCode(),
                'http_status' => $e->getHttpStatus(),
                'paiement_id' => $paiement->id
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage(),
                'paiement' => $paiement
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors de la vérification du statut Stripe', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Traiter la réponse de Stripe (webhook)
     *
     * @param array $donnees
     * @return array
     */
    public function traiterReponse(array $donnees): array
    {
        try {
            // Vérifier la signature du webhook si disponible
            if (isset($donnees['stripe_signature']) && $this->webhookSecret) {
                $event = \Stripe\Webhook::constructEvent(
                    $donnees['payload'] ?? json_encode($donnees),
                    $donnees['stripe_signature'],
                    $this->webhookSecret
                );
                $eventData = $event->data->object;
            } else {
                // Si pas de signature, utiliser les données brutes
                $eventData = $donnees;
            }

            // Récupérer l'ID du PaymentIntent
            $paymentIntentId = $eventData['id'] ?? $eventData['payment_intent'] ?? null;
            
            if (!$paymentIntentId) {
                return [
                    'success' => false,
                    'message' => 'ID de PaymentIntent manquant dans la réponse'
                ];
            }

            // Trouver le paiement correspondant
            $paiement = Paiement::where('reference_externe', $paymentIntentId)->first();

            if (!$paiement) {
                return [
                    'success' => false,
                    'message' => 'Paiement non trouvé pour l\'ID: ' . $paymentIntentId
                ];
            }

            // Vérifier le statut du paiement
            return $this->verifierStatut($paiement);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Erreur de vérification de signature Stripe', [
                'message' => $e->getMessage(),
                'donnees' => $donnees
            ]);

            return [
                'success' => false,
                'message' => 'Erreur de vérification de signature: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Exception lors du traitement de la réponse Stripe', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'donnees' => $donnees
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage(),
                'exception' => $e->getMessage()
            ];
        }
    }

    /**
     * Annuler un paiement
     *
     * @param Paiement $paiement
     * @param string|null $raison
     * @return bool
     */
    public function annuler(Paiement $paiement, ?string $raison = null): bool
    {
        try {
            $paymentIntentId = $paiement->reference_externe;
            
            if (!$paymentIntentId) {
                return false;
            }

            // Récupérer le PaymentIntent
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            // Annuler le PaymentIntent si possible
            if ($paymentIntent->status === 'requires_payment_method' || 
                $paymentIntent->status === 'requires_capture' || 
                $paymentIntent->status === 'requires_confirmation' ||
                $paymentIntent->status === 'requires_action') {
                $paymentIntent->cancel([
                    'cancellation_reason' => 'requested_by_customer'
                ]);
            }

            // Mettre à jour le paiement
            $paiement->update([
                'statut' => Paiement::STATUT_ANNULE,
                'commentaire' => $raison ?? 'Annulé par l\'utilisateur',
                'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                    'annulation' => [
                        'date' => now()->toIso8601String(),
                        'raison' => $raison ?? 'Annulé par l\'utilisateur',
                        'stripe_status' => $paymentIntent->status
                    ]
                ])
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Erreur Stripe API lors de l\'annulation', [
                'message' => $e->getMessage(),
                'code' => $e->getStripeCode(),
                'http_status' => $e->getHttpStatus(),
                'paiement_id' => $paiement->id
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'annulation du paiement Stripe', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);
            
            return false;
        }
    }

    /**
     * Rembourser un paiement
     *
     * @param Paiement $paiement
     * @param float|null $montant
     * @param string|null $raison
     * @return bool
     */
    public function rembourser(Paiement $paiement, ?float $montant = null, ?string $raison = null): bool
    {
        try {
            $paymentIntentId = $paiement->reference_externe;
            
            if (!$paymentIntentId) {
                return false;
            }

            // Récupérer le PaymentIntent
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            
            // Vérifier si le paiement peut être remboursé
            if ($paymentIntent->status !== 'succeeded') {
                return false;
            }

            // Calculer le montant à rembourser
            $montantRemboursement = $montant ?? $paiement->montant;
            $amount = $this->convertirEnCentimes($montantRemboursement, $paiement->devise);

            // Créer le remboursement
            $refund = Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'paiement_id' => $paiement->id,
                    'reference' => $paiement->reference,
                    'raison' => $raison ?? 'Remboursement demandé'
                ]
            ]);

            // Mettre à jour le paiement
            $paiement->update([
                'statut' => Paiement::STATUT_REMBOURSE,
                'commentaire' => $raison ?? 'Remboursement effectué',
                'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                    'remboursement' => [
                        'date' => now()->toIso8601String(),
                        'montant' => $montantRemboursement,
                        'raison' => $raison ?? 'Remboursement demandé',
                        'stripe_refund_id' => $refund->id,
                        'stripe_refund_status' => $refund->status
                    ]
                ])
            ]);

            return true;
        } catch (ApiErrorException $e) {
            Log::error('Erreur Stripe API lors du remboursement', [
                'message' => $e->getMessage(),
                'code' => $e->getStripeCode(),
                'http_status' => $e->getHttpStatus(),
                'paiement_id' => $paiement->id
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Exception lors du remboursement Stripe', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);
            
            return false;
        }
    }

    /**
     * Mapper les statuts Stripe aux statuts de notre application
     *
     * @param string $stripeStatus
     * @return string
     */
    protected function mapStripeStatus(string $stripeStatus): string
    {
        switch ($stripeStatus) {
            case 'succeeded':
                return Paiement::STATUT_COMPLETE;
            case 'processing':
                return Paiement::STATUT_TRAITEMENT;
            case 'requires_payment_method':
            case 'requires_confirmation':
            case 'requires_action':
            case 'requires_capture':
                return Paiement::STATUT_EN_ATTENTE;
            case 'canceled':
                return Paiement::STATUT_ANNULE;
            default:
                return Paiement::STATUT_ECHOUE;
        }
    }

    /**
     * Finaliser un paiement réussi
     *
     * @param Paiement $paiement
     * @return void
     */
    protected function finaliserPaiement(Paiement $paiement): void
    {
        // Mettre à jour la facturation
        if ($paiement->facturation) {
            $paiement->facturation->update([
                'statut_paiement' => 'payé',
                'mode_paiement' => $paiement->methode,
                'reference_paiement' => $paiement->reference
            ]);
        }

        // Mettre à jour l'abonnement
        if ($paiement->abonnement && $paiement->abonnement->statut !== 'actif') {
            // Mapper la méthode de paiement aux valeurs acceptées par l'enum
            $modePaiement = 'non_specifie';
            if ($paiement->methode === 'card') {
                $modePaiement = 'carte';
            } elseif ($paiement->methode === 'bank_transfer') {
                $modePaiement = 'virement';
            } elseif ($paiement->methode === 'cash') {
                $modePaiement = 'especes';
            }
            
            // Mettre à jour le mode de paiement et la référence
            $paiement->abonnement->update([
                'mode_paiement' => $modePaiement,
                'reference_paiement' => $paiement->reference
            ]);
            
            // Renouveler l'abonnement en mensuel (mise à jour des dates et du statut)
            $paiement->abonnement->renouveler('mensuel');
            
            Log::info('Abonnement renouvelé avec succès via Stripe', [
                'abonnement_id' => $paiement->abonnement->id,
                'date_debut' => $paiement->abonnement->date_debut,
                'date_fin' => $paiement->abonnement->date_fin
            ]);
        }
    }

    /**
     * Convertir un montant en centimes pour Stripe
     *
     * @param float $montant
     * @param string $devise
     * @return int
     */
    protected function convertirEnCentimes(float $montant, string $devise): int
    {
        // Stripe utilise les centimes pour toutes les devises
        return (int) ($montant * 100);
    }

    /**
     * Convertir la devise au format Stripe
     *
     * @param string $devise
     * @return string
     */
    protected function convertirDevise(string $devise): string
    {
        // Mapper les devises spécifiques
        $mapping = [
            'FCFA' => 'XOF',
            'CFA' => 'XOF',
            'EUR' => 'EUR',
            'USD' => 'USD'
        ];

        return $mapping[$devise] ?? 'XOF';
    }
}
