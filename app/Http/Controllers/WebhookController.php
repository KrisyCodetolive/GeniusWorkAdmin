<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\Paiement\PaiementService;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $paiementService;
    protected $workflowService;

    public function __construct(PaiementService $paiementService, WorkflowService $workflowService)
    {
        $this->paiementService = $paiementService;
        $this->workflowService = $workflowService;
    }

    /**
     * Gérer le webhook Paystack
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function paystack(Request $request)
    {
        // Vérifier la signature du webhook
        $signature = $request->header('X-Paystack-Signature');
        $payload = $request->getContent();
        $secret = config('paiement.paystack.secret_key');
        
        if (!$signature || hash_hmac('sha512', $payload, $secret) !== $signature) {
            Log::warning('Webhook Paystack: signature invalide');
            return response()->json(['status' => 'error', 'message' => 'Signature invalide'], 401);
        }

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? null;
        $data = $event['data'] ?? [];
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            Log::warning('Webhook Paystack: référence manquante', ['event' => $eventType]);
            return response()->json(['status' => 'error', 'message' => 'Référence manquante'], 400);
        }

        Log::info('Webhook Paystack reçu', [
            'event' => $eventType,
            'reference' => $reference
        ]);

        // Trouver le paiement correspondant
        $paiement = Paiement::where('reference_externe', $reference)
            ->orWhere('reference', $reference)
            ->first();

        if (!$paiement) {
            Log::warning('Webhook Paystack: paiement non trouvé', ['reference' => $reference]);
            return response()->json(['status' => 'error', 'message' => 'Paiement non trouvé'], 404);
        }

        // Traiter l'événement
        switch ($eventType) {
            case 'charge.success':
                // Paiement réussi
                if ($paiement->estEnAttente() || $paiement->estTraitement()) {
                    // Mettre à jour le paiement
                    $paiement->update([
                        'statut' => Paiement::STATUT_COMPLETE,
                        'date_validation' => now(),
                        'montant_recu' => $data['amount'] / 100, // Paystack retourne le montant en centimes
                        'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                            'paystack_event' => $event
                        ])
                    ]);

                    // Envoyer une notification de succès
                    $paiement->sendPaymentNotification('success');

                    // Compléter le workflow
                    $this->workflowService->completeWorkflowAfterPayment($paiement);

                    Log::info('Webhook Paystack: paiement validé', ['paiement_id' => $paiement->id]);
                }
                break;

            case 'charge.failed':
                // Paiement échoué
                if ($paiement->estEnAttente() || $paiement->estTraitement()) {
                    $errorMessage = $data['gateway_response'] ?? 'Raison inconnue';
                    
                    $paiement->update([
                        'statut' => Paiement::STATUT_ECHOUE,
                        'commentaire' => 'Paiement échoué: ' . $errorMessage,
                        'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                            'paystack_event' => $event
                        ])
                    ]);

                    // Envoyer une notification d'échec
                    $paiement->sendPaymentNotification('failed', $errorMessage);

                    Log::info('Webhook Paystack: paiement échoué', ['paiement_id' => $paiement->id]);
                }
                break;

            default:
                Log::info('Webhook Paystack: événement ignoré', ['event' => $eventType]);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Gérer le webhook Stripe
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function stripe(Request $request)
    {
        // Vérifier la signature du webhook
        $signature = $request->header('Stripe-Signature');
        $payload = $request->getContent();
        $secret = config('paiement.stripe.webhook_secret');
        
        if (!$signature) {
            Log::warning('Webhook Stripe: signature manquante');
            return response()->json(['status' => 'error', 'message' => 'Signature manquante'], 401);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Exception $e) {
            Log::warning('Webhook Stripe: signature invalide', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Signature invalide'], 401);
        }

        $eventType = $event->type;
        $data = $event->data->object;
        $paymentIntent = $data->id ?? null;

        if (!$paymentIntent) {
            Log::warning('Webhook Stripe: payment intent manquant', ['event' => $eventType]);
            return response()->json(['status' => 'error', 'message' => 'Payment intent manquant'], 400);
        }

        Log::info('Webhook Stripe reçu', [
            'event' => $eventType,
            'payment_intent' => $paymentIntent
        ]);

        // Trouver le paiement correspondant
        $paiement = Paiement::where('reference_externe', $paymentIntent)->first();

        if (!$paiement) {
            Log::warning('Webhook Stripe: paiement non trouvé', ['payment_intent' => $paymentIntent]);
            return response()->json(['status' => 'error', 'message' => 'Paiement non trouvé'], 404);
        }

        // Traiter l'événement
        switch ($eventType) {
            case 'payment_intent.succeeded':
                // Paiement réussi
                if ($paiement->estEnAttente() || $paiement->estTraitement()) {
                    // Mettre à jour le paiement
                    $paiement->update([
                        'statut' => Paiement::STATUT_COMPLETE,
                        'date_validation' => now(),
                        'montant_recu' => $data->amount / 100, // Stripe retourne le montant en centimes
                        'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                            'stripe_event' => json_decode(json_encode($event), true)
                        ])
                    ]);

                    // Envoyer une notification de succès
                    $paiement->sendPaymentNotification('success');

                    // Compléter le workflow
                    $this->workflowService->completeWorkflowAfterPayment($paiement);

                    Log::info('Webhook Stripe: paiement validé', ['paiement_id' => $paiement->id]);
                }
                break;

            case 'payment_intent.payment_failed':
                // Paiement échoué
                if ($paiement->estEnAttente() || $paiement->estTraitement()) {
                    $errorMessage = $data->last_payment_error->message ?? 'Raison inconnue';
                    
                    $paiement->update([
                        'statut' => Paiement::STATUT_ECHOUE,
                        'commentaire' => 'Paiement échoué: ' . $errorMessage,
                        'meta_donnees' => array_merge($paiement->meta_donnees ?? [], [
                            'stripe_event' => json_decode(json_encode($event), true)
                        ])
                    ]);

                    // Envoyer une notification d'échec
                    $paiement->sendPaymentNotification('failed', $errorMessage);

                    Log::info('Webhook Stripe: paiement échoué', ['paiement_id' => $paiement->id]);
                }
                break;

            default:
                Log::info('Webhook Stripe: événement ignoré', ['event' => $eventType]);
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
