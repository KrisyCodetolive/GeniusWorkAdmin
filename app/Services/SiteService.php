<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class SiteService
{
    /**
     * Récupérer tous les sites avec pagination
     *
     * @param int $perPage Nombre d'éléments par page
     * @param array $filters Filtres à appliquer
     * @return LengthAwarePaginator
     */
    public function getAllSites(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Site::query();
        
        // Appliquer les filtres
        if (isset($filters['entreprise_id']) && !empty($filters['entreprise_id'])) {
            $query->parEntreprise($filters['entreprise_id']);
        }
        
        if (isset($filters['statut'])) {
            if ($filters['statut'] === 'actif') {
                $query->actif();
            } elseif ($filters['statut'] === 'inactif') {
                $query->where('statut', 'inactif');
            }
        }
        
        if (isset($filters['geofencing']) && $filters['geofencing']) {
            $query->avecGeofencing();
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('adresse', 'like', "%{$search}%")
                  ->orWhere('ville', 'like', "%{$search}%")
                  ->orWhere('code_postal', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortField = $filters['sort_field'] ?? 'nom';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortField, $sortDirection);
        
        return $query->with('entreprise')->paginate($perPage);
    }
    
    /**
     * Récupérer un site par son ID
     *
     * @param string $id ID du site
     * @return Site|null
     */
    public function getSiteById(string $id): ?Site
    {
        return Site::with(['entreprise', 'employes'])->find($id);
    }
    
    /**
     * Créer un nouveau site
     *
     * @param array $data Données du site
     * @return Site
     */
    public function createSite(array $data): Site
    {
        try {
            DB::beginTransaction();
            
            // Formater les horaires si nécessaire
            if (isset($data['horaires']) && is_array($data['horaires'])) {
                // Les horaires sont déjà au format tableau, pas besoin de conversion
            } else {
                // Initialiser un format d'horaires par défaut
                $data['horaires'] = $this->getDefaultHoraires();
            }
            
            $site = Site::create($data);
            
            // Associer les employés si fournis
            if (isset($data['employes']) && is_array($data['employes'])) {
                $site->employes()->sync($data['employes']);
            }
            
            DB::commit();
            return $site;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du site: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Mettre à jour un site existant
     *
     * @param string $id ID du site
     * @param array $data Nouvelles données
     * @return Site|null
     */
    public function updateSite(string $id, array $data): ?Site
    {
        try {
            DB::beginTransaction();
            
            $site = Site::find($id);
            
            if (!$site) {
                return null;
            }
            
            // Formater les horaires si nécessaire
            if (isset($data['horaires']) && is_array($data['horaires'])) {
                // Les horaires sont déjà au format tableau
            } elseif (isset($data['horaires'])) {
                // Tenter de décoder les horaires si c'est une chaîne JSON
                $data['horaires'] = json_decode($data['horaires'], true);
            }
            
            $site->update($data);
            
            // Mettre à jour les employés associés si fournis
            if (isset($data['employes']) && is_array($data['employes'])) {
                $site->employes()->sync($data['employes']);
            }
            
            DB::commit();
            return $site;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du site: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Supprimer un site
     *
     * @param string $id ID du site
     * @return bool
     */
    public function deleteSite(string $id): bool
    {
        try {
            DB::beginTransaction();
            
            $site = Site::find($id);
            
            if (!$site) {
                return false;
            }
            
            // Vérifier s'il y a des pointages associés
            if ($site->pointages()->count() > 0) {
                throw new \Exception('Impossible de supprimer ce site car il possède des pointages associés.');
            }
            
            // Détacher les employés associés
            $site->employes()->detach();
            
            // Supprimer le site (soft delete)
            $site->delete();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du site: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Vérifier si un employé est dans le rayon d'un site
     *
     * @param string $siteId ID du site
     * @param float $latitude Latitude de l'employé
     * @param float $longitude Longitude de l'employé
     * @return bool
     */
    public function verifierPresenceEmploye(string $siteId, float $latitude, float $longitude): bool
    {
        $site = Site::find($siteId);
        
        if (!$site) {
            return false;
        }
        
        return $site->estDansRayon($latitude, $longitude);
    }
    
    /**
     * Récupérer les sites associés à un employé
     *
     * @param string $employeId ID de l'employé
     * @return Collection
     */
    public function getSitesParEmploye(string $employeId): Collection
    {
        return Site::whereHas('employes', function($query) use ($employeId) {
            $query->where('users.id', $employeId);
        })->get();
    }
    
    /**
     * Récupérer les sites d'une entreprise
     *
     * @param string $entrepriseId ID de l'entreprise
     * @return Collection
     */
    public function getSitesParEntreprise(string $entrepriseId): Collection
    {
        return Site::parEntreprise($entrepriseId)->get();
    }
    
    /**
     * Obtenir un format d'horaires par défaut
     *
     * @return array
     */
    private function getDefaultHoraires(): array
    {
        return [
            'lundi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
            'mardi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
            'mercredi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
            'jeudi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
            'vendredi' => ['ouverture' => '09:00', 'fermeture' => '18:00'],
            'samedi' => ['ouverture' => null, 'fermeture' => null],
            'dimanche' => ['ouverture' => null, 'fermeture' => null]
        ];
    }
    
    /**
     * Récupérer les statistiques des sites
     *
     * @param string|null $entrepriseId ID de l'entreprise (optionnel)
     * @return array
     */
    public function getStatistiquesSites(?string $entrepriseId = null): array
    {
        $query = Site::query();
        
        if ($entrepriseId) {
            $query->parEntreprise($entrepriseId);
        }
        
        $totalSites = $query->count();
        $sitesActifs = $query->actif()->count();
        $sitesInactifs = $query->where('statut', 'inactif')->count();
        $sitesAvecGeofencing = $query->avecGeofencing()->count();
        
        return [
            'total' => $totalSites,
            'actifs' => $sitesActifs,
            'inactifs' => $sitesInactifs,
            'avec_geofencing' => $sitesAvecGeofencing,
            'pourcentage_actifs' => $totalSites > 0 ? round(($sitesActifs / $totalSites) * 100, 2) : 0,
            'pourcentage_geofencing' => $totalSites > 0 ? round(($sitesAvecGeofencing / $totalSites) * 100, 2) : 0
        ];
    }
}
