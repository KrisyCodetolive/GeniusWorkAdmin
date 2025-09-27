<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\User;
use App\Models\Site;
use App\Models\Employeur;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PresenceService
{
    /**
     * Récupère les présences pour l'entreprise de l'utilisateur connecté
     *
     * @param array $filters Filtres à appliquer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPresencesForEntreprise(array $filters = [])
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise->id;
        
        $query = Presence::query()
            ->whereHas('user', function ($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            });
            
        // Appliquer les filtres
        if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
            $query->whereBetween('date', [$filters['date_debut'], $filters['date_fin']]);
        } elseif (!empty($filters['date_debut'])) {
            $query->where('date', '>=', $filters['date_debut']);
        } elseif (!empty($filters['date_fin'])) {
            $query->where('date', '<=', $filters['date_fin']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        
        if (!empty($filters['departement_id'])) {
            $query->whereHas('user', function ($query) use ($filters) {
                $query->where('departement_id', $filters['departement_id']);
            });
        }
        
        return $query->with(['user'])->orderBy('date', 'desc')->get();
    }
    
    /**
     * Valide une présence
     *
     * @param int $presenceId ID de la présence
     * @return Presence
     */
    public function validerPresence(int $presenceId)
    {
        $presence = Presence::findOrFail($presenceId);
        $presence->status = 'validé';
        $presence->validated_at = Carbon::now();
        $presence->validated_by = Auth::id();
        $presence->save();
        
        return $presence;
    }
    
    /**
     * Rejette une présence
     *
     * @param int $presenceId ID de la présence
     * @param string $motif Motif du rejet
     * @return Presence
     */
    public function rejeterPresence(int $presenceId, string $motif)
    {
        $presence = Presence::findOrFail($presenceId);
        $presence->status = 'rejeté';
        $presence->rejection_reason = $motif;
        $presence->rejected_at = Carbon::now();
        $presence->rejected_by = Auth::id();
        $presence->save();
        
        return $presence;
    }

    // ==========================================
    // NOUVELLES MÉTHODES POUR POINTAGE MOBILE
    // ==========================================

    /**
     * Valide un QR code de site
     *
     * @param string $token Token du QR code
     * @param string $siteId ID du site
     * @return bool
     */
    public function validateQRCode(string $token, string $siteId): bool
    {
        try {
            $site = Site::find($siteId);
            
            if (!$site) {
                Log::warning('Site non trouvé pour le QR code', ['site_id' => $siteId]);
                return false;
            }

            // Vérifier si le site a un QR code valide
            if (!$site->validateQRCode($token)) {
                Log::warning('QR code invalide ou expiré', [
                    'site_id' => $siteId,
                    'token' => substr($token, 0, 10) . '...'
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation du QR code', [
                'error' => $e->getMessage(),
                'site_id' => $siteId
            ]);
            return false;
        }
    }

    /**
     * Valide la géolocalisation par rapport au site
     *
     * @param float $latitude Latitude de l'employé
     * @param float $longitude Longitude de l'employé
     * @param Site $site Site de référence
     * @return array Résultat de la validation avec distance
     */
    public function validateGeolocation(float $latitude, float $longitude, Site $site): array
    {
        try {
            // Si le géofencing n'est pas activé, on accepte
            if (!$site->has_geofencing) {
                return [
                    'valid' => true,
                    'distance' => 0,
                    'message' => 'Géofencing désactivé pour ce site'
                ];
            }

            // Vérifier si l'employé est dans le rayon
            $isInRadius = $site->estDansRayon($latitude, $longitude);
            $distance = $this->calculateDistance($latitude, $longitude, $site->latitude, $site->longitude);

            return [
                'valid' => $isInRadius,
                'distance' => round($distance, 2),
                'rayon_autorise' => $site->rayon_geofencing,
                'message' => $isInRadius 
                    ? 'Position validée' 
                    : "Vous êtes à {$distance}m du site (rayon autorisé: {$site->rayon_geofencing}m)"
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation de géolocalisation', [
                'error' => $e->getMessage(),
                'site_id' => $site->id
            ]);
            
            return [
                'valid' => false,
                'distance' => null,
                'message' => 'Erreur lors de la validation de la position'
            ];
        }
    }

    /**
     * Enregistre une présence mobile
     *
     * @param Employeur $employeur Employé qui pointe
     * @param Site $site Site de pointage
     * @param array $data Données supplémentaires
     * @return Presence
     */
    public function recordMobilePresence(Employeur $employeur, Site $site, array $data): Presence
    {
        try {
            DB::beginTransaction();

            // Vérifier s'il y a déjà un pointage aujourd'hui
            $today = Carbon::today();
            $existingPresence = Presence::where('employeur_id', $employeur->id)
                ->where('site_id', $site->id)
                ->whereDate('date_heure_entree', $today)
                ->whereNull('date_heure_sortie')
                ->first();

            if ($existingPresence) {
                // C'est un pointage de sortie
                return $this->recordCheckout($existingPresence, $data);
            } else {
                // C'est un pointage d'entrée
                return $this->recordCheckin($employeur, $site, $data);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement de présence mobile', [
                'error' => $e->getMessage(),
                'employeur_id' => $employeur->id,
                'site_id' => $site->id
            ]);
            throw $e;
        }
    }

    /**
     * Enregistre un pointage d'entrée
     *
     * @param Employeur $employeur
     * @param Site $site
     * @param array $data
     * @return Presence
     */
    private function recordCheckin(Employeur $employeur, Site $site, array $data): Presence
    {
        $now = Carbon::now();
        
        // Calculer le statut (à temps, retard, etc.)
        $status = $this->calculatePresenceStatus($employeur, $site, $now);
        
        $presence = Presence::create([
            'employeur_id' => $employeur->id,
            'site_id' => $site->id,
            'date_heure_entree' => $now,
            'date_heure' => $now,
            'type' => 'entree',
            'latitude_entree' => $data['latitude'] ?? null,
            'longitude_entree' => $data['longitude'] ?? null,
            'distance_site' => $data['distance'] ?? null,
            'adresse_ip_entree' => request()->ip(),
            'appareil_entree' => request()->userAgent(),
            'navigateur' => $this->getBrowserInfo(),
            'source' => 'mobile_web',
            'statut' => $status['statut'],
            'minutes_retard' => $status['minutes_retard'],
            'webauthn_credential_id' => $data['webauthn_credential_id'] ?? null,
            'verification_data' => [
                'webauthn_verified' => $data['webauthn_verified'] ?? false,
                'geolocation_verified' => $data['geolocation_verified'] ?? false,
                'qr_code_verified' => true,
                'timestamp' => $now->toISOString()
            ]
        ]);

        DB::commit();
        
        Log::info('Pointage d\'entrée enregistré', [
            'presence_id' => $presence->id,
            'employeur_id' => $employeur->id,
            'site_id' => $site->id,
            'statut' => $status['statut']
        ]);

        return $presence;
    }

    /**
     * Enregistre un pointage de sortie
     *
     * @param Presence $presence
     * @param array $data
     * @return Presence
     */
    private function recordCheckout(Presence $presence, array $data): Presence
    {
        $now = Carbon::now();
        
        // Calculer les heures travaillées
        $heuresTravaillees = $this->calculateWorkingMinutes($presence->date_heure_entree, $now);
        
        $presence->update([
            'date_heure_sortie' => $now,
            'type' => 'sortie',
            'latitude_sortie' => $data['latitude'] ?? null,
            'longitude_sortie' => $data['longitude'] ?? null,
            'adresse_ip_sortie' => request()->ip(),
            'appareil_sortie' => request()->userAgent(),
            'minutes_travaillees' => $heuresTravaillees['minutes_travaillees'],
            'minutes_supplementaires' => $heuresTravaillees['minutes_supplementaires'],
            'duree_effective' => $heuresTravaillees['duree_effective'],
            'verification_data' => array_merge($presence->verification_data ?? [], [
                'checkout_webauthn_verified' => $data['webauthn_verified'] ?? false,
                'checkout_geolocation_verified' => $data['geolocation_verified'] ?? false,
                'checkout_timestamp' => $now->toISOString()
            ])
        ]);

        DB::commit();
        
        Log::info('Pointage de sortie enregistré', [
            'presence_id' => $presence->id,
            'minutes_travaillees' => $heuresTravaillees['minutes_travaillees']
        ]);

        return $presence;
    }

    /**
     * Calcule le statut de présence (à temps, retard, etc.)
     *
     * @param Employeur $employeur
     * @param Site $site
     * @param Carbon $dateHeure
     * @return array
     */
    private function calculatePresenceStatus(Employeur $employeur, Site $site, Carbon $dateHeure): array
    {
        // TODO: Implémenter la logique des horaires de travail
        // Pour l'instant, on considère 8h00 comme heure de début standard
        $heureDebut = Carbon::createFromTime(8, 0, 0, $dateHeure->timezone);
        $heureDebut->setDate($dateHeure->year, $dateHeure->month, $dateHeure->day);
        
        if ($dateHeure->lte($heureDebut)) {
            return [
                'statut' => 'present',
                'minutes_retard' => 0
            ];
        } else {
            $minutesRetard = $dateHeure->diffInMinutes($heureDebut);
            return [
                'statut' => 'retard',
                'minutes_retard' => $minutesRetard
            ];
        }
    }

    /**
     * Calcule les minutes de travail
     *
     * @param Carbon $entree
     * @param Carbon $sortie
     * @return array
     */
    private function calculateWorkingMinutes(Carbon $entree, Carbon $sortie): array
    {
        $dureeEffective = $sortie->diffInMinutes($entree);
        
        // Durée standard de travail (8 heures = 480 minutes)
        $dureeStandard = 480;
        
        $minutesTravaillees = min($dureeEffective, $dureeStandard);
        $minutesSupplementaires = max(0, $dureeEffective - $dureeStandard);
        
        return [
            'duree_effective' => $dureeEffective,
            'minutes_travaillees' => $minutesTravaillees,
            'minutes_supplementaires' => $minutesSupplementaires
        ];
    }

    /**
     * Calcule la distance entre deux points GPS
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance en mètres
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Rayon de la terre en mètres

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Récupère les informations du navigateur
     *
     * @return string
     */
    private function getBrowserInfo(): string
    {
        $userAgent = request()->userAgent();
        
        // Extraction simple du navigateur
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        }
        
        return 'Inconnu';
    }

    /**
     * Récupère l'historique des pointages pour un employé
     *
     * @param Employeur $employeur
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEmployeePresenceHistory(Employeur $employeur, int $limit = 10)
    {
        return Presence::where('employeur_id', $employeur->id)
            ->with(['site'])
            ->orderBy('date_heure_entree', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calcule les heures de travail pour un employé sur une période
     *
     * @param Employeur $employeur
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @return array
     */
    public function calculateWorkingHours(Employeur $employeur, Carbon $dateDebut, Carbon $dateFin): array
    {
        $presences = Presence::where('employeur_id', $employeur->id)
            ->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
            ->whereNotNull('date_heure_sortie')
            ->get();

        $totalMinutes = $presences->sum('minutes_travaillees');
        $totalSupplementaires = $presences->sum('minutes_supplementaires');
        $totalRetard = $presences->sum('minutes_retard');
        
        return [
            'total_heures' => round($totalMinutes / 60, 2),
            'heures_supplementaires' => round($totalSupplementaires / 60, 2),
            'minutes_retard' => $totalRetard,
            'jours_travailles' => $presences->count(),
            'presences' => $presences
        ];
    }
}
