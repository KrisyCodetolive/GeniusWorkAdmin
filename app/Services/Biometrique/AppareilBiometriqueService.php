<?php

namespace App\Services\Biometrique;

use App\Models\AppareilBiometrique;
use App\Models\LogAppareilBiometrique;
use App\Models\User;
use App\Models\Site;
use App\Services\Biometrique\Protocols\ProtocolFactory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service principal pour la gestion des appareils biométriques
 */
class AppareilBiometriqueService
{
    /**
     * @var ProtocolFactory
     */
    protected $protocolFactory;
    
    /**
     * @var LogAppareilBiometriqueService
     */
    protected $logService;

    /**
     * Constructeur
     *
     * @param ProtocolFactory $protocolFactory
     * @param LogAppareilBiometriqueService $logService
     */
    public function __construct(
        ProtocolFactory $protocolFactory,
        LogAppareilBiometriqueService $logService
    ) {
        $this->protocolFactory = $protocolFactory;
        $this->logService = $logService;
    }

    /**
     * Récupère tous les appareils biométriques actifs
     *
     * @param int|null $entrepriseId
     * @param int|null $siteId
     * @return Collection
     */
    public function getAppareilsActifs(?int $entrepriseId = null, ?int $siteId = null): Collection
    {
        $query = AppareilBiometrique::actif();
        
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        }
        
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        return $query->get();
    }

    /**
     * Récupère un appareil biométrique par son ID
     *
     * @param string $id
     * @return AppareilBiometrique|null
     */
    public function getAppareilById(string $id): ?AppareilBiometrique
    {
        return AppareilBiometrique::find($id);
    }

    /**
     * Crée un nouvel appareil biométrique
     *
     * @param array $data
     * @return AppareilBiometrique
     */
    public function creerAppareil(array $data): AppareilBiometrique
    {
        $appareil = new AppareilBiometrique($data);
        $appareil->save();
        
        $this->logService->creerLog(
            $appareil,
            null,
            'creation',
            ['message' => 'Appareil biométrique créé'],
            'success'
        );
        
        return $appareil;
    }

    /**
     * Met à jour un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param array $data
     * @return AppareilBiometrique
     */
    public function mettreAJourAppareil(AppareilBiometrique $appareil, array $data): AppareilBiometrique
    {
        $appareil->fill($data);
        $appareil->save();
        
        $this->logService->creerLog(
            $appareil,
            null,
            'modification',
            ['message' => 'Appareil biométrique mis à jour'],
            'success'
        );
        
        return $appareil;
    }

    /**
     * Supprime un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return bool
     */
    public function supprimerAppareil(AppareilBiometrique $appareil): bool
    {
        $this->logService->creerLog(
            $appareil,
            null,
            'suppression',
            ['message' => 'Appareil biométrique supprimé'],
            'info'
        );
        
        return $appareil->delete();
    }

    /**
     * Teste la connexion avec un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return bool
     */
    public function testerConnexion(AppareilBiometrique $appareil): bool
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            // Vérifier si le protocole a été créé correctement
            if ($protocol === null) {
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'erreur',
                    ['message' => 'Impossible de créer le protocole pour cet appareil'],
                    'error'
                );
                return false;
            }
            
            $connected = $protocol->connect();
            
            if ($connected) {
                $appareil->dernier_sync = Carbon::now();
                $appareil->statut = 'actif';
                $appareil->save();
                
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'connexion',
                    ['message' => 'Connexion réussie'],
                    'success'
                );
            } else {
                $appareil->statut = 'erreur';
                $appareil->save();
                
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'connexion',
                    ['message' => 'Échec de connexion'],
                    'error'
                );
            }
            
            return $connected;
        } catch (\Exception $e) {
            Log::error('Erreur de connexion à l\'appareil biométrique', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $appareil->statut = 'erreur';
            $appareil->save();
            
            $this->logService->creerLog(
                $appareil,
                null,
                'connexion',
                [
                    'message' => 'Erreur de connexion',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return false;
        }
    }

    /**
     * Synchronise les données d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    public function synchroniserDonnees(AppareilBiometrique $appareil): array
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            // Vérifier si le protocole a été créé correctement
            if ($protocol === null) {
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'erreur',
                    ['message' => 'Impossible de créer le protocole pour cet appareil'],
                    'error'
                );
                return [
                    'success' => false,
                    'message' => 'Protocole non supporté pour cet appareil'
                ];
            }
            
            if (!$protocol->connect()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de se connecter à l\'appareil'
                ];
            }
            
            // Récupérer les logs de présence
            $logs = $protocol->getAttendanceLogs();
            
            // Traiter les logs
            $processed = $this->traiterLogsPresence($appareil, $logs);
            
            // Mettre à jour l'appareil
            $appareil->dernier_sync = Carbon::now();
            $appareil->statut = 'actif';
            $appareil->save();
            
            $this->logService->creerLog(
                $appareil,
                null,
                'synchronisation',
                [
                    'message' => 'Synchronisation réussie',
                    'logs_traites' => count($logs),
                    'pointages_crees' => $processed['created']
                ],
                'success'
            );
            
            return [
                'success' => true,
                'message' => 'Synchronisation réussie',
                'logs_count' => count($logs),
                'processed' => $processed
            ];
        } catch (\Exception $e) {
            Log::error('Erreur de synchronisation avec l\'appareil biométrique', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'synchronisation',
                [
                    'message' => 'Erreur de synchronisation',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return [
                'success' => false,
                'message' => 'Erreur de synchronisation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Traite les logs de présence récupérés de l'appareil
     *
     * @param AppareilBiometrique $appareil
     * @param array $logs
     * @return array
     */
    protected function traiterLogsPresence(AppareilBiometrique $appareil, array $logs): array
    {
        $result = [
            'total' => count($logs),
            'created' => 0,
            'skipped' => 0,
            'errors' => 0
        ];
        
        $pointageService = app(PointageBiometriqueService::class);
        
        foreach ($logs as $log) {
            try {
                $processed = $pointageService->traiterPointageBiometrique($appareil, $log);
                
                if ($processed) {
                    $result['created']++;
                } else {
                    $result['skipped']++;
                }
            } catch (\Exception $e) {
                Log::error('Erreur de traitement du log de présence', [
                    'appareil_id' => $appareil->id,
                    'log' => $log,
                    'error' => $e->getMessage()
                ]);
                
                $result['errors']++;
            }
        }
        
        return $result;
    }

    /**
     * Enregistre un utilisateur sur un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @param string $identifiantBiometrique
     * @param string $typeDonnee
     * @return bool
     */
    public function enregistrerUtilisateur(AppareilBiometrique $appareil, User $user, string $identifiantBiometrique, string $typeDonnee): bool
    {
        try {
            if (!$appareil->estActif()) {
                throw new \Exception("L'appareil n'est pas actif");
            }
            
            // Vérifier si l'utilisateur est déjà enregistré
            $dejaEnregistre = $appareil->utilisateursEnregistres()
                ->where('user_id', $user->id)
                ->exists();
                
            if ($dejaEnregistre) {
                throw new \Exception("L'utilisateur est déjà enregistré sur cet appareil");
            }
            
            // Obtenir le client pour l'appareil
            $client = $this->getClient($appareil);
            
            // Enregistrer l'utilisateur sur l'appareil
            $success = $client->registerUser(
                $user->id,
                $user->name,
                $identifiantBiometrique,
                $typeDonnee
            );
            
            if ($success) {
                // Créer un log de succès
                $this->logService->creerLog(
                    $appareil,
                    $user,
                    'enregistrement_utilisateur',
                    [
                        'message' => 'Utilisateur enregistré avec succès',
                        'identifiant_biometrique' => $identifiantBiometrique,
                        'type_donnee' => $typeDonnee
                    ],
                    'success'
                );
                
                return true;
            } else {
                throw new \Exception("Échec de l'enregistrement sur l'appareil");
            }
        } catch (\Exception $e) {
            // Créer un log d'erreur
            $this->logService->creerLog(
                $appareil,
                $user,
                'enregistrement_utilisateur',
                [
                    'message' => "Échec de l'enregistrement",
                    'erreur' => $e->getMessage(),
                    'identifiant_biometrique' => $identifiantBiometrique,
                    'type_donnee' => $typeDonnee
                ],
                'error'
            );
            
            throw $e;
        }
    }
    
    /**
     * Met à jour les informations d'un utilisateur sur un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @param string $identifiantBiometrique
     * @param string $typeDonnee
     * @param string $statut
     * @return bool
     */
    public function mettreAJourUtilisateur(AppareilBiometrique $appareil, User $user, string $identifiantBiometrique, string $typeDonnee, string $statut = 'actif'): bool
    {
        try {
            if (!$appareil->estActif()) {
                throw new \Exception("L'appareil n'est pas actif");
            }
            
            // Obtenir le client pour l'appareil
            $client = $this->getClient($appareil);
            
            // Mettre à jour l'utilisateur sur l'appareil
            $success = $client->updateUser(
                $user->id,
                $user->name,
                $identifiantBiometrique,
                $typeDonnee,
                $statut === 'actif'
            );
            
            if ($success) {
                // Créer un log de succès
                $this->logService->creerLog(
                    $appareil,
                    $user,
                    'mise_a_jour_utilisateur',
                    [
                        'message' => 'Utilisateur mis à jour avec succès',
                        'identifiant_biometrique' => $identifiantBiometrique,
                        'type_donnee' => $typeDonnee,
                        'statut' => $statut
                    ],
                    'success'
                );
                
                return true;
            } else {
                throw new \Exception("Échec de la mise à jour sur l'appareil");
            }
        } catch (\Exception $e) {
            // Créer un log d'erreur
            $this->logService->creerLog(
                $appareil,
                $user,
                'mise_a_jour_utilisateur',
                [
                    'message' => "Échec de la mise à jour",
                    'erreur' => $e->getMessage(),
                    'identifiant_biometrique' => $identifiantBiometrique,
                    'type_donnee' => $typeDonnee,
                    'statut' => $statut
                ],
                'error'
            );
            
            throw $e;
        }
    }
    
    /**
     * Met à jour le statut d'un utilisateur sur un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @param string $statut
     * @return bool
     */
    public function mettreAJourStatutUtilisateur(AppareilBiometrique $appareil, User $user, string $statut): bool
    {
        try {
            if (!$appareil->estActif()) {
                throw new \Exception("L'appareil n'est pas actif");
            }
            
            // Récupérer les informations de l'utilisateur depuis la table pivot
            $pivot = $appareil->utilisateursEnregistres()
                ->where('user_id', $user->id)
                ->first()
                ->pivot;
                
            if (!$pivot) {
                throw new \Exception("L'utilisateur n'est pas enregistré sur cet appareil");
            }
            
            // Obtenir le client pour l'appareil
            $client = $this->getClient($appareil);
            
            // Mettre à jour le statut de l'utilisateur sur l'appareil
            $success = $client->updateUserStatus(
                $user->id,
                $pivot->identifiant_biometrique,
                $statut === 'actif'
            );
            
            if ($success) {
                // Créer un log de succès
                $this->logService->creerLog(
                    $appareil,
                    $user,
                    'mise_a_jour_statut_utilisateur',
                    [
                        'message' => 'Statut utilisateur mis à jour avec succès',
                        'statut' => $statut
                    ],
                    'success'
                );
                
                return true;
            } else {
                throw new \Exception("Échec de la mise à jour du statut sur l'appareil");
            }
        } catch (\Exception $e) {
            // Créer un log d'erreur
            $this->logService->creerLog(
                $appareil,
                $user,
                'mise_a_jour_statut_utilisateur',
                [
                    'message' => "Échec de la mise à jour du statut",
                    'erreur' => $e->getMessage(),
                    'statut' => $statut
                ],
                'error'
            );
            
            throw $e;
        }
    }
    
    /**
     * Supprime un utilisateur d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @param User $user
     * @return bool
     */
    public function supprimerUtilisateur(AppareilBiometrique $appareil, User $user): bool
    {
        try {
            if (!$appareil->estActif()) {
                throw new \Exception("L'appareil n'est pas actif");
            }
            
            // Récupérer les informations de l'utilisateur depuis la table pivot
            $pivot = $appareil->utilisateursEnregistres()
                ->where('user_id', $user->id)
                ->first()
                ->pivot;
                
            if (!$pivot) {
                throw new \Exception("L'utilisateur n'est pas enregistré sur cet appareil");
            }
            
            // Obtenir le client pour l'appareil
            $client = $this->getClient($appareil);
            
            // Supprimer l'utilisateur de l'appareil
            $success = $client->deleteUser(
                $user->id,
                $pivot->identifiant_biometrique
            );
            
            if ($success) {
                // Créer un log de succès
                $this->logService->creerLog(
                    $appareil,
                    $user,
                    'suppression_utilisateur',
                    [
                        'message' => 'Utilisateur supprimé avec succès',
                        'identifiant_biometrique' => $pivot->identifiant_biometrique
                    ],
                    'success'
                );
                
                return true;
            } else {
                throw new \Exception("Échec de la suppression sur l'appareil");
            }
        } catch (\Exception $e) {
            // Créer un log d'erreur
            $this->logService->creerLog(
                $appareil,
                $user,
                'suppression_utilisateur',
                [
                    'message' => "Échec de la suppression",
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            throw $e;
        }
    }
    
    /**
     * Synchronise les utilisateurs avec un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return bool
     */
    public function synchroniserUtilisateurs(AppareilBiometrique $appareil): bool
    {
        try {
            if (!$appareil->estActif()) {
                throw new \Exception("L'appareil n'est pas actif");
            }
            
            // Obtenir le client pour l'appareil
            $client = $this->getClient($appareil);
            
            // Récupérer la liste des utilisateurs enregistrés sur l'appareil
            $utilisateursAppareil = $client->getRegisteredUsers();
            
            // Récupérer la liste des utilisateurs enregistrés dans la base de données
            $utilisateursDB = $appareil->utilisateursEnregistres()->get();
            
            // Synchroniser les utilisateurs
            $success = $client->syncUsers($utilisateursDB);
            
            if ($success) {
                // Créer un log de succès
                $this->logService->creerLog(
                    $appareil,
                    auth()->user(),
                    'synchronisation_utilisateurs',
                    [
                        'message' => 'Utilisateurs synchronisés avec succès',
                        'nombre_utilisateurs' => $utilisateursDB->count()
                    ],
                    'success'
                );
                
                return true;
            } else {
                throw new \Exception("Échec de la synchronisation des utilisateurs");
            }
        } catch (\Exception $e) {
            // Créer un log d'erreur
            $this->logService->creerLog(
                $appareil,
                auth()->user(),
                'synchronisation_utilisateurs',
                [
                    'message' => "Échec de la synchronisation des utilisateurs",
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            throw $e;
        }
    }

    /**
     * Génère un identifiant biométrique unique pour un utilisateur sur un appareil
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
     * Obtient les informations système de l'appareil
     *
     * @param AppareilBiometrique $appareil
     * @return array|null
     */
    public function getInformationsSysteme(AppareilBiometrique $appareil): ?array
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            // Vérifier si le protocole a été créé correctement
            if ($protocol === null) {
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'info_systeme',
                    ['message' => 'Impossible de créer le protocole pour cet appareil'],
                    'error'
                );
                return null;
            }
            
            if (!$protocol->connect()) {
                return null;
            }
            
            $info = $protocol->getDeviceInfo();
            
            if ($info) {
                // Mettre à jour les informations de l'appareil
                $appareil->version_firmware = $info['firmware_version'] ?? $appareil->version_firmware;
                $appareil->capacite_empreintes = $info['fingerprint_capacity'] ?? $appareil->capacite_empreintes;
                $appareil->capacite_visages = $info['face_capacity'] ?? $appareil->capacite_visages;
                $appareil->capacite_cartes = $info['card_capacity'] ?? $appareil->capacite_cartes;
                $appareil->capacite_logs = $info['log_capacity'] ?? $appareil->capacite_logs;
                $appareil->dernier_sync = Carbon::now();
                $appareil->save();
                
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'info_systeme',
                    [
                        'message' => 'Informations système récupérées',
                        'info' => $info
                    ],
                    'info'
                );
            }
            
            return $info;
        } catch (\Exception $e) {
            Log::error('Erreur de récupération des informations système', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'info_systeme',
                [
                    'message' => 'Erreur de récupération des informations système',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return null;
        }
    }

    /**
     * Redémarre un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return bool
     */
    public function redemarrerAppareil(AppareilBiometrique $appareil): bool
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            // Vérifier si le protocole a été créé correctement
            if ($protocol === null) {
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'redemarrage',
                    ['message' => 'Impossible de créer le protocole pour cet appareil'],
                    'error'
                );
                return false;
            }
            
            if (!$protocol->connect()) {
                return false;
            }
            
            $success = $protocol->rebootDevice();
            
            if ($success) {
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'redemarrage',
                    [
                        'message' => 'Appareil redémarré avec succès'
                    ],
                    'success'
                );
            } else {
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'redemarrage',
                    [
                        'message' => 'Échec du redémarrage de l\'appareil'
                    ],
                    'error'
                );
            }
            
            return $success;
        } catch (\Exception $e) {
            Log::error('Erreur de redémarrage de l\'appareil biométrique', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'redemarrage',
                [
                    'message' => 'Erreur de redémarrage',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return false;
        }
    }
}
