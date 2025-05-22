<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\Paiement\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Gérer les webhooks Stripe
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $result = $this->stripeService->traiterReponse([
                'payload' => $payload,
                'stripe_signature' => $sigHeader
            ]);

            if (!$result['success']) {
                Log::warning('Échec du traitement du webhook Stripe', $result);
                return response()->json(['message' => $result['message']], 400);
            }

            return response()->json(['message' => 'Webhook traité avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du webhook Stripe', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'Erreur lors du traitement du webhook'], 500);
        }
    }
}
