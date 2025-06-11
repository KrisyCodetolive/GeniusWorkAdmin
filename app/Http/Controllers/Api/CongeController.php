<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CongeService;
use App\Models\Conge;
use App\Models\TypeConge;
use App\Http\Requests\Conge\CreateCongeRequest;
use App\Http\Resources\CongeResource;
use App\Http\Resources\CongeCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CongeController extends Controller
{
    protected $congeService;

    public function __construct(CongeService $congeService)
    {
        $this->congeService = $congeService;
    }

    /**
     * Récupérer la liste des congés de l'utilisateur
     *
     * @param Request $request
     * @return CongeCollection
     */
    public function index(Request $request)
    {
        $conges = $request->user()->employeur->conges()
            ->with(['typeConge', 'validateur'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return new CongeCollection($conges);
    }

    /**
     * Créer une nouvelle demande de congé
     *
     * @param CreateCongeRequest $request
     * @return CongeResource
     */
    public function store(CreateCongeRequest $request)
    {
        try {
            $conge = $this->congeService->creerDemande($request->validated(), $request->user());
            return new CongeResource($conge);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Récupérer les détails d'une demande de congé
     *
     * @param Conge $conge
     * @return CongeResource
     */
    public function show(Conge $conge)
    {
        $this->authorize('view', $conge);
        return new CongeResource($conge->load(['typeConge', 'validateur']));
    }

    /**
     * Annuler une demande de congé
     *
     * @param Conge $conge
     * @param Request $request
     * @return CongeResource
     */
    public function cancel(Conge $conge, Request $request)
    {
        $this->authorize('cancel', $conge);
        
        try {
            $conge = $this->congeService->annulerDemande($conge, $request->get('commentaire'));
            return new CongeResource($conge);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Approuver une demande de congé
     *
     * @param Conge $conge
     * @param Request $request
     * @return CongeResource
     */
    public function approve(Conge $conge, Request $request)
    {
        $this->authorize('approve', $conge);
        
        try {
            $conge = $this->congeService->approuverDemande($conge, $request->user(), $request->get('commentaire'));
            return new CongeResource($conge);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Rejeter une demande de congé
     *
     * @param Conge $conge
     * @param Request $request
     * @return CongeResource
     */
    public function reject(Conge $conge, Request $request)
    {
        $this->authorize('reject', $conge);
        
        try {
            $conge = $this->congeService->rejeterDemande($conge, $request->user(), $request->get('commentaire'));
            return new CongeResource($conge);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Récupérer les statistiques de congés de l'utilisateur
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        $stats = $this->congeService->getStatistiquesUtilisateur(
            $request->user(),
            $request->get('annee', date('Y'))
        );

        return response()->json($stats);
    }
}
