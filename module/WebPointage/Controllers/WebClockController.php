<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Http\Controllers\ClockController;

class WebClockController extends Controller
{
    protected $clockController;

    public function __construct(ClockController $clockController)
    {
        $this->clockController = $clockController;
    }

    public function initiateClocking(Request $request)
    {
        try {
            $idno = $request->input('idno');
            
            // Générer un ID unique pour cette requête
            $requestId = Str::uuid()->toString();
            
            // Faire un appel à clocking avec le type automatique
            $clockRequest = new Request();
            $clockRequest->merge([
                'idno' => $idno,
                'type' => 'clockin' // Par défaut on met clockin, le contrôleur déterminera le bon type
            ]);
            
            $clockResponse = $this->clockController->autoClocking($clockRequest);
            $employeeData = json_decode($clockResponse->getContent(), true);

            if (isset($employeeData['error'])) {
                throw new \Exception($employeeData['error']);
            }

            // Stocker les données de transition dans la session
            session([
                "transition_data_{$requestId}" => [
                    'name' => $employeeData['employee'],
                    'type' => $employeeData['type'],
                    'message' => $employeeData['voice'] ?? null
                ]
            ]);

            // Stocker le résultat dans le cache
            Cache::put("clocking_request_{$requestId}", [
                'status' => 'completed',
                'completed_at' => now(),
                'data' => $employeeData
            ], now()->addMinutes(5));

            // Retourner la réponse JSON avec les données nécessaires
            return response()->json([
                'requestId' => $requestId,
                'name' => $employeeData['employee'],
                'type' => $employeeData['type'],
                'message' => $employeeData['voice'] ?? null
            ]);

        } catch (\Exception $e) {
            // En cas d'erreur, retourner une réponse d'erreur
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function checkStatus($requestId)
    {
        $data = Cache::get("clocking_request_{$requestId}");
        
        if (!$data) {
            return response()->json(['status' => 'not_found'], 404);
        }
        
        return response()->json($data);
    }
    
    public function showTransition(Request $request)
    {
        $requestId = $request->input('id');
        $transitionData = session("transition_data_{$requestId}");
        
        if (!$transitionData) {
            return redirect()->route('smart-clock.index');
        }
        
        return view('smart-clock-transition', [
            'requestId' => $requestId,
            'name' => $transitionData['name'],
            'type' => $transitionData['type'],
            'message' => $transitionData['message']
        ]);
    }
}
