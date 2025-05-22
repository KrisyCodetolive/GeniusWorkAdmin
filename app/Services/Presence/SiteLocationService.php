<?php

namespace App\Services\Presence;

use App\Models\Employeur;
use App\Models\Site;
use App\Models\Entreprise;

class SiteLocationService
{
    /**
     * Récupère les coordonnées du site principal associé à un employé
     *
     * @param string $employeId ID ou QR code secret de l'employé
     * @return array Coordonnées du site et configuration de géolocalisation
     */
    public function getEmployeSiteCoordinates($employeId)
    {
        // Rechercher l'employé par ID ou QR code secret
        $employe = is_string($employeId) && strlen($employeId) === 32 
            ? Employeur::where('qr_code_secret', $employeId)->first()
            : Employeur::find($employeId);

        if (!$employe) {
            return [
                'status' => 'error',
                'message' => 'Employé non trouvé'
            ];
        }

        // Vérifier si l'employé a un site spécifique configuré dans ses métadonnées
        $siteId = $employe->getMeta('site_id');
        
        if ($siteId) {
            $site = Site::find($siteId);
            if ($site && $site->has_geofencing && $site->latitude && $site->longitude) {
                return $this->formatSiteResponse($site);
            }
        }

        // Si l'employé est associé à une filiale, vérifier si la filiale a un site
        if ($employe->filiale_id) {
            $filiale = $employe->filiale;
            if ($filiale) {
                // Rechercher le site principal de la filiale
                $site = Site::where('entreprise_id', $employe->entreprise_id)
                    ->where('nom', 'LIKE', "%{$filiale->nom}%")
                    ->where('has_geofencing', true)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->first();
                
                if ($site) {
                    return $this->formatSiteResponse($site);
                }
            }
        }

        // Si aucun site spécifique n'est trouvé, utiliser le site principal de l'entreprise
        $sites = Site::where('entreprise_id', $employe->entreprise_id)
            ->where('has_geofencing', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($sites->isNotEmpty()) {
            // Utiliser le premier site avec géofencing activé
            return $this->formatSiteResponse($sites->first());
        }

        // Si aucun site n'est trouvé, utiliser les coordonnées de l'entreprise si disponibles
        $entreprise = Entreprise::find($employe->entreprise_id);
        if ($entreprise && $entreprise->latitude && $entreprise->longitude) {
            return [
                'status' => 'success',
                'site' => [
                    'nom' => $entreprise->nom,
                    'adresse' => $entreprise->adresse . ', ' . $entreprise->ville . ', ' . $entreprise->pays,
                    'latitude' => (float) $entreprise->latitude,
                    'longitude' => (float) $entreprise->longitude,
                    'rayon_geofencing' => 100, // Rayon par défaut
                    'has_geofencing' => true
                ]
            ];
        }

        // Aucune coordonnée trouvée
        return [
            'status' => 'error',
            'message' => 'Aucune coordonnée de site trouvée pour cet employé'
        ];
    }

    /**
     * Formate la réponse pour un site
     *
     * @param Site $site
     * @return array
     */
    private function formatSiteResponse(Site $site)
    {
        return [
            'status' => 'success',
            'site' => [
                'id' => $site->id,
                'nom' => $site->nom,
                'adresse' => $site->adresse . ', ' . $site->ville . ', ' . $site->pays,
                'latitude' => (float) $site->latitude,
                'longitude' => (float) $site->longitude,
                'rayon_geofencing' => (int) $site->rayon_geofencing ?? 100,
                'has_geofencing' => (bool) $site->has_geofencing
            ]
        ];
    }

    /**
     * Vérifie si un point GPS est dans le rayon autorisé pour un employé
     *
     * @param string $employeId ID ou QR code secret de l'employé
     * @param float $latitude Latitude du point à vérifier
     * @param float $longitude Longitude du point à vérifier
     * @return array Résultat de la vérification
     */
    public function verifierPositionEmploye($employeId, $latitude, $longitude)
    {
        $siteInfo = $this->getEmployeSiteCoordinates($employeId);
        
        if ($siteInfo['status'] === 'error') {
            return $siteInfo;
        }

        $site = $siteInfo['site'];
        
        if (!$site['has_geofencing']) {
            return [
                'status' => 'success',
                'in_zone' => true,
                'distance' => 0,
                'rayon_maximum' => $site['rayon_geofencing'],
                'message' => 'La vérification de géolocalisation est désactivée pour ce site'
            ];
        }

        // Calculer la distance entre le point et le site
        $distance = $this->calculerDistance(
            $latitude,
            $longitude,
            $site['latitude'],
            $site['longitude']
        );

        $inZone = $distance <= $site['rayon_geofencing'];

        return [
            'status' => 'success',
            'in_zone' => $inZone,
            'distance' => round($distance),
            'rayon_maximum' => $site['rayon_geofencing'],
            'message' => $inZone 
                ? 'Position valide dans la zone autorisée' 
                : 'Position en dehors de la zone autorisée'
        ];
    }

    /**
     * Calculer la distance entre deux points GPS en mètres.
     *
     * @param float $lat1 Latitude du premier point
     * @param float $lon1 Longitude du premier point
     * @param float $lat2 Latitude du deuxième point
     * @param float $lon2 Longitude du deuxième point
     * @return float Distance en mètres
     */
    private function calculerDistance($lat1, $lon1, $lat2, $lon2)
    {
        // Formule de Haversine pour calculer la distance entre deux points GPS
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
}
