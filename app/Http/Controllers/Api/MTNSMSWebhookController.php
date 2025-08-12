<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SMSLog;

class MTNSMSWebhookController extends Controller
{
    /**
     * Gère les notifications de livraison de SMS MTN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliveryReceipt(Request $request)
    {
        $data = $request->all();
        
        Log::channel('sms')->info('Notification de livraison MTN SMS reçue', [
            'data' => $data
        ]);
        
        // Vérifier si la notification contient les informations nécessaires
        if (!isset($data['deliveryInfo'])) {
            return response()->json(['status' => 'error', 'message' => 'Format de notification invalide'], 400);
        }
        
        // Traiter les informations de livraison
        $requestId = $data['deliveryInfo']['requestId'] ?? null;
        $clientCorrelator = $data['deliveryInfo']['clientCorrelator'] ?? null;
        
        // Traiter les statuts de livraison pour chaque destinataire
        if (isset($data['deliveryInfo']['deliveryStatus']) && is_array($data['deliveryInfo']['deliveryStatus'])) {
            foreach ($data['deliveryInfo']['deliveryStatus'] as $delivery) {
                $receiverAddress = $delivery['receiverAddress'] ?? null;
                $status = $delivery['status'] ?? null;
                
                if ($receiverAddress && $status) {
                    // Mettre à jour le statut du SMS dans la base de données
                    $this->updateSMSStatus($requestId, $receiverAddress, $status);
                }
            }
        }
        
        return response()->json(['status' => 'success']);
    }
    
    /**
     * Gère les SMS entrants de MTN
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inboundMessage(Request $request)
    {
        $data = $request->all();
        
        Log::channel('sms')->info('SMS entrant MTN reçu', [
            'data' => $data
        ]);
        
        // Traiter le SMS entrant selon les besoins de l'application
        // ...
        
        return response()->json(['status' => 'success']);
    }
    
    /**
     * Met à jour le statut d'un SMS dans la base de données
     *
     * @param string $requestId ID de la requête
     * @param string $phoneNumber Numéro de téléphone
     * @param string $status Statut de livraison
     * @return void
     */
    private function updateSMSStatus($requestId, $phoneNumber, $status)
    {
        // Nettoyer le numéro de téléphone pour la recherche
        $cleanPhoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Mapper les statuts MTN vers les statuts internes
        $statusMap = [
            'DeliveredToTerminal' => SMSLog::STATUS_SENT,
            'DeliveryUncertain' => SMSLog::STATUS_UNCERTAIN,
            'DeliveryImpossible' => SMSLog::STATUS_FAILED,
            'MessageWaiting' => SMSLog::STATUS_PENDING,
            'DeliveredToNetwork' => SMSLog::STATUS_SENT,
            'DeliveryNotificationNotSupported' => SMSLog::STATUS_UNCERTAIN
        ];
        
        $internalStatus = $statusMap[$status] ?? SMSLog::STATUS_UNCERTAIN;
        
        // Rechercher le SMS correspondant dans la base de données
        $smsLog = SMSLog::where('notification_type', 'mtn_sms')
            ->where(function($query) use ($cleanPhoneNumber) {
                $query->where('phone_number', 'like', '%' . $cleanPhoneNumber . '%')
                    ->orWhere('phone_number', 'like', '%' . $cleanPhoneNumber);
            })
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($smsLog) {
            // Mettre à jour le statut et les informations supplémentaires
            $smsLog->status = $internalStatus;
            $smsLog->external_id = $requestId;
            
            if ($internalStatus === SMSLog::STATUS_FAILED) {
                $smsLog->error_message = 'Échec de livraison signalé par MTN: ' . $status;
            }
            
            $smsLog->save();
            
            Log::channel('sms')->info('Statut SMS MTN mis à jour', [
                'sms_id' => $smsLog->id,
                'status' => $status,
                'internal_status' => $internalStatus
            ]);
        } else {
            Log::channel('sms')->warning('SMS MTN non trouvé pour mise à jour de statut', [
                'request_id' => $requestId,
                'phone_number' => $phoneNumber,
                'status' => $status
            ]);
        }
    }
}
