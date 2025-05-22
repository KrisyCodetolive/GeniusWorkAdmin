<?php

namespace App\Services\Paiement;

use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService implements PasserelleInterface
{
    protected $baseUrl;
    protected $secretKey;
    protected $publicKey;

    public function __construct()
    {
        $this->baseUrl = config('paiement.paystack.base_url', 'https://api.paystack.co');
        $this->secretKey = config('paiement.paystack.secret_key');
        $this->publicKey = config('paiement.paystack.public_key');
    }

    /**
     * Initialiser un paiement avec Paystack
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
                'passerelle' => Paiement::PASSERELLE_PAYSTACK,
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

            // Préparer les données pour Paystack
            $amount = $facturation->montant_ttc * 100; // Paystack utilise les centimes
            $currency = $facturation->devise === 'FCFA' ? 'XOF' : $facturation->devise;
            
            // Faire l'appel API à Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/transaction/initialize', [
                'amount' => $amount,
                'email' => $initiateur->email,
                'currency' => $currency,
                'reference' => $paiement->reference,
                'callback_url' => route('paiement.paystack.callback'),
                'metadata' => [
                    'facturation_id' => $facturation->id,
                    'paiement_id' => $paiement->id,
                    'custom_fields' => [
                        [
                            'display_name' => 'Numéro de facture',
                            'variable_name' => 'invoice_number',
                            'value' => $facturation->numero_facture
                        ],
                        [
                            'display_name' => 'Entreprise',
                            'variable_name' => 'company_name',
                            'value' => $facturation->entreprise->nom ?? 'N/A'
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Mettre à jour le paiement avec les données de Paystack
                $paiement->update([
                    'reference_externe' => $responseData['data']['reference'] ?? null,
                    'statut' => Paiement::STATUT_TRAITEMENT,
                    'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                        'paystack_response' => $responseData,
                        'authorization_url' => $responseData['data']['authorization_url'] ?? null,
                        'access_code' => $responseData['data']['access_code'] ?? null
                    ])
                ]);

                return [
                    'success' => true,
                    'message' => 'Paiement initialisé avec succès',
                    'paiement' => $paiement,
                    'redirect_url' => $responseData['data']['authorization_url'] ?? null,
                    'reference' => $paiement->reference
                ];
            } else {
                // En cas d'échec, mettre à jour le paiement
                $responseData = $response->json();
                $paiement->update([
                    'statut' => Paiement::STATUT_ECHOUE,
                    'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                        'paystack_error' => $responseData
                    ]),
                    'commentaire' => 'Erreur lors de l\'initialisation du paiement: ' . ($responseData['message'] ?? 'Erreur inconnue')
                ]);

                Log::error('Erreur Paystack', [
                    'response' => $responseData,
                    'facturation_id' => $facturation->id
                ]);

                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'initialisation du paiement: ' . ($responseData['message'] ?? 'Erreur inconnue'),
                    'paiement' => $paiement
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'initialisation du paiement Paystack', [
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
            $reference = $paiement->reference_externe ?? $paiement->reference;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get($this->baseUrl . '/transaction/verify/' . $reference);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Mettre à jour le paiement avec les données de Paystack
                $status = $responseData['data']['status'] ?? 'unknown';
                $newStatus = $this->mapPaystackStatus($status);
                
                $paiement->update([
                    'statut' => $newStatus,
                    'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                        'paystack_verification' => $responseData
                    ])
                ]);

                // Si le paiement est complet, mettre à jour la facturation
                if ($newStatus === Paiement::STATUT_COMPLETE) {
                    $this->finaliserPaiement($paiement);
                }

                return [
                    'success' => true,
                    'message' => 'Statut vérifié avec succès',
                    'paiement' => $paiement,
                    'status' => $newStatus,
                    'paystack_status' => $status
                ];
            } else {
                $responseData = $response->json();
                
                Log::error('Erreur lors de la vérification du statut Paystack', [
                    'response' => $responseData,
                    'paiement_id' => $paiement->id
                ]);

                return [
                    'success' => false,
                    'message' => 'Erreur lors de la vérification: ' . ($responseData['message'] ?? 'Erreur inconnue'),
                    'paiement' => $paiement
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de la vérification du statut Paystack', [
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
     * Traiter la réponse de Paystack (callback)
     *
     * @param array $donnees
     * @return array
     */
    public function traiterReponse(array $donnees): array
    {
        try {
            $reference = $donnees['reference'] ?? null;
            
            if (!$reference) {
                return [
                    'success' => false,
                    'message' => 'Référence manquante dans la réponse'
                ];
            }

            // Trouver le paiement correspondant
            $paiement = Paiement::where('reference', $reference)
                ->orWhere('reference_externe', $reference)
                ->first();

            if (!$paiement) {
                return [
                    'success' => false,
                    'message' => 'Paiement non trouvé pour la référence: ' . $reference
                ];
            }

            // Vérifier le statut du paiement auprès de Paystack
            $result = $this->verifierStatut($paiement);
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Exception lors du traitement de la réponse Paystack', [
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
        // Paystack ne permet pas d'annuler une transaction en cours
        // On peut seulement mettre à jour notre statut local
        
        $paiement->update([
            'statut' => Paiement::STATUT_ANNULE,
            'commentaire' => $raison ?? 'Annulé par l\'utilisateur',
            'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                'annulation' => [
                    'date' => now()->toIso8601String(),
                    'raison' => $raison ?? 'Annulé par l\'utilisateur'
                ]
            ])
        ]);

        return true;
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
            $reference = $paiement->reference_externe ?? $paiement->reference;
            $montantRemboursement = $montant ?? $paiement->montant;

            // Paystack nécessite que le montant soit en centimes
            $amount = $montantRemboursement * 100;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/refund', [
                'transaction' => $reference,
                'amount' => $amount,
                'currency' => $paiement->devise === 'FCFA' ? 'XOF' : $paiement->devise,
                'merchant_note' => $raison ?? 'Remboursement demandé par l\'administrateur'
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Mettre à jour le paiement
                $paiement->update([
                    'statut' => Paiement::STATUT_REMBOURSE,
                    'commentaire' => $raison ?? 'Remboursement effectué',
                    'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                        'remboursement' => [
                            'date' => now()->toIso8601String(),
                            'montant' => $montantRemboursement,
                            'raison' => $raison ?? 'Remboursement demandé',
                            'paystack_response' => $responseData
                        ]
                    ])
                ]);

                return true;
            } else {
                $responseData = $response->json();
                
                Log::error('Erreur lors du remboursement Paystack', [
                    'response' => $responseData,
                    'paiement_id' => $paiement->id
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception lors du remboursement Paystack', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);

            return false;
        }
    }

    /**
     * Mapper les statuts Paystack aux statuts de notre application
     *
     * @param string $paystackStatus
     * @return string
     */
    protected function mapPaystackStatus(string $paystackStatus): string
    {
        switch (strtolower($paystackStatus)) {
            case 'success':
                return Paiement::STATUT_COMPLETE;
            case 'failed':
                return Paiement::STATUT_ECHOUE;
            case 'abandoned':
                return Paiement::STATUT_ANNULE;
            case 'pending':
                return Paiement::STATUT_TRAITEMENT;
            default:
                return Paiement::STATUT_EN_ATTENTE;
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
                'statut_paiement' => 'payee', // Correction: utiliser 'payee' selon l'enum défini
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
                'statut' => 'actif',
                'mode_paiement' => $modePaiement,
                'reference_paiement' => $paiement->reference
            ]);
            
            // Renouveler l'abonnement en mensuel (mise à jour des dates et du statut)
            $paiement->abonnement->renouveler('mensuel');
            
            Log::info('Abonnement renouvelé avec succès', [
                'abonnement_id' => $paiement->abonnement->id,
                'date_debut' => $paiement->abonnement->date_debut,
                'date_fin' => $paiement->abonnement->date_fin
            ]);
        }

        Log::info('Paiement finalisé avec succès', [
            'paiement_id' => $paiement->id,
            'reference' => $paiement->reference
        ]);
    }
}
