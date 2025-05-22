<?php

namespace App\Services\Paiement;

use App\Models\Facturation;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PaiementService
{
    /**
     * Obtenir l'instance de la passerelle de paiement
     *
     * @param string|null $passerelle
     * @return PasserelleInterface
     * @throws \Exception
     */
    public function getPasserelle(?string $passerelle = null): PasserelleInterface
    {
        // Utiliser la passerelle spécifiée ou la passerelle par défaut
        $passerelle = $passerelle ?? config('paiement.default_gateway', 'paystack');

        switch ($passerelle) {
            case Paiement::PASSERELLE_PAYSTACK:
                return app(PaystackService::class);
            case Paiement::PASSERELLE_STRIPE:
                return app(StripeService::class);
            case Paiement::PASSERELLE_MANUEL:
                return app(ManuelService::class);
            default:
                throw new \Exception("Passerelle de paiement non prise en charge: {$passerelle}");
        }
    }

    /**
     * Initialiser un paiement avec la passerelle spécifiée
     *
     * @param Facturation $facturation
     * @param User $initiateur
     * @param array $options
     * @return array
     */
    public function initialiserPaiement(Facturation $facturation, User $initiateur, array $options = []): array
    {
        try {
            $passerelle = $options['passerelle'] ?? config('paiement.default_gateway', 'paystack');
            $passerelleService = $this->getPasserelle($passerelle);
            
            return $passerelleService->initialiser($facturation, $initiateur, $options);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'initialisation du paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'facturation_id' => $facturation->id,
                'options' => $options
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
            $passerelleService = $this->getPasserelle($paiement->passerelle);
            
            return $passerelleService->verifierStatut($paiement);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut du paiement', [
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
     * Annuler un paiement
     *
     * @param Paiement $paiement
     * @param string|null $raison
     * @return bool
     */
    public function annulerPaiement(Paiement $paiement, ?string $raison = null): bool
    {
        try {
            $passerelleService = $this->getPasserelle($paiement->passerelle);
            
            return $passerelleService->annuler($paiement, $raison);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation du paiement', [
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
    public function rembourserPaiement(Paiement $paiement, ?float $montant = null, ?string $raison = null): bool
    {
        try {
            $passerelleService = $this->getPasserelle($paiement->passerelle);
            
            return $passerelleService->rembourser($paiement, $montant, $raison);
        } catch (\Exception $e) {
            Log::error('Erreur lors du remboursement du paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);

            return false;
        }
    }

    /**
     * Obtenir les passerelles disponibles pour l'utilisateur
     *
     * @param User $user
     * @return array
     */
    public function getPasserellesDisponibles(User $user): array
    {
        $passerelles = [];

        // Vérifier si Paystack est disponible
        if (config('paiement.paystack.secret_key')) {
            $passerelles[Paiement::PASSERELLE_PAYSTACK] = [
                'id' => Paiement::PASSERELLE_PAYSTACK,
                'nom' => 'Paystack',
                'description' => 'Paiement par carte bancaire ou mobile money via Paystack',
                'logo' => asset('images/paystack-logo.png'),
                'methodes' => [
                    Paiement::METHODE_CARTE => 'Carte bancaire',
                    Paiement::METHODE_MOBILE_MONEY => 'Mobile Money'
                ]
            ];
        }

        // Vérifier si Stripe est disponible
        if (config('paiement.stripe.secret_key')) {
            $passerelles[Paiement::PASSERELLE_STRIPE] = [
                'id' => Paiement::PASSERELLE_STRIPE,
                'nom' => 'Stripe',
                'description' => 'Paiement sécurisé par carte bancaire internationale via Stripe',
                'logo' => asset('images/stripe-logo.png'),
                'methodes' => [
                    Paiement::METHODE_CARTE => 'Carte bancaire'
                ]
            ];
        }

        // Vérifier si le paiement manuel est disponible (pour les administrateurs)
        if ($user->hasRole('admin') || $user->hasPermissionTo('manage-payments')) {
            $passerelles[Paiement::PASSERELLE_MANUEL] = [
                'id' => Paiement::PASSERELLE_MANUEL,
                'nom' => 'Paiement manuel',
                'description' => 'Paiement par virement, chèque ou espèces (nécessite une validation manuelle)',
                'logo' => asset('images/manual-payment-logo.png'),
                'methodes' => [
                    Paiement::METHODE_VIREMENT => 'Virement bancaire',
                    Paiement::METHODE_CHEQUE => 'Chèque',
                    Paiement::METHODE_ESPECES => 'Espèces'
                ]
            ];
        }

        return $passerelles;
    }
}
