<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PresenceService;
use App\Services\WebAuthnService;
use App\Services\QRCodeService;
use App\Models\Site;
use App\Models\Employeur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MobilePointageController extends Controller
{
    protected $presenceService;
    protected $webAuthnService;
    protected $qrCodeService;

    public function __construct(
        PresenceService $presenceService,
        WebAuthnService $webAuthnService,
        QRCodeService $qrCodeService
    ) {
        $this->presenceService = $presenceService;
        $this->webAuthnService = $webAuthnService;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Valide un QR code et retourne les informations du site
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateQRCode(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|min:10',
                'site_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $token = $request->input('token');
            $siteId = $request->input('site_id');

            // Valider le QR code
            $isValid = $this->presenceService->validateQRCode($token, $siteId);

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code invalide ou expiré'
                ], 400);
            }

            // Récupérer les informations du site
            $qrInfo = $this->qrCodeService->getQRCodeInfo($token);

            if (!$qrInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer les informations du site'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR code valide',
                'data' => $qrInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation du QR code', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Génère les options d'authentification WebAuthn
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWebAuthnAuthOptions(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email invalide',
                    'errors' => $validator->errors()
                ], 400);
            }

            $email = $request->input('email');

            // Générer les options d'authentification
            $options = $this->webAuthnService->generateAuthenticationOptions($email);

            return response()->json([
                'success' => true,
                'message' => 'Options d\'authentification générées',
                'data' => $options
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des options WebAuthn', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des options d\'authentification'
            ], 500);
        }
    }

    /**
     * Vérifie l'authentification WebAuthn
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyWebAuthnAuth(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'webauthn_response' => 'required|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données d\'authentification invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $email = $request->input('email');
            $webauthnResponse = $request->input('webauthn_response');

            // Authentifier l'employeur
            $employeur = $this->webAuthnService->authenticateEmployeur($email, $webauthnResponse);

            if (!$employeur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentification échouée'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Authentification réussie',
                'data' => [
                    'employeur' => [
                        'id' => $employeur->id,
                        'nom_complet' => $employeur->nom_complet,
                        'email' => $employeur->email,
                        'matricule' => $employeur->matricule,
                        'entreprise' => [
                            'id' => $employeur->entreprise->id,
                            'nom' => $employeur->entreprise->nom
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification WebAuthn', [
                'error' => $e->getMessage(),
                'email' => $request->input('email')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'authentification'
            ], 500);
        }
    }

    /**
     * Valide la géolocalisation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateLocation(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'site_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coordonnées GPS invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $siteId = $request->input('site_id');

            // Récupérer le site
            $site = Site::find($siteId);

            if (!$site) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site non trouvé'
                ], 404);
            }

            // Valider la géolocalisation
            $validation = $this->presenceService->validateGeolocation($latitude, $longitude, $site);

            return response()->json([
                'success' => $validation['valid'],
                'message' => $validation['message'],
                'data' => [
                    'distance' => $validation['distance'],
                    'rayon_autorise' => $validation['rayon_autorise'] ?? null,
                    'geofencing_enabled' => $site->has_geofencing
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation de géolocalisation', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation de la position'
            ], 500);
        }
    }

    /**
     * Enregistre un pointage (entrée ou sortie)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recordPointage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employeur_id' => 'required|string',
                'site_id' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'webauthn_verified' => 'required|boolean',
                'webauthn_credential_id' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de pointage invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Récupérer l'employeur et le site
            $employeur = Employeur::find($request->input('employeur_id'));
            $site = Site::find($request->input('site_id'));

            if (!$employeur || !$site) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employeur ou site non trouvé'
                ], 404);
            }

            // Vérifier que l'employeur appartient à la même entreprise que le site
            if ($employeur->entreprise_id !== $site->entreprise_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employeur non autorisé pour ce site'
                ], 403);
            }

            // Valider la géolocalisation
            $geoValidation = $this->presenceService->validateGeolocation(
                $request->input('latitude'),
                $request->input('longitude'),
                $site
            );

            if (!$geoValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $geoValidation['message'],
                    'data' => [
                        'distance' => $geoValidation['distance'],
                        'rayon_autorise' => $geoValidation['rayon_autorise'] ?? null
                    ]
                ], 400);
            }

            // Préparer les données de pointage
            $pointageData = [
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
                'distance' => $geoValidation['distance'],
                'webauthn_verified' => $request->input('webauthn_verified'),
                'webauthn_credential_id' => $request->input('webauthn_credential_id'),
                'geolocation_verified' => $geoValidation['valid']
            ];

            // Enregistrer le pointage
            $presence = $this->presenceService->recordMobilePresence($employeur, $site, $pointageData);

            // Déterminer le type de pointage
            $isCheckout = $presence->date_heure_sortie !== null;

            return response()->json([
                'success' => true,
                'message' => $isCheckout ? 'Pointage de sortie enregistré' : 'Pointage d\'entrée enregistré',
                'data' => [
                    'presence_id' => $presence->id,
                    'type' => $isCheckout ? 'sortie' : 'entree',
                    'date_heure' => $isCheckout ? $presence->date_heure_sortie : $presence->date_heure_entree,
                    'statut' => $presence->statut,
                    'minutes_retard' => $presence->minutes_retard,
                    'minutes_travaillees' => $presence->minutes_travaillees,
                    'site' => [
                        'nom' => $site->nom,
                        'adresse' => $site->adresse
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du pointage', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du pointage'
            ], 500);
        }
    }

    /**
     * Récupère l'historique des pointages d'un employé
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPresenceHistory(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employeur_id' => 'required|string',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $employeur = Employeur::find($request->input('employeur_id'));

            if (!$employeur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employeur non trouvé'
                ], 404);
            }

            $limit = $request->input('limit', 10);
            $presences = $this->presenceService->getEmployeePresenceHistory($employeur, $limit);

            $formattedPresences = $presences->map(function ($presence) {
                return [
                    'id' => $presence->id,
                    'date_entree' => $presence->date_heure_entree,
                    'date_sortie' => $presence->date_heure_sortie,
                    'statut' => $presence->statut,
                    'minutes_travaillees' => $presence->minutes_travaillees,
                    'minutes_retard' => $presence->minutes_retard,
                    'site' => [
                        'nom' => $presence->site->nom,
                        'adresse' => $presence->site->adresse
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Historique récupéré',
                'data' => [
                    'presences' => $formattedPresences,
                    'total' => $presences->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'historique', [
                'error' => $e->getMessage(),
                'employeur_id' => $request->input('employeur_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique'
            ], 500);
        }
    }

    /**
     * Récupère le statut de pointage actuel d'un employé
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCurrentStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employeur_id' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID employeur requis',
                    'errors' => $validator->errors()
                ], 400);
            }

            $employeur = Employeur::find($request->input('employeur_id'));

            if (!$employeur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employeur non trouvé'
                ], 404);
            }

            // Vérifier s'il y a un pointage en cours aujourd'hui
            $today = Carbon::today();
            $currentPresence = $employeur->presences()
                ->whereDate('date_heure_entree', $today)
                ->whereNull('date_heure_sortie')
                ->with('site')
                ->first();

            if ($currentPresence) {
                // Calculer le temps de travail actuel
                $tempsEcoule = Carbon::now()->diffInMinutes($currentPresence->date_heure_entree);

                return response()->json([
                    'success' => true,
                    'message' => 'Pointage en cours',
                    'data' => [
                        'is_checked_in' => true,
                        'presence_id' => $currentPresence->id,
                        'heure_entree' => $currentPresence->date_heure_entree,
                        'temps_ecoule_minutes' => $tempsEcoule,
                        'statut' => $currentPresence->statut,
                        'site' => [
                            'nom' => $currentPresence->site->nom,
                            'adresse' => $currentPresence->site->adresse
                        ]
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Aucun pointage en cours',
                    'data' => [
                        'is_checked_in' => false
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du statut', [
                'error' => $e->getMessage(),
                'employeur_id' => $request->input('employeur_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du statut'
            ], 500);
        }
    }

    /**
     * Calcule les heures de travail pour une période
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkingHours(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'employeur_id' => 'required|string',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres de période invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $employeur = Employeur::find($request->input('employeur_id'));

            if (!$employeur) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employeur non trouvé'
                ], 404);
            }

            $dateDebut = Carbon::parse($request->input('date_debut'));
            $dateFin = Carbon::parse($request->input('date_fin'));

            $workingHours = $this->presenceService->calculateWorkingHours($employeur, $dateDebut, $dateFin);

            return response()->json([
                'success' => true,
                'message' => 'Heures de travail calculées',
                'data' => $workingHours
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du calcul des heures de travail', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des heures de travail'
            ], 500);
        }
    }
}
