<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\QRCodeService;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class QRCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Génère un QR code pour un site
     *
     * @param Request $request
     * @param string $siteId
     * @return JsonResponse
     */
    public function generateQRCode(Request $request, string $siteId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'expiration_hours' => 'nullable|integer|min:1|max:168' // Max 7 jours
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Récupérer le site
            $site = Site::find($siteId);

            if (!$site) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site non trouvé'
                ], 404);
            }

            // Vérifier les permissions (l'utilisateur doit appartenir à la même entreprise)
            $user = Auth::user();
            if ($user->entreprise_id !== $site->entreprise_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à ce site'
                ], 403);
            }

            $expirationHours = $request->input('expiration_hours', 24);
            $result = $this->qrCodeService->generateSiteQRCode($site, $expirationHours);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR code généré avec succès',
                'data' => [
                    'qr_data' => $result['qr_data'],
                    'qr_url' => $result['qr_url'],
                    'expires_at' => $result['expires_at'],
                    'site' => $result['site']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du QR code'
            ], 500);
        }
    }

    /**
     * Rafraîchit le QR code d'un site
     *
     * @param Request $request
     * @param string $siteId
     * @return JsonResponse
     */
    public function refreshQRCode(Request $request, string $siteId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'expiration_hours' => 'nullable|integer|min:1|max:168'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $site = Site::find($siteId);

            if (!$site) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site non trouvé'
                ], 404);
            }

            // Vérifier les permissions
            $user = Auth::user();
            if ($user->entreprise_id !== $site->entreprise_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à ce site'
                ], 403);
            }

            $expirationHours = $request->input('expiration_hours', 24);
            $result = $this->qrCodeService->refreshQRCode($site, $expirationHours);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR code rafraîchi avec succès',
                'data' => [
                    'qr_data' => $result['qr_data'],
                    'qr_url' => $result['qr_url'],
                    'expires_at' => $result['expires_at'],
                    'site' => $result['site']
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du QR code'
            ], 500);
        }
    }

    /**
     * Invalide le QR code d'un site
     *
     * @param string $siteId
     * @return JsonResponse
     */
    public function invalidateQRCode(string $siteId): JsonResponse
    {
        try {
            $site = Site::find($siteId);

            if (!$site) {
                return response()->json([
                    'success' => false,
                    'message' => 'Site non trouvé'
                ], 404);
            }

            // Vérifier les permissions
            $user = Auth::user();
            if ($user->entreprise_id !== $site->entreprise_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à ce site'
                ], 403);
            }

            $success = $this->qrCodeService->invalidateQRCode($site);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'invalidation du QR code'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR code invalidé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'invalidation du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $siteId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'invalidation du QR code'
            ], 500);
        }
    }

    /**
     * Récupère les informations d'un QR code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getQRCodeInfo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|min:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token requis',
                    'errors' => $validator->errors()
                ], 400);
            }

            $token = $request->input('token');
            $qrInfo = $this->qrCodeService->getQRCodeInfo($token);

            if (!$qrInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR code invalide ou expiré'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Informations du QR code récupérées',
                'data' => $qrInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations du QR code', [
                'error' => $e->getMessage(),
                'token' => substr($request->input('token', ''), 0, 10) . '...'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations'
            ], 500);
        }
    }

    /**
     * Génère des QR codes pour tous les sites d'une entreprise
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateQRCodesForEntreprise(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'expiration_hours' => 'nullable|integer|min:1|max:168'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres invalides',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            if (!$entrepriseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non associé à une entreprise'
                ], 400);
            }

            $expirationHours = $request->input('expiration_hours', 24);
            $result = $this->qrCodeService->generateQRCodesForEntreprise($entrepriseId, $expirationHours);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR codes générés pour l\'entreprise',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des QR codes pour l\'entreprise', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des QR codes'
            ], 500);
        }
    }

    /**
     * Récupère les statistiques d'utilisation des QR codes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getQRCodeStats(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after_or_equal:date_debut'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Période invalide',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            if (!$entrepriseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non associé à une entreprise'
                ], 400);
            }

            $dateDebut = \Carbon\Carbon::parse($request->input('date_debut'));
            $dateFin = \Carbon\Carbon::parse($request->input('date_fin'));

            $stats = $this->qrCodeService->getQRCodeStats($entrepriseId, $dateDebut, $dateFin);

            if (isset($stats['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $stats['error']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques QR code', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Liste tous les sites avec leur statut de QR code
     *
     * @return JsonResponse
     */
    public function listSitesWithQRStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            $entrepriseId = $user->entreprise_id;

            if (!$entrepriseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non associé à une entreprise'
                ], 400);
            }

            $sites = Site::where('entreprise_id', $entrepriseId)
                ->select(['id', 'nom', 'adresse', 'ville', 'statut', 'qr_token', 'qr_generated_at'])
                ->get()
                ->map(function ($site) {
                    $hasQR = !empty($site->qr_token);
                    $isExpired = false;
                    
                    if ($hasQR && $site->qr_generated_at) {
                        $isExpired = $site->qr_generated_at->addHours(24)->isPast();
                    }

                    return [
                        'id' => $site->id,
                        'nom' => $site->nom,
                        'adresse' => $site->adresse,
                        'ville' => $site->ville,
                        'statut' => $site->statut,
                        'qr_status' => [
                            'has_qr' => $hasQR,
                            'is_expired' => $isExpired,
                            'generated_at' => $site->qr_generated_at,
                            'expires_at' => $hasQR && $site->qr_generated_at ? 
                                $site->qr_generated_at->addHours(24) : null
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Liste des sites récupérée',
                'data' => [
                    'sites' => $sites,
                    'total' => $sites->count(),
                    'with_qr' => $sites->where('qr_status.has_qr', true)->count(),
                    'expired' => $sites->where('qr_status.is_expired', true)->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la liste des sites', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la liste des sites'
            ], 500);
        }
    }

    /**
     * Nettoie les QR codes expirés
     *
     * @return JsonResponse
     */
    public function cleanupExpiredQRCodes(): JsonResponse
    {
        try {
            $cleanedCount = $this->qrCodeService->cleanupExpiredQRCodes();

            return response()->json([
                'success' => true,
                'message' => "Nettoyage terminé. {$cleanedCount} QR codes expirés supprimés.",
                'data' => [
                    'cleaned_count' => $cleanedCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage des QR codes expirés', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage des QR codes expirés'
            ], 500);
        }
    }
}
