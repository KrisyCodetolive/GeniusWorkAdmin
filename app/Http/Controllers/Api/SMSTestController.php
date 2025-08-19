<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SMS\SMSServiceFactory;
use App\Services\SMS\OrangeSMSService;
use App\Services\SMS\MTNSMSService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SMSTestController extends Controller
{
    /**
     * Envoyer un SMS de test avec le fournisseur par défaut
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestSMS(Request $request)
    {
        // Validation des données
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160',
            'provider' => 'nullable|string|in:orange,mtn',
        ]);

        // La validation est automatiquement gérée par Laravel
        // Si la validation échoue, une exception ValidationException est levée
        // et la réponse JSON est générée automatiquement

        try {
            $provider = $request->input('provider');
            $smsService = SMSServiceFactory::create($provider);
            
            $result = $smsService->sendSMS(
                $request->input('phone_number'),
                $request->input('message')
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'provider' => $provider ?? config('sms.default_provider')
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS de test', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un SMS de test avec Orange SMS
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOrangeSMS(Request $request)
    {
        // Validation des données
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $orangeSMSService = app(OrangeSMSService::class);
            
            // Utiliser une configuration personnalisée si fournie
            if ($request->filled('client_id') && $request->filled('client_secret')) {
                $orangeSMSService->useCustomConfig([
                    'client_id' => $request->input('client_id'),
                    'client_secret' => $request->input('client_secret'),
                    'dev_phone_number' => $request->input('dev_phone_number')
                ]);
            }
            
            $result = $orangeSMSService->sendSMS(
                $request->input('phone_number'),
                $request->input('message')
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'provider' => 'orange'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS Orange', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoyer un SMS de test avec MTN SMS
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMTNSMS(Request $request)
    {
        // Validation des données
        $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string|max:160',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'sender_address' => 'nullable|string',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $mtnSMSService = app(MTNSMSService::class);
            
            // Utiliser une configuration personnalisée si fournie
            if ($request->filled('client_id') && $request->filled('client_secret')) {
                $mtnSMSService->useCustomConfig([
                    'client_id' => $request->input('client_id'),
                    'client_secret' => $request->input('client_secret'),
                    'sender_address' => $request->input('sender_address'),
                    'dev_phone_number' => $request->input('dev_phone_number')
                ]);
            }
            
            $result = $mtnSMSService->sendSMS(
                $request->input('phone_number'),
                $request->input('message')
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'provider' => 'mtn'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier le statut de livraison d'un SMS MTN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkMTNDeliveryStatus(Request $request)
    {
        // Validation des données
        $request->validate([
            'sender_address' => 'required|string',
            'request_id' => 'required|string',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $mtnSMSService = app(MTNSMSService::class);
            
            $result = $mtnSMSService->checkDeliveryStatus(
                $request->input('sender_address'),
                $request->input('request_id')
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'status' => $result['status'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut de livraison MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un abonnement aux notifications de livraison MTN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMTNDeliverySubscription(Request $request)
    {
        // Validation des données
        $request->validate([
            'sender_address' => 'required|string',
            'notify_url' => 'required|url',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $mtnSMSService = app(MTNSMSService::class);
            
            $result = $mtnSMSService->createDeliverySubscription(
                $request->input('sender_address'),
                $request->input('notify_url')
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'subscription_id' => $result['subscription_id'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'abonnement MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un abonnement aux notifications de livraison MTN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMTNDeliverySubscription(Request $request)
    {
        // Validation des données
        $request->validate([
            'sender_address' => 'required|string',
            'subscription_id' => 'required|string',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $mtnSMSService = app(MTNSMSService::class);
            
            $result = $mtnSMSService->deleteDeliverySubscription(
                $request->input('sender_address'),
                $request->input('subscription_id')
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'abonnement MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les SMS entrants MTN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMTNInboundMessages(Request $request)
    {
        // Validation des données
        $request->validate([
            'request_id' => 'required|string',
            'max_batch_size' => 'nullable|integer|min:1|max:100',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $mtnSMSService = app(MTNSMSService::class);
            
            $result = $mtnSMSService->getInboundMessages(
                $request->input('request_id'),
                $request->input('max_batch_size', 10)
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'messages' => $result['messages'] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des SMS entrants MTN', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des SMS
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSMSStats(Request $request)
    {
        // Validation des données
        $request->validate([
            'provider' => 'nullable|string|in:orange,mtn',
            'days' => 'nullable|integer|min:1|max:90',
        ]);

        // La validation est automatiquement gérée par Laravel

        try {
            $provider = $request->input('provider', config('sms.default_provider'));
            $days = $request->input('days', 30);
            
            $smsLogService = app(\App\Services\SMS\SMSLogService::class);
            $stats = $smsLogService->getProviderStats($provider, $days);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'provider' => $provider,
                    'days' => $days,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques SMS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
