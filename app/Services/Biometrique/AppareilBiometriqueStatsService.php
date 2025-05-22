<?php

namespace App\Services\Biometrique;

use App\Models\AppareilBiometrique;
use App\Models\LogAppareilBiometrique;
use App\Models\Presence;
use App\Models\User;
use App\Services\Biometrique\Protocols\ProtocolFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service pour les statistiques et fonctionnalités avancées des appareils biométriques
 */
class AppareilBiometriqueStatsService
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
     * Récupère les statistiques d'un appareil biométrique
     *
     * @param string $id
     * @return array
     */
    public function getAppareilStats(string $id): array
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        
        // Statistiques des logs
        $totalLogs = LogAppareilBiometrique::where('appareil_biometrique_id', $id)->count();
        $logsAujourdhui = LogAppareilBiometrique::where('appareil_biometrique_id', $id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Statistiques des pointages
        $totalPointages = Presence::where('appareil_biometrique_id', $id)->count();
        $pointagesAujourdhui = Presence::where('appareil_biometrique_id', $id)
            ->whereDate('date_heure', Carbon::today())
            ->count();
        
        // Statistiques des utilisateurs
        $totalUtilisateurs = $appareil->utilisateursEnregistres()->count();
        
        // Statistiques des erreurs
        $totalErreurs = LogAppareilBiometrique::where('appareil_biometrique_id', $id)
            ->where('niveau', 'error')
            ->count();
        
        // Dernière activité
        $derniereActivite = LogAppareilBiometrique::where('appareil_biometrique_id', $id)
            ->latest()
            ->first();
        
        return [
            'total_logs' => $totalLogs,
            'logs_aujourdhui' => $logsAujourdhui,
            'total_pointages' => $totalPointages,
            'pointages_aujourdhui' => $pointagesAujourdhui,
            'total_utilisateurs' => $totalUtilisateurs,
            'total_erreurs' => $totalErreurs,
            'derniere_activite' => $derniereActivite ? $derniereActivite->created_at : null,
            'derniere_activite_type' => $derniereActivite ? $derniereActivite->type : null,
            'uptime' => $this->calculateUptime($appareil),
            'statut_connexion' => $appareil->statut === 'actif' ? 'Connecté' : 'Déconnecté'
        ];
    }

    /**
     * Calcule le temps de fonctionnement de l'appareil
     *
     * @param AppareilBiometrique $appareil
     * @return string
     */
    protected function calculateUptime(AppareilBiometrique $appareil): string
    {
        if (!$appareil->dernier_sync) {
            return 'Inconnu';
        }
        
        $lastSync = Carbon::parse($appareil->dernier_sync);
        $now = Carbon::now();
        
        if ($appareil->statut !== 'actif') {
            return 'Hors ligne';
        }
        
        $diff = $lastSync->diffForHumans($now);
        return "En ligne depuis {$diff}";
    }

    /**
     * Récupère les informations détaillées d'un appareil
     *
     * @param string $id
     * @return array|null
     */
    public function getDeviceInfo(string $id): ?array
    {
        $appareil = AppareilBiometrique::findOrFail($id);
        
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            if (!$protocol->connect()) {
                return [
                    'status' => 'error',
                    'message' => 'Impossible de se connecter à l\'appareil',
                    'basic_info' => $this->getBasicDeviceInfo($appareil)
                ];
            }
            
            // Récupérer les informations de l'appareil
            $deviceInfo = $protocol->getDeviceInfo();
            
            if (!$deviceInfo) {
                return [
                    'status' => 'error',
                    'message' => 'Impossible de récupérer les informations de l\'appareil',
                    'basic_info' => $this->getBasicDeviceInfo($appareil)
                ];
            }
            
            // Mettre à jour les informations de base de l'appareil
            $this->updateDeviceInfo($appareil, $deviceInfo);
            
            // Ajouter les informations de base
            $deviceInfo = array_merge($deviceInfo, $this->getBasicDeviceInfo($appareil));
            
            // Ajouter le statut
            $deviceInfo['status'] = 'success';
            
            return $deviceInfo;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations de l\'appareil', [
                'appareil_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'info_appareil',
                [
                    'message' => 'Erreur lors de la récupération des informations',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return [
                'status' => 'error',
                'message' => 'Erreur: ' . $e->getMessage(),
                'basic_info' => $this->getBasicDeviceInfo($appareil)
            ];
        }
    }

    /**
     * Récupère les informations de base d'un appareil
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    protected function getBasicDeviceInfo(AppareilBiometrique $appareil): array
    {
        return [
            'id' => $appareil->id,
            'nom' => $appareil->nom,
            'fabricant' => $appareil->fabricant,
            'modele' => $appareil->modele,
            'adresse_ip' => $appareil->adresse_ip,
            'port' => $appareil->port,
            'protocole' => $appareil->protocole,
            'version_firmware' => $appareil->version_firmware,
            'statut' => $appareil->statut,
            'dernier_sync' => $appareil->dernier_sync ? Carbon::parse($appareil->dernier_sync)->format('d/m/Y H:i:s') : null,
            'site' => $appareil->site ? $appareil->site->nom : null
        ];
    }

    /**
     * Met à jour les informations d'un appareil
     *
     * @param AppareilBiometrique $appareil
     * @param array $deviceInfo
     * @return void
     */
    protected function updateDeviceInfo(AppareilBiometrique $appareil, array $deviceInfo): void
    {
        $appareil->version_firmware = $deviceInfo['firmware_version'] ?? $appareil->version_firmware;
        $appareil->capacite_empreintes = $deviceInfo['fingerprint_capacity'] ?? $appareil->capacite_empreintes;
        $appareil->capacite_visages = $deviceInfo['face_capacity'] ?? $appareil->capacite_visages;
        $appareil->capacite_cartes = $deviceInfo['card_capacity'] ?? $appareil->capacite_cartes;
        $appareil->capacite_logs = $deviceInfo['log_capacity'] ?? $appareil->capacite_logs;
        $appareil->dernier_sync = Carbon::now();
        $appareil->save();
    }

    /**
     * Synchronise les utilisateurs avec un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    public function synchroniserUtilisateurs(AppareilBiometrique $appareil): array
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            if (!$protocol->connect()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de se connecter à l\'appareil'
                ];
            }
            
            // Récupérer les utilisateurs de l'appareil
            $deviceUsers = $protocol->getAllUsers();
            
            if ($deviceUsers === null) {
                return [
                    'success' => false,
                    'message' => 'Impossible de récupérer les utilisateurs de l\'appareil'
                ];
            }
            
            // Récupérer les utilisateurs enregistrés dans la base de données
            $dbUsers = $appareil->utilisateursEnregistres()->get();
            
            // Identifier les utilisateurs à ajouter et à supprimer
            $deviceUserIds = array_column($deviceUsers, 'id');
            $dbUserIds = $dbUsers->pluck('pivot.identifiant_biometrique')->toArray();
            
            $toAdd = array_diff($dbUserIds, $deviceUserIds);
            $toRemove = array_diff($deviceUserIds, $dbUserIds);
            
            // Statistiques
            $stats = [
                'total_device' => count($deviceUsers),
                'total_db' => count($dbUsers),
                'to_add' => count($toAdd),
                'to_remove' => count($toRemove),
                'added' => 0,
                'removed' => 0,
                'errors' => 0
            ];
            
            // Ajouter les utilisateurs manquants sur l'appareil
            foreach ($toAdd as $userIdToAdd) {
                $dbUser = $dbUsers->first(function ($user) use ($userIdToAdd) {
                    return $user->pivot->identifiant_biometrique === $userIdToAdd;
                });
                
                if (!$dbUser) {
                    continue;
                }
                
                try {
                    $success = $protocol->registerUser(
                        $dbUser->pivot->identifiant_biometrique,
                        $dbUser->nom,
                        $dbUser->pivot->type_donnee ?? 'password',
                        []
                    );
                    
                    if ($success) {
                        $stats['added']++;
                        
                        $this->logService->creerLog(
                            $appareil,
                            $dbUser,
                            'sync_utilisateur',
                            [
                                'message' => 'Utilisateur ajouté à l\'appareil',
                                'identifiant' => $dbUser->pivot->identifiant_biometrique
                            ],
                            'success'
                        );
                    } else {
                        $stats['errors']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Erreur lors de l\'ajout d\'un utilisateur à l\'appareil', [
                        'appareil_id' => $appareil->id,
                        'user_id' => $dbUser->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Supprimer les utilisateurs en trop sur l'appareil
            foreach ($toRemove as $userIdToRemove) {
                try {
                    $success = $protocol->deleteUser($userIdToRemove);
                    
                    if ($success) {
                        $stats['removed']++;
                        
                        $this->logService->creerLog(
                            $appareil,
                            null,
                            'sync_utilisateur',
                            [
                                'message' => 'Utilisateur supprimé de l\'appareil',
                                'identifiant' => $userIdToRemove
                            ],
                            'success'
                        );
                    } else {
                        $stats['errors']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Erreur lors de la suppression d\'un utilisateur de l\'appareil', [
                        'appareil_id' => $appareil->id,
                        'user_id' => $userIdToRemove,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Mettre à jour la date de synchronisation
            $appareil->dernier_sync = Carbon::now();
            $appareil->save();
            
            return [
                'success' => true,
                'message' => 'Synchronisation des utilisateurs terminée',
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation des utilisateurs', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'sync_utilisateur',
                [
                    'message' => 'Erreur lors de la synchronisation des utilisateurs',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Synchronise les logs avec un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    public function synchroniserLogs(AppareilBiometrique $appareil): array
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            if (!$protocol->connect()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de se connecter à l\'appareil'
                ];
            }
            
            // Récupérer la date du dernier log synchronisé
            $lastLog = Presence::where('appareil_biometrique_id', $appareil->id)
                ->latest('date_heure')
                ->first();
            
            $fromDate = $lastLog ? Carbon::parse($lastLog->date_heure) : Carbon::now()->subDays(30);
            
            // Récupérer les logs de l'appareil
            $logs = $protocol->getAttendanceLogs($fromDate);
            
            if ($logs === null) {
                return [
                    'success' => false,
                    'message' => 'Impossible de récupérer les logs de l\'appareil'
                ];
            }
            
            // Traiter les logs
            $pointageService = app(PointageBiometriqueService::class);
            $stats = [
                'total' => count($logs),
                'processed' => 0,
                'created' => 0,
                'skipped' => 0,
                'errors' => 0
            ];
            
            foreach ($logs as $log) {
                $stats['processed']++;
                
                try {
                    $result = $pointageService->traiterPointageBiometrique($appareil, $log);
                    
                    if ($result) {
                        $stats['created']++;
                    } else {
                        $stats['skipped']++;
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error('Erreur lors du traitement d\'un log', [
                        'appareil_id' => $appareil->id,
                        'log' => $log,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Mettre à jour la date de synchronisation
            $appareil->dernier_sync = Carbon::now();
            $appareil->save();
            
            $this->logService->creerLog(
                $appareil,
                null,
                'sync_logs',
                [
                    'message' => 'Synchronisation des logs terminée',
                    'stats' => $stats
                ],
                'success'
            );
            
            return [
                'success' => true,
                'message' => 'Synchronisation des logs terminée',
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation des logs', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'sync_logs',
                [
                    'message' => 'Erreur lors de la synchronisation des logs',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Synchronise l'heure d'un appareil biométrique
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    public function synchroniserHeure(AppareilBiometrique $appareil): array
    {
        try {
            $protocol = $this->protocolFactory->createProtocol($appareil);
            
            if (!$protocol->connect()) {
                return [
                    'success' => false,
                    'message' => 'Impossible de se connecter à l\'appareil'
                ];
            }
            
            // Définir l'heure actuelle sur l'appareil
            $currentTime = Carbon::now();
            $success = $protocol->setDeviceTime($currentTime);
            
            if (!$success) {
                return [
                    'success' => false,
                    'message' => 'Impossible de définir l\'heure sur l\'appareil'
                ];
            }
            
            // Vérifier que l'heure a bien été définie
            $deviceTime = $protocol->getDeviceTime();
            
            if (!$deviceTime) {
                return [
                    'success' => false,
                    'message' => 'Impossible de vérifier l\'heure de l\'appareil'
                ];
            }
            
            $diff = $currentTime->diffInSeconds(Carbon::parse($deviceTime));
            
            $this->logService->creerLog(
                $appareil,
                null,
                'sync_heure',
                [
                    'message' => 'Synchronisation de l\'heure terminée',
                    'heure_serveur' => $currentTime->format('Y-m-d H:i:s'),
                    'heure_appareil' => $deviceTime,
                    'difference_secondes' => $diff
                ],
                'success'
            );
            
            // Mettre à jour la date de synchronisation
            $appareil->dernier_sync = Carbon::now();
            $appareil->save();
            
            return [
                'success' => true,
                'message' => 'Synchronisation de l\'heure terminée',
                'server_time' => $currentTime->format('Y-m-d H:i:s'),
                'device_time' => $deviceTime,
                'difference_seconds' => $diff
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation de l\'heure', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            $this->logService->creerLog(
                $appareil,
                null,
                'sync_heure',
                [
                    'message' => 'Erreur lors de la synchronisation de l\'heure',
                    'erreur' => $e->getMessage()
                ],
                'error'
            );
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }
}
