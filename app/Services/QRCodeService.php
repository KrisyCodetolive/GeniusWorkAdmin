<?php

namespace App\Services;

use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class QRCodeService
{
    /**
     * Génère un QR code pour un site
     *
     * @param Site $site
     * @param int $expirationHours Durée de validité en heures (défaut: 24h)
     * @return array
     */
    public function generateSiteQRCode(Site $site, int $expirationHours = 24): array
    {
        try {
            // Générer un token unique et sécurisé
            $token = $this->generateSecureToken();
            $expiresAt = Carbon::now()->addHours($expirationHours);
            
            // Mettre à jour le site avec le nouveau token
            $site->update([
                'qr_token' => $token,
                'qr_generated_at' => Carbon::now()
            ]);
            
            // Créer les données du QR code
            $qrData = [
                'site_id' => $site->id,
                'token' => $token,
                'expires_at' => $expiresAt->toISOString(),
                'url' => $this->generatePointageUrl($site->id, $token)
            ];
            
            // Stocker temporairement en cache pour validation rapide
            Cache::put(
                "qr_token_{$token}", 
                [
                    'site_id' => $site->id,
                    'expires_at' => $expiresAt
                ], 
                $expiresAt
            );
            
            Log::info('QR code généré pour le site', [
                'site_id' => $site->id,
                'site_nom' => $site->nom,
                'expires_at' => $expiresAt->toDateTimeString(),
                'token' => substr($token, 0, 10) . '...'
            ]);
            
            return [
                'success' => true,
                'qr_data' => json_encode($qrData),
                'qr_url' => $qrData['url'],
                'token' => $token,
                'expires_at' => $expiresAt,
                'site' => [
                    'id' => $site->id,
                    'nom' => $site->nom,
                    'adresse' => $site->adresse
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $site->id
            ]);
            
            return [
                'success' => false,
                'error' => 'Erreur lors de la génération du QR code'
            ];
        }
    }

    /**
     * Valide un token de QR code
     *
     * @param string $token
     * @return Site|null
     */
    public function validateQRCode(string $token): ?Site
    {
        try {
            // Vérifier d'abord dans le cache pour une validation rapide
            $cachedData = Cache::get("qr_token_{$token}");
            
            if ($cachedData) {
                $site = Site::find($cachedData['site_id']);
                
                if ($site && $site->qr_token === $token) {
                    // Vérifier l'expiration
                    $expiresAt = Carbon::parse($cachedData['expires_at']);
                    
                    if ($expiresAt->isFuture()) {
                        Log::info('QR code validé depuis le cache', [
                            'site_id' => $site->id,
                            'token' => substr($token, 0, 10) . '...'
                        ]);
                        return $site;
                    }
                }
            }
            
            // Validation depuis la base de données
            $site = Site::where('qr_token', $token)->first();
            
            if (!$site) {
                Log::warning('Token QR code non trouvé', [
                    'token' => substr($token, 0, 10) . '...'
                ]);
                return null;
            }
            
            // Vérifier l'expiration (24h par défaut)
            if ($site->qr_generated_at && $site->qr_generated_at->addHours(24)->isPast()) {
                Log::warning('QR code expiré', [
                    'site_id' => $site->id,
                    'generated_at' => $site->qr_generated_at->toDateTimeString(),
                    'token' => substr($token, 0, 10) . '...'
                ]);
                return null;
            }
            
            // Vérifier que le site est actif
            if (!$site->estActif()) {
                Log::warning('Tentative d\'utilisation d\'un QR code pour un site inactif', [
                    'site_id' => $site->id,
                    'statut' => $site->statut
                ]);
                return null;
            }
            
            Log::info('QR code validé depuis la base de données', [
                'site_id' => $site->id,
                'site_nom' => $site->nom
            ]);
            
            return $site;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation du QR code', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 10) . '...'
            ]);
            return null;
        }
    }

    /**
     * Rafraîchit le QR code d'un site
     *
     * @param Site $site
     * @param int $expirationHours
     * @return array
     */
    public function refreshQRCode(Site $site, int $expirationHours = 24): array
    {
        try {
            // Invalider l'ancien token s'il existe
            if ($site->qr_token) {
                Cache::forget("qr_token_{$site->qr_token}");
                
                Log::info('Ancien QR code invalidé', [
                    'site_id' => $site->id,
                    'old_token' => substr($site->qr_token, 0, 10) . '...'
                ]);
            }
            
            // Générer un nouveau QR code
            return $this->generateSiteQRCode($site, $expirationHours);
        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $site->id
            ]);
            
            return [
                'success' => false,
                'error' => 'Erreur lors du rafraîchissement du QR code'
            ];
        }
    }

    /**
     * Invalide un QR code
     *
     * @param Site $site
     * @return bool
     */
    public function invalidateQRCode(Site $site): bool
    {
        try {
            if ($site->qr_token) {
                // Supprimer du cache
                Cache::forget("qr_token_{$site->qr_token}");
                
                // Supprimer de la base de données
                $site->update([
                    'qr_token' => null,
                    'qr_generated_at' => null
                ]);
                
                Log::info('QR code invalidé', [
                    'site_id' => $site->id,
                    'token' => substr($site->qr_token, 0, 10) . '...'
                ]);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'invalidation du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $site->id
            ]);
            return false;
        }
    }

    /**
     * Récupère les informations d'un QR code
     *
     * @param string $token
     * @return array|null
     */
    public function getQRCodeInfo(string $token): ?array
    {
        try {
            $site = $this->validateQRCode($token);
            
            if (!$site) {
                return null;
            }
            
            return [
                'site' => [
                    'id' => $site->id,
                    'nom' => $site->nom,
                    'adresse' => $site->adresse,
                    'ville' => $site->ville,
                    'has_geofencing' => $site->has_geofencing,
                    'rayon_geofencing' => $site->rayon_geofencing,
                    'latitude' => $site->latitude,
                    'longitude' => $site->longitude
                ],
                'qr_info' => [
                    'generated_at' => $site->qr_generated_at,
                    'expires_at' => $site->qr_generated_at ? $site->qr_generated_at->addHours(24) : null,
                    'is_valid' => true
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations du QR code', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 10) . '...'
            ]);
            return null;
        }
    }

    /**
     * Génère une liste de QR codes pour tous les sites d'une entreprise
     *
     * @param string|int $entrepriseId
     * @param int $expirationHours
     * @return array
     */
    public function generateQRCodesForEntreprise($entrepriseId, int $expirationHours = 24): array
    {
        try {
            $sites = Site::where('entreprise_id', $entrepriseId)
                ->where('statut', 'actif')
                ->get();
            
            $results = [];
            
            foreach ($sites as $site) {
                $qrResult = $this->generateSiteQRCode($site, $expirationHours);
                $results[] = [
                    'site_id' => $site->id,
                    'site_nom' => $site->nom,
                    'qr_result' => $qrResult
                ];
            }
            
            Log::info('QR codes générés pour l\'entreprise', [
                'entreprise_id' => $entrepriseId,
                'sites_count' => count($sites),
                'success_count' => count(array_filter($results, fn($r) => $r['qr_result']['success']))
            ]);
            
            return [
                'success' => true,
                'entreprise_id' => $entrepriseId,
                'sites' => $results,
                'total_sites' => count($sites)
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des QR codes pour l\'entreprise', [
                'error' => $e->getMessage(),
                'entreprise_id' => $entrepriseId
            ]);
            
            return [
                'success' => false,
                'error' => 'Erreur lors de la génération des QR codes'
            ];
        }
    }

    /**
     * Nettoie les QR codes expirés
     *
     * @return int Nombre de QR codes nettoyés
     */
    public function cleanupExpiredQRCodes(): int
    {
        try {
            $expiredSites = Site::whereNotNull('qr_token')
                ->whereNotNull('qr_generated_at')
                ->where('qr_generated_at', '<', Carbon::now()->subHours(24))
                ->get();
            
            $cleanedCount = 0;
            
            foreach ($expiredSites as $site) {
                if ($this->invalidateQRCode($site)) {
                    $cleanedCount++;
                }
            }
            
            Log::info('Nettoyage des QR codes expirés terminé', [
                'cleaned_count' => $cleanedCount,
                'total_expired' => $expiredSites->count()
            ]);
            
            return $cleanedCount;
        } catch (\Exception $e) {
            Log::error('Erreur lors du nettoyage des QR codes expirés', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Génère un token sécurisé
     *
     * @return string
     */
    private function generateSecureToken(): string
    {
        return Str::random(64);
    }

    /**
     * Génère l'URL de pointage pour un site
     *
     * @param string $siteId
     * @param string $token
     * @return string
     */
    private function generatePointageUrl(string $siteId, string $token): string
    {
        return url("/mobile/pointage/{$siteId}?token={$token}");
    }

    /**
     * Génère les statistiques d'utilisation des QR codes
     *
     * @param string|int $entrepriseId
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @return array
     */
    public function getQRCodeStats($entrepriseId, Carbon $dateDebut, Carbon $dateFin): array
    {
        try {
            // Récupérer les sites de l'entreprise
            $sites = Site::where('entreprise_id', $entrepriseId)->get();
            
            $stats = [
                'total_sites' => $sites->count(),
                'sites_with_qr' => $sites->whereNotNull('qr_token')->count(),
                'active_qr_codes' => 0,
                'expired_qr_codes' => 0,
                'usage_by_site' => []
            ];
            
            foreach ($sites as $site) {
                if ($site->qr_token && $site->qr_generated_at) {
                    if ($site->qr_generated_at->addHours(24)->isFuture()) {
                        $stats['active_qr_codes']++;
                    } else {
                        $stats['expired_qr_codes']++;
                    }
                }
                
                // Compter les utilisations (pointages via mobile)
                $usageCount = $site->pointages()
                    ->where('source', 'mobile_web')
                    ->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
                    ->count();
                
                if ($usageCount > 0) {
                    $stats['usage_by_site'][] = [
                        'site_id' => $site->id,
                        'site_nom' => $site->nom,
                        'usage_count' => $usageCount
                    ];
                }
            }
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des statistiques QR code', [
                'error' => $e->getMessage(),
                'entreprise_id' => $entrepriseId
            ]);
            
            return [
                'error' => 'Erreur lors de la génération des statistiques'
            ];
        }
    }
}
