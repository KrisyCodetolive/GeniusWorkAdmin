<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Services\Paiement\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paiementService;

    public function __construct(PaiementService $paiementService)
    {
        $this->paiementService = $paiementService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Vérifier le statut d'un paiement
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Request $request, string $reference)
    {
        try {
            $paiement = Paiement::where('reference', $reference)->first();
            
            if (!$paiement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement non trouvé'
                ], 404);
            }
            
            // Vérifier que l'utilisateur a le droit de voir ce paiement
            if ($request->user()->id !== $paiement->initiateur_id && 
                $request->user()->id !== $paiement->facturation->user_id && 
                ($paiement->entreprise && $request->user()->id !== $paiement->entreprise->user_id) && 
                !$request->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à accéder à ce paiement'
                ], 403);
            }

            // Vérifier le statut du paiement
            $result = $this->paiementService->verifierStatut($paiement);

            return response()->json([
                'success' => true,
                'data' => [
                    'paiement' => $paiement,
                    'statut' => $result
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification du statut du paiement via API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'reference' => $reference
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }
}
