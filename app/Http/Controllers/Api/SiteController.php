<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    protected $siteService;

    /**
     * Constructeur avec injection du service
     */
    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    /**
     * Récupérer tous les sites de l'entreprise de l'utilisateur connecté
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $entrepriseId = $request->user()->entreprise_id;
            $sites = $this->siteService->getSitesByEntreprise($entrepriseId);
            
            return response()->json([
                'status' => 'success',
                'data' => $sites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des sites: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un site spécifique
     *
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(string $id, Request $request): JsonResponse
    {
        try {
            $entrepriseId = $request->user()->entreprise_id;
            $site = Site::where('id', $id)
                ->where('entreprise_id', $entrepriseId)
                ->first();
            
            if (!$site) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Site non trouvé'
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $site
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération du site: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si l'utilisateur est dans la zone d'un site
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifierPosition(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|string|exists:sites,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données de position invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $site = Site::find($request->site_id);
            
            if (!$site) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Site non trouvé'
                ], 404);
            }
            
            $estDansZone = $site->estDansRayon(
                $request->latitude,
                $request->longitude
            );
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'est_dans_zone' => $estDansZone,
                    'rayon_geofencing' => $site->rayon_geofencing,
                    'has_geofencing' => $site->has_geofencing
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la vérification de position: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le site le plus proche d'une position donnée
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function siteProche(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données de position invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $entrepriseId = $request->user()->entreprise_id;
            $site = $this->siteService->findNearestSite(
                $request->latitude,
                $request->longitude,
                $entrepriseId
            );
            
            if (!$site) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun site trouvé'
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $site
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la recherche du site proche: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les statistiques des sites
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistiques(Request $request): JsonResponse
    {
        try {
            $entrepriseId = $request->user()->entreprise_id;
            $stats = $this->siteService->getStatistiquesSites($entrepriseId);
            
            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }
}
