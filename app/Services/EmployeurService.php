<?php

namespace App\Services;

use App\Models\Employeur;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeurService
{
    /**
     * Récupérer tous les employeurs avec pagination
     *
     * @param int $perPage Nombre d'éléments par page
     * @param array $filters Filtres à appliquer
     * @return LengthAwarePaginator
     */
    public function getAllEmployeurs(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Employeur::query();
        
        // Appliquer les filtres
        if (isset($filters['entreprise_id']) && !empty($filters['entreprise_id'])) {
            $query->parEntreprise($filters['entreprise_id']);
        }
        
        if (isset($filters['departement_id']) && !empty($filters['departement_id'])) {
            $query->parDepartement($filters['departement_id']);
        }
        
        if (isset($filters['statut'])) {
            if ($filters['statut'] === 'actif') {
                $query->actif();
            } elseif ($filters['statut'] === 'inactif') {
                $query->inactif();
            }
        }
        
        if (isset($filters['type_contrat']) && !empty($filters['type_contrat'])) {
            $query->where('type_contrat', $filters['type_contrat']);
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('matricule', 'like', "%{$search}%")
                  ->orWhere('code_employe', 'like', "%{$search}%");
            });
        }
        
        // Tri
        $sortField = $filters['sort_field'] ?? 'nom';
        $sortDirection = $filters['sort_direction'] ?? 'asc';
        $query->orderBy($sortField, $sortDirection);
        
        return $query->with(['entreprise', 'departement'])->paginate($perPage);
    }
    
    /**
     * Récupérer un employeur par son ID
     *
     * @param string $id ID de l'employeur
     * @return Employeur|null
     */
    public function getEmployeurById(string $id): ?Employeur
    {
        return Employeur::with(['entreprise', 'departement', 'user', 'presences', 'conges'])->find($id);
    }
    
    /**
     * Créer un nouvel employeur
     *
     * @param array $data Données de l'employeur
     * @param bool $createUser Créer un compte utilisateur associé
     * @return Employeur
     */
    public function createEmployeur(array $data, bool $createUser = false): Employeur
    {
        try {
            DB::beginTransaction();
            
            // Gérer l'upload de photo si présent
            if (isset($data['photo']) && $data['photo']) {
                $data['photo'] = $this->handlePhotoUpload($data['photo']);
            }
            
            // Préparer les méta-données et la configuration
            $data['meta_donnees'] = $data['meta_donnees'] ?? [];
            $data['configuration'] = $data['configuration'] ?? $this->getDefaultConfiguration();
            
            $employeur = Employeur::create($data);
            
            // Créer un compte utilisateur si demandé
            if ($createUser && isset($data['create_user']) && $data['create_user']) {
                $this->createUserForEmployeur($employeur, $data);
            }
            
            DB::commit();
            return $employeur;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de l\'employeur: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Mettre à jour un employeur existant
     *
     * @param string $id ID de l'employeur
     * @param array $data Nouvelles données
     * @return Employeur|null
     */
    public function updateEmployeur(string $id, array $data): ?Employeur
    {
        try {
            DB::beginTransaction();
            
            $employeur = Employeur::find($id);
            
            if (!$employeur) {
                return null;
            }
            
            // Gérer l'upload de photo si présent
            if (isset($data['photo']) && $data['photo']) {
                // Supprimer l'ancienne photo si elle existe
                if ($employeur->photo && Storage::exists($employeur->photo)) {
                    Storage::delete($employeur->photo);
                }
                
                $data['photo'] = $this->handlePhotoUpload($data['photo']);
            }
            
            // Mettre à jour les méta-données si présentes
            if (isset($data['meta_donnees']) && is_array($data['meta_donnees'])) {
                $currentMeta = $employeur->meta_donnees ?? [];
                $data['meta_donnees'] = array_merge($currentMeta, $data['meta_donnees']);
            }
            
            // Mettre à jour la configuration si présente
            if (isset($data['configuration']) && is_array($data['configuration'])) {
                $currentConfig = $employeur->configuration ?? [];
                $data['configuration'] = array_merge($currentConfig, $data['configuration']);
            }
            
            $employeur->update($data);
            
            // Mettre à jour l'utilisateur associé si nécessaire
            if (isset($data['update_user']) && $data['update_user'] && $employeur->user) {
                $this->updateUserForEmployeur($employeur, $data);
            } elseif (isset($data['create_user']) && $data['create_user'] && !$employeur->user) {
                $this->createUserForEmployeur($employeur, $data);
            }
            
            DB::commit();
            return $employeur;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de l\'employeur: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Supprimer un employeur
     *
     * @param string $id ID de l'employeur
     * @return bool
     */
    public function deleteEmployeur(string $id): bool
    {
        try {
            DB::beginTransaction();
            
            $employeur = Employeur::find($id);
            
            if (!$employeur) {
                return false;
            }
            
            // Vérifier s'il y a des dépendances qui empêchent la suppression
            if ($this->hasBlockingDependencies($employeur)) {
                throw new \Exception('Impossible de supprimer cet employeur car il possède des enregistrements associés.');
            }
            
            // Supprimer l'utilisateur associé si existant
            if ($employeur->user) {
                $employeur->user->delete();
            }
            
            // Supprimer la photo si elle existe
            if ($employeur->photo && Storage::exists($employeur->photo)) {
                Storage::delete($employeur->photo);
            }
            
            // Supprimer l'employeur (soft delete)
            $employeur->delete();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de l\'employeur: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Générer un nouveau QR code pour l'employeur
     *
     * @param string $id ID de l'employeur
     * @return array|null Données du QR code
     */
    public function regenerateQRCode(string $id): ?array
    {
        try {
            $employeur = Employeur::find($id);
            
            if (!$employeur) {
                return null;
            }
            
            $employeur->rotateQRCode();
            $employeur->save();
            
            return $employeur->getQRCodeData();
        } catch (\Exception $e) {
            Log::error('Erreur lors de la régénération du QR code: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Désactiver le QR code d'un employeur
     *
     * @param string $id ID de l'employeur
     * @return bool
     */
    public function deactivateQRCode(string $id): bool
    {
        try {
            $employeur = Employeur::find($id);
            
            if (!$employeur) {
                return false;
            }
            
            $employeur->deactivateQRCode();
            return true;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la désactivation du QR code: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Récupérer les statistiques des employeurs
     *
     * @param string|null $entrepriseId ID de l'entreprise (optionnel)
     * @return array
     */
    public function getStatistiquesEmployeurs(?string $entrepriseId = null): array
    {
        $query = Employeur::query();
        
        if ($entrepriseId) {
            $query->parEntreprise($entrepriseId);
        }
        
        $totalEmployeurs = $query->count();
        $employeursActifs = (clone $query)->actif()->count();
        $employeursInactifs = (clone $query)->inactif()->count();
        
        // Répartition par type de contrat
        $typeContrats = (clone $query)->select('type_contrat')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('type_contrat')
            ->pluck('total', 'type_contrat')
            ->toArray();
        
        // Répartition par département
        $departements = (clone $query)->select('departement_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('departement_id')
            ->pluck('total', 'departement_id')
            ->toArray();
        
        // Répartition par genre
        $genres = (clone $query)->select('genre')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('genre')
            ->pluck('total', 'genre')
            ->toArray();
        
        // Calcul de l'ancienneté moyenne
        $ancienneteAvg = (clone $query)->whereNotNull('date_embauche')
            ->get()
            ->avg(function($employeur) {
                return $employeur->getAnciennete() ?? 0;
            });
        
        return [
            'total' => $totalEmployeurs,
            'actifs' => $employeursActifs,
            'inactifs' => $employeursInactifs,
            'pourcentage_actifs' => $totalEmployeurs > 0 ? round(($employeursActifs / $totalEmployeurs) * 100, 2) : 0,
            'type_contrats' => $typeContrats,
            'departements' => $departements,
            'genres' => $genres,
            'anciennete_moyenne' => round($ancienneteAvg, 1)
        ];
    }
    
    /**
     * Récupérer les employeurs par département
     *
     * @param string $departementId ID du département
     * @return Collection
     */
    public function getEmployeursByDepartement(string $departementId): Collection
    {
        return Employeur::parDepartement($departementId)->get();
    }
    
    /**
     * Récupérer les employeurs par entreprise
     *
     * @param string $entrepriseId ID de l'entreprise
     * @return Collection
     */
    public function getEmployeursByEntreprise(string $entrepriseId): Collection
    {
        return Employeur::parEntreprise($entrepriseId)->get();
    }
    
    /**
     * Vérifier si un employeur a des dépendances qui bloquent sa suppression
     *
     * @param Employeur $employeur
     * @return bool
     */
    private function hasBlockingDependencies(Employeur $employeur): bool
    {
        return $employeur->presences()->count() > 0 ||
               $employeur->conges()->count() > 0 ||
               $employeur->supplementaires()->count() > 0 ||
               $employeur->permutations()->count() > 0;
    }
    
    /**
     * Gérer l'upload de photo
     *
     * @param mixed $photo
     * @return string Chemin de la photo
     */
    private function handlePhotoUpload($photo): string
    {
        $path = $photo->store('public/photos/employes');
        return $path;
    }
    
    /**
     * Créer un utilisateur pour un employeur
     *
     * @param Employeur $employeur
     * @param array $data
     * @return User
     */
    private function createUserForEmployeur(Employeur $employeur, array $data): User
    {
        $password = $data['user_password'] ?? Str::random(10);
        
        $user = User::create([
            'name' => $employeur->getNomComplet(),
            'email' => $employeur->email,
            'password' => Hash::make($password),
            'employeur_id' => $employeur->id,
            'entreprise_id' => $employeur->entreprise_id
        ]);
        
        // Assigner le rôle employé
        $user->assignRole('employe');
        
        // Envoyer un email avec les identifiants si nécessaire
        if (isset($data['send_credentials']) && $data['send_credentials']) {
            // Code pour envoyer un email (à implémenter)
        }
        
        return $user;
    }
    
    /**
     * Mettre à jour l'utilisateur d'un employeur
     *
     * @param Employeur $employeur
     * @param array $data
     * @return User
     */
    private function updateUserForEmployeur(Employeur $employeur, array $data): User
    {
        $user = $employeur->user;
        
        $userData = [
            'name' => $employeur->getNomComplet(),
            'email' => $employeur->email
        ];
        
        if (isset($data['user_password']) && !empty($data['user_password'])) {
            $userData['password'] = Hash::make($data['user_password']);
        }
        
        $user->update($userData);
        
        return $user;
    }
    
    /**
     * Obtenir une configuration par défaut
     *
     * @return array
     */
    private function getDefaultConfiguration(): array
    {
        return [
            'notifications' => [
                'email' => true,
                'sms' => false,
                'application' => true
            ],
            'preferences' => [
                'langue' => 'fr',
                'fuseau_horaire' => 'Europe/Paris'
            ],
            'pointage' => [
                'rappel_quotidien' => false,
                'validation_automatique' => true
            ]
        ];
    }
}
