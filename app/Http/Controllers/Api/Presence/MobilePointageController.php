<?php

namespace App\Http\Controllers\Api\Presence;

use App\Http\Controllers\Controller;
use App\Services\Presence\MobilePointageService;
use App\Services\Presence\SiteLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MobilePointageController extends Controller
{
    protected $mobilePointageService;
    protected $siteLocationService;

    /**
     * Constructeur du contrôleur
     * 
     * @param MobilePointageService $mobilePointageService
     * @param SiteLocationService $siteLocationService
     */
    public function __construct(
        MobilePointageService $mobilePointageService,
        SiteLocationService $siteLocationService
    ) {
        $this->mobilePointageService = $mobilePointageService;
        $this->siteLocationService = $siteLocationService;
    }

    /**
     * Enregistre un pointage depuis l'application mobile
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function enregistrerPointage(Request $request)
    {
        // Récupérer l'utilisateur authentifié
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        // Recuperer l'IP de l'utilisateur
        $ip = $request->ip();

        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'type' => 'nullable',
            'methode_pointage' => 'nullable|string',
            'token' => 'required',
            'user_agent' => 'nullable|string',
            'ip' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Traiter le pointage
            $result = $this->mobilePointageService->processPointage($request);
            
            if (!is_array($result) || !isset($result['status'])) {
                throw new \Exception('Format de réponse invalide du service de pointage ' . $result);
            }

            // Retourner la réponse
            if ($result['status'] === 'success') {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors du traitement du pointage: ' . $result . ' - ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère l'historique des pointages
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistorique(Request $request)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }
            
            // Vérifier si l'utilisateur a un employé associé
            if (!$user->employeur) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employé non trouvé'
                ], 404);
            }
            
            // Valider les données de la requête
            $validator = Validator::make($request->all(), [
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer une nouvelle requête avec l'ID de l'employeur
            $historiqueRequest = new Request($request->all());
            $historiqueRequest->merge(['employeur' => $user->employeur]);
            
            // Récupérer l'historique
            $result = $this->mobilePointageService->getHistorique($historiqueRequest);

            // Retourner la réponse
            if ($result['status'] === 'success') {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération de l\'historique: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * Récupère les statistiques de pointage
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistiques(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'idno' => 'required|string',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        // Récupérer les statistiques
        $result = $this->mobilePointageService->getStatistiques($request);

        // Retourner la réponse
        if ($result['status'] === 'success') {
            return response()->json($result);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Récupère les coordonnées du site associé à un employé
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiteCoordinates(Request $request)
    {
        // Nous n'avons plus besoin de valider le token dans le corps de la requête
        // car nous utilisons l'en-tête Authorization

        try {
            // Récupérer l'employé à partir du token d'authentification Sanctum
            // Le token est déjà vérifié par le middleware sanctum si présent
            // Sinon, nous devons le vérifier manuellement
            
            // Vérifier si nous avons un utilisateur authentifié via Sanctum
            if ($request->user()) {
                $user = $request->user();
            } else {
                // Essayer de trouver le token dans la table personal_access_tokens
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);
                if (!$tokenModel) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token d\'authentification invalide'
                    ], 401);
                }
                
                $user = $tokenModel->tokenable;
            }
            
            // Vérifier si l'utilisateur a un employé associé
            if (!$user || !$user->employeur) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié ou employé non trouvé'
                ], 401);
            }
            
            $result = $this->siteLocationService->getEmployeSiteCoordinates($user->employeur->qr_code_secret);
    
            if ($result['status'] === 'error') {
                return response()->json($result, 404);
            }
    
            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération des coordonnées du site: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des coordonnées du site'
            ], 500);
        }
    }

    /**
     * Vérifie si un employé est autorisé à pointer sur un site
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifierAutorisation(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Récupérer l'employé à partir du token d'authentification Sanctum
            // Le token est déjà vérifié par le middleware sanctum si présent
            // Sinon, nous devons le vérifier manuellement
            
            // Vérifier si nous avons un utilisateur authentifié via Sanctum
            if ($request->user()) {
                $user = $request->user();
            } else {
                // Essayer de trouver le token dans la table personal_access_tokens
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);
                if (!$tokenModel) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token d\'authentification invalide'
                    ], 401);
                }
                
                $user = $tokenModel->tokenable;
            }
            
            // Vérifier si l'utilisateur a un employé associé
            if (!$user || !$user->employeur) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié ou employé non trouvé'
                ], 401);
            }
            
            $employeur = $user->employeur;
            
            // Récupérer le site principal de l'entreprise
            $site = \App\Models\Site::where('entreprise_id', $employeur->entreprise_id)
                ->where('principal', true)
                ->first();
            
            if (!$site) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Aucun site valide trouvé pour votre entreprise'
                ], 400);
            }
            
            // Vérifier la distance si les coordonnées sont fournies
            $distance = null;
            $isInZone = true;
            
            if ($request->filled(['lat', 'lng']) && $site->latitude && $site->longitude) {
                $webPointageService = new \App\Services\Presence\WebPointageService();
                $distance = $webPointageService->calculateDistance(
                    $request->lat,
                    $request->lng,
                    $site->latitude,
                    $site->longitude
                );
                
                // Vérifier si la distance est trop grande
                $distanceMax = $site->rayon_geofencing ?? 100;
                $isInZone = $distance <= $distanceMax;
            }
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'employe' => [
                        'id' => $employeur->id,
                        'nom' => $employeur->nom_complet,
                        'entreprise' => $employeur->entreprise->nom ?? 'Entreprise inconnue'
                    ],
                    'site' => [
                        'id' => $site->id,
                        'nom' => $site->nom,
                        'adresse' => $site->adresse,
                        'latitude' => $site->latitude,
                        'longitude' => $site->longitude,
                        'rayon' => $site->rayon_geofencing ?? 100
                    ],
                    'localisation' => [
                        'distance' => round($distance),
                        'isInZone' => $isInZone,
                        'message' => $isInZone ? 'Vous êtes dans la zone autorisée' : 'Vous êtes en dehors de la zone autorisée'
                    ],
                    'autorisation' => $isInZone
                ]
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la vérification d\'autorisation: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la vérification de l\'autorisation'
            ], 500);
        }
    }

    /**
     * Vérifie si la position d'un employé est dans la zone autorisée
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifierPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Récupérer l'employé à partir du token d'authentification Sanctum
            // Le token est déjà vérifié par le middleware sanctum si présent
            // Sinon, nous devons le vérifier manuellement
            
            // Vérifier si nous avons un utilisateur authentifié via Sanctum
            if ($request->user()) {
                $user = $request->user();
            } else {
                // Essayer de trouver le token dans la table personal_access_tokens
                $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);
                if (!$tokenModel) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token d\'authentification invalide'
                    ], 401);
                }
                
                $user = $tokenModel->tokenable;
            }
            
            // Vérifier si l'utilisateur a un employé associé
            if (!$user || !$user->employeur) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié ou employé non trouvé'
                ], 401);
            }

            $result = $this->siteLocationService->verifierPositionEmploye(
                $user->employeur->qr_code_secret,
                $request->lat,
                $request->lng
            );

            return response()->json($result);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la vérification de la position: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la vérification de la position'
            ], 500);
        }
    }

    /**
     * Synchronise les pointages stockés localement
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function synchroniserPointages(Request $request)
    {
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'pointages' => 'required|array',
            'pointages.*.idno' => 'required|string',
            'pointages.*.token' => 'required|string',
            'pointages.*.lat' => 'required|numeric',
            'pointages.*.lng' => 'required|numeric',
            'pointages.*.type' => 'required|string|in:entree,sortie,pause_debut,pause_fin',
            'pointages.*.timestamp' => 'required|numeric',
            'pointages.*.local_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $success = true;

        // Traiter chaque pointage
        foreach ($request->pointages as $pointageData) {
            // Créer une requête individuelle pour chaque pointage
            $pointageRequest = new Request($pointageData);
            
            // Utiliser le timestamp fourni pour définir la date du pointage
            $timestamp = $pointageData['timestamp'];
            $date = new \DateTime();
            $date->setTimestamp($timestamp / 1000); // Convertir millisecondes en secondes
            
            // Ajouter la date à la requête
            $pointageRequest->merge(['date_pointage' => $date->format('Y-m-d H:i:s')]);
            
            // Traiter le pointage
            $result = $this->mobilePointageService->processPointage($pointageRequest);
            
            // Ajouter le résultat à la liste
            $results[$pointageData['local_id']] = $result;
            
            // Si un pointage échoue, marquer la synchronisation comme partiellement réussie
            if ($result['status'] !== 'success') {
                $success = false;
            }
        }

        return response()->json([
            'status' => $success ? 'success' : 'partial',
            'message' => $success ? 'Tous les pointages ont été synchronisés' : 'Certains pointages n\'ont pas pu être synchronisés',
            'results' => $results
        ]);
    }
    
    /**
     * Récupère l'état de pointage actuel d'un employé
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEtatPointage(Request $request)
    {
        try {
            // Récupérer l'utilisateur authentifié
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }
            
            // Vérifier si l'utilisateur a un employé associé
            if (!$user->employeur) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Employé non trouvé'
                ], 404);
            }
            
            // Récupérer la date du jour (format Y-m-d)
            $today = date('Y-m-d');
            
            // Récupérer les paramètres optionnels
            $fields = $request->input('fields', 'is_checked_in,last_pointage_type,last_pointage_time');
            $fieldsArray = explode(',', $fields);
            
            // Récupérer la dernière présence de l'employé pour aujourd'hui
            $lastPointage = \App\Models\Presence::where('employeur_id', $user->employeur->id)
                ->whereDate('created_at', $today)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $result = [
                'status' => 'success',
                'data' => []
            ];
            
            // Déterminer si l'employé est pointé ou non
            $isCheckedIn = false;
            $lastPointageType = null;
            $lastPointageTime = null;
            
            if ($lastPointage) {
                $lastPointageType = $lastPointage->type;
                $lastPointageTime = $lastPointage->created_at->format('Y-m-d H:i:s');
                
                // L'employé est considéré comme pointé si son dernier pointage est de type 'entree' ou 'pause_fin'
                $isCheckedIn = in_array($lastPointage->type, ['entree', 'pause_fin']);
            }
            
            // Construire la réponse en fonction des champs demandés
            if (in_array('is_checked_in', $fieldsArray)) {
                $result['data']['is_checked_in'] = $isCheckedIn;
            }
            
            if (in_array('last_pointage_type', $fieldsArray) && $lastPointageType) {
                $result['data']['last_pointage_type'] = $lastPointageType;
            }
            
            if (in_array('last_pointage_time', $fieldsArray) && $lastPointageTime) {
                $result['data']['last_pointage_time'] = $lastPointageTime;
            }
            
            // Ajouter des statistiques si demandées
            if (in_array('stats', $fieldsArray)) {
                // Calculer les statistiques de la journée
                $stats = $this->mobilePointageService->calculateDailyStats($user->employeur->id, $today);
                $result['data']['stats'] = $stats;
            }
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération de l\'état de pointage: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération de l\'état de pointage'
            ], 500);
        }
    }
}
