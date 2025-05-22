<?php

namespace App\Services\Biometrique;

use App\Models\AppareilBiometrique;
use App\Models\Presence;
use App\Models\User;
use App\Models\MethodePointage;
use App\Models\Site;
use App\Services\PointageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion des pointages biométriques
 */
class PointageBiometriqueService
{
    /**
     * @var PointageService
     */
    protected $pointageService;
    
    /**
     * @var LogAppareilBiometriqueService
     */
    protected $logService;

    /**
     * Constructeur
     *
     * @param PointageService $pointageService
     * @param LogAppareilBiometriqueService $logService
     */
    public function __construct(
        PointageService $pointageService,
        LogAppareilBiometriqueService $logService
    ) {
        $this->pointageService = $pointageService;
        $this->logService = $logService;
    }

    /**
     * Traite un pointage biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param array $logData
     * @return bool
     */
    public function traiterPointageBiometrique(AppareilBiometrique $appareil, array $logData): bool
    {
        try {
            // Vérifier si le log a déjà été traité
            if ($this->estDejaTraite($appareil, $logData)) {
                return false;
            }
            
            // Récupérer l'utilisateur correspondant à l'identifiant biométrique
            $user = $this->trouverUtilisateur($appareil, $logData['user_id']);
            
            if (!$user) {
                Log::warning('Utilisateur non trouvé pour le pointage biométrique', [
                    'appareil_id' => $appareil->id,
                    'identifiant_biometrique' => $logData['user_id']
                ]);
                
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'pointage_erreur',
                    [
                        'message' => 'Utilisateur non trouvé',
                        'identifiant_biometrique' => $logData['user_id']
                    ],
                    'warning',
                    $logData
                );
                
                return false;
            }
            
            // Déterminer le type de pointage (entrée ou sortie)
            $typePointage = $this->determinerTypePointage($user, $logData);
            
            // Créer la présence
            $presence = $this->creerPresence($appareil, $user, $typePointage, $logData);
            
            if ($presence) {
                $this->logService->creerLog(
                    $appareil,
                    $user,
                    'pointage_success',
                    [
                        'message' => 'Pointage enregistré',
                        'type' => $typePointage,
                        'presence_id' => $presence->id
                    ],
                    'success',
                    $logData
                );
                
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error('Erreur de traitement du pointage biométrique', [
                'appareil_id' => $appareil->id,
                'log_data' => $logData,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'pointage_erreur',
                [
                    'message' => 'Erreur de traitement',
                    'erreur' => $e->getMessage()
                ],
                'error',
                $logData
            );
            
            return false;
        }
    }

    /**
     * Vérifie si un log a déjà été traité
     *
     * @param AppareilBiometrique $appareil
     * @param array $logData
     * @return bool
     */
    protected function estDejaTraite(AppareilBiometrique $appareil, array $logData): bool
    {
        // Vérifier si un log avec les mêmes données brutes existe déjà
        $logExistant = $appareil->logs()
            ->where('type_evenement', 'pointage_success')
            ->where('date_evenement', '>=', Carbon::parse($logData['datetime'])->subMinutes(5))
            ->where('date_evenement', '<=', Carbon::parse($logData['datetime'])->addMinutes(5))
            ->whereJsonContains('donnees_brutes->user_id', $logData['user_id'])
            ->first();
            
        return $logExistant !== null;
    }

    /**
     * Trouve l'utilisateur correspondant à un identifiant biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param string $identifiantBiometrique
     * @return User|null
     */
    protected function trouverUtilisateur(AppareilBiometrique $appareil, string $identifiantBiometrique): ?User
    {
        // Rechercher l'utilisateur dans la table pivot
        $pivot = $appareil->utilisateursEnregistres()
            ->wherePivot('identifiant_biometrique', $identifiantBiometrique)
            ->wherePivot('statut', 'actif')
            ->first();
            
        if ($pivot) {
            return $pivot;
        }
        
        // Si non trouvé, essayer de déduire l'ID utilisateur à partir de l'identifiant biométrique
        // Format attendu: SITE_ID (3 digits) + USER_ID (6 digits)
        if (strlen($identifiantBiometrique) >= 6) {
            $userId = ltrim(substr($identifiantBiometrique, -6), '0');
            
            if ($userId) {
                return User::find($userId);
            }
        }
        
        return null;
    }

    /**
     * Détermine le type de pointage (entrée ou sortie)
     *
     * @param User $user
     * @param array $logData
     * @return string
     */
    protected function determinerTypePointage(User $user, array $logData): string
    {
        // Si le type est explicitement spécifié dans les données du log
        if (isset($logData['type']) && in_array($logData['type'], ['entree', 'sortie', 'pause_debut', 'pause_fin'])) {
            return $logData['type'];
        }
        
        // Sinon, déterminer automatiquement en fonction du dernier pointage
        $dernierPointage = Presence::where('user_id', $user->id)
            ->orderBy('date_heure', 'desc')
            ->first();
            
        if (!$dernierPointage) {
            return 'entree';
        }
        
        // Si le dernier pointage était une entrée ou une fin de pause, le prochain est une sortie ou un début de pause
        if (in_array($dernierPointage->type, ['entree', 'pause_fin'])) {
            // Si le pointage est dans les 4 heures, considérer comme pause_debut
            if ($dernierPointage->date_heure->diffInHours(Carbon::parse($logData['datetime'])) < 4) {
                return 'pause_debut';
            }
            
            return 'sortie';
        }
        
        // Si le dernier pointage était une sortie, le prochain est une entrée
        if ($dernierPointage->type === 'sortie') {
            return 'entree';
        }
        
        // Si le dernier pointage était un début de pause, le prochain est une fin de pause
        if ($dernierPointage->type === 'pause_debut') {
            return 'pause_fin';
        }
        
        // Par défaut, entrée
        return 'entree';
    }

    /**
     * Crée une présence à partir d'un pointage biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @param string $typePointage
     * @param array $logData
     * @return Presence|null
     */
    protected function creerPresence(
        AppareilBiometrique $appareil,
        User $user,
        string $typePointage,
        array $logData
    ): ?Presence {
        $dateHeure = Carbon::parse($logData['datetime']);
        
        $data = [
            'employeur_id' => $user->employeur_id,
            'site_id' => $appareil->site_id,
            'type' => $typePointage,
            'date_heure' => $dateHeure,
            'source' => 'biometrique',
            'source_id' => $appareil->id,
            'appareil' => $appareil->modele . ' (' . $appareil->nom . ')',
            'statut' => 'enregistre',
            'commentaire' => 'Pointage biométrique automatique'
        ];
        
        // Récupérer la méthode de pointage biométrique
        $methodePointage = \App\Models\MethodePointage::where('code', 'biometrique')
            ->where('entreprise_id', $user->employeur_id)
            ->first();
            
        if (!$methodePointage) {
            // Créer la méthode de pointage biométrique si elle n'existe pas
            $methodePointage = new \App\Models\MethodePointage([
                'entreprise_id' => $user->employeur_id,
                'nom' => 'Pointage Biométrique',
                'code' => 'biometrique',
                'description' => 'Pointage via appareil biométrique',
                'necessite_photo' => false,
                'necessite_geolocalisation' => false,
                'necessite_signature' => false,
                'necessite_validation' => false,
                'autoriser_hors_site' => true,
                'statut' => 'actif'
            ]);
            $methodePointage->save();
        }
        
        try {
            // Enregistrer la présence
            return $this->pointageService->enregistrerPresence($data, $user, $methodePointage);
        } catch (\Exception $e) {
            Log::error('Erreur de création de présence', [
                'user_id' => $user->id,
                'appareil_id' => $appareil->id,
                'type' => $typePointage,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Effectue un pointage manuel pour un utilisateur
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @param string $typePointage
     * @param Carbon|null $dateHeure
     * @return Presence|null
     */
    public function effectuerPointageManuel(
        AppareilBiometrique $appareil,
        User $user,
        string $typePointage,
        ?Carbon $dateHeure = null
    ): ?Presence {
        if (!$dateHeure) {
            $dateHeure = Carbon::now();
        }
        
        $logData = [
            'user_id' => $this->genererIdentifiantBiometrique($user, $appareil),
            'datetime' => $dateHeure->toDateTimeString(),
            'type' => $typePointage,
            'manual' => true
        ];
        
        try {
            $presence = $this->creerPresence($appareil, $user, $typePointage, $logData);
            
            if ($presence) {
                $this->logService->creerLog(
                    $appareil,
                    $user,
                    'pointage_manuel',
                    [
                        'message' => 'Pointage manuel enregistré',
                        'type' => $typePointage,
                        'presence_id' => $presence->id
                    ],
                    'success',
                    $logData
                );
            }
            
            return $presence;
        } catch (\Exception $e) {
            Log::error('Erreur de pointage manuel', [
                'user_id' => $user->id,
                'appareil_id' => $appareil->id,
                'type' => $typePointage,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                $user,
                'pointage_manuel_erreur',
                [
                    'message' => 'Erreur de pointage manuel',
                    'type' => $typePointage,
                    'erreur' => $e->getMessage()
                ],
                'error',
                $logData
            );
            
            return null;
        }
    }

    /**
     * Génère un identifiant biométrique pour un utilisateur
     *
     * @param User $user
     * @param AppareilBiometrique $appareil
     * @return string
     */
    protected function genererIdentifiantBiometrique(User $user, AppareilBiometrique $appareil): string
    {
        // Format: SITE_ID + USER_ID (padded)
        $sitePrefix = $appareil->site_id ? str_pad($appareil->site_id, 3, '0', STR_PAD_LEFT) : '000';
        $userSuffix = str_pad($user->id, 6, '0', STR_PAD_LEFT);
        
        return $sitePrefix . $userSuffix;
    }

    /**
     * Synchronise les pointages depuis un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return int Nombre de pointages synchronisés
     */
    public function synchroniserPointages(AppareilBiometrique $appareil): int
    {
        try {
            // Vérifier si l'appareil est actif
            if (!$appareil->estActif()) {
                throw new \Exception("L'appareil n'est pas actif");
            }
            
            // Récupérer les logs de pointage depuis l'appareil
            $client = app(AppareilBiometriqueService::class)->getClient($appareil);
            $dateDebut = $appareil->dernier_sync ? Carbon::parse($appareil->dernier_sync) : Carbon::now()->subDays(7);
            
            $logs = $client->getAttendanceLogs($dateDebut->toDateTimeString());
            
            // Traiter chaque log
            $count = 0;
            foreach ($logs as $log) {
                if ($this->traiterPointageBiometrique($appareil, $log)) {
                    $count++;
                }
            }
            
            // Mettre à jour la date de dernière synchronisation
            $appareil->update([
                'dernier_sync' => Carbon::now(),
                'statut' => 'actif'
            ]);
            
            $this->logService->creerLog(
                $appareil,
                auth()->user(),
                'synchronisation',
                [
                    'message' => "{$count} pointages synchronisés",
                    'date_debut' => $dateDebut->toDateTimeString(),
                    'date_fin' => Carbon::now()->toDateTimeString()
                ],
                'success'
            );
            
            return $count;
        } catch (\Exception $e) {
            // Mettre à jour le statut de l'appareil en cas d'erreur
            $appareil->update([
                'statut' => 'erreur'
            ]);
            
            $this->logService->creerLog(
                $appareil,
                auth()->user(),
                'synchronisation',
                [
                    'message' => "Échec de la synchronisation",
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            throw $e;
        }
    }
    
    /**
     * Crée un pointage manuel
     *
     * @param array $data
     * @return Presence
     */
    public function creerPointageManuel(array $data): Presence
    {
        // Récupérer l'utilisateur si nécessaire
        $user = User::findOrFail($data['employeur_id']);
        
        // Récupérer la méthode de pointage
        $methodePointage = MethodePointage::find($data['methode_pointage_id'] ?? null) ?? 
            MethodePointage::where('code', 'MANUEL')->first();
        
        // Préparer les données pour enregistrerPresence
        $presenceData = [
            'employeur_id' => $data['employeur_id'],
            'site_id' => $data['site_id'],
            'type' => $data['type'],
            'date_heure' => $data['date_heure'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'precision_geo' => $data['precision_geo'] ?? null,
            'distance_site' => $data['distance_site'] ?? null,
            'commentaire' => $data['commentaire'] ?? null,
            'source' => 'biometrique',
            'appareil_id' => $data['appareil_id'] ?? null,
            'entreprise_id' => $data['entreprise_id']
        ];
        
        // Créer le pointage via le service de pointage
        $presence = $this->pointageService->enregistrerPresence($presenceData, $user, $methodePointage);
        
        // Mettre à jour le statut si spécifié
        if (isset($data['statut']) && in_array($data['statut'], ['valide', 'en_attente', 'rejete'])) {
            $presence->update(['statut' => $data['statut']]);
        }
        
        return $presence;
    }
    
    /**
     * Valide un pointage
     *
     * @param Presence $presence
     * @return bool
     */
    public function validerPointage(Presence $presence): bool
    {
        return $this->pointageService->validerPointage(
            $presence,
            auth()->user(),
            'Validation manuelle depuis l\'interface d\'administration'
        );
    }
    
    /**
     * Rejette un pointage
     *
     * @param Presence $presence
     * @param string $commentaire
     * @return bool
     */
    public function rejeterPointage(Presence $presence, string $commentaire): bool
    {
        return $this->pointageService->rejeterPointage(
            $presence,
            auth()->user(),
            $commentaire
        );
    }
}
