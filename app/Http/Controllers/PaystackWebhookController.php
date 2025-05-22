<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\Paiement\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Gérer les webhooks Paystack
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            
            // Vérifier la signature si disponible
            if (config('paiement.paystack.webhook_secret')) {
                $signature = $request->header('X-Paystack-Signature');
                $calculatedSignature = hash_hmac('sha512', json_encode($payload), config('paiement.paystack.webhook_secret'));
                
                if ($signature !== $calculatedSignature) {
                    Log::warning('Signature Paystack invalide', [
                        'received' => $signature,
                        'calculated' => $calculatedSignature
                    ]);
                    
                    return response()->json(['message' => 'Signature invalide'], 401);
                }
            }
            
            $result = $this->paystackService->traiterReponse($payload);

            if (!$result['success']) {
                Log::warning('Échec du traitement du webhook Paystack', $result);
                return response()->json(['message' => $result['message']], 400);
            }

            return response()->json(['message' => 'Webhook traité avec succès']);
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du webhook Paystack', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'Erreur lors du traitement du webhook'], 500);
        }
    }
}
