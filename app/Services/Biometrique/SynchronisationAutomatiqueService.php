<?php

namespace App\Services\Biometrique;

use App\Models\AppareilBiometrique;
use App\Models\LogAppareilBiometrique;
use App\Services\Biometrique\AppareilBiometriqueStatsService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion des synchronisations automatiques des appareils biométriques
 */
class SynchronisationAutomatiqueService
{
    /**
     * @var AppareilBiometriqueStatsService
     */
    protected $statsService;
    
    /**
     * @var LogAppareilBiometriqueService
     */
    protected $logService;

    /**
     * Constructeur
     *
     * @param AppareilBiometriqueStatsService $statsService
     * @param LogAppareilBiometriqueService $logService
     */
    public function __construct(
        AppareilBiometriqueStatsService $statsService,
        LogAppareilBiometriqueService $logService
    ) {
        $this->statsService = $statsService;
        $this->logService = $logService;
    }

    /**
     * Synchronise tous les appareils configurés pour la synchronisation automatique
     *
     * @param string $type Type de synchronisation (logs, users, time, all)
     * @param int|null $siteId ID du site pour filtrer les appareils
     * @return array Résultats de la synchronisation
     */
    public function synchroniserTousLesAppareils(string $type = 'all', ?int $siteId = null): array
    {
        $query = AppareilBiometrique::where('sync_auto_enabled', true)
            ->where('statut', 'actif');
            
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        $appareils = $query->get();
        
        return $this->synchroniserAppareils($appareils, $type);
    }

    /**
     * Synchronise un appareil spécifique
     *
     * @param string $appareilId ID de l'appareil
     * @param string $type Type de synchronisation (logs, users, time, all)
     * @return array Résultats de la synchronisation
     */
    public function synchroniserAppareil(string $appareilId, string $type = 'all'): array
    {
        $appareil = AppareilBiometrique::findOrFail($appareilId);
        
        return $this->synchroniserAppareils(collect([$appareil]), $type);
    }

    /**
     * Synchronise une collection d'appareils
     *
     * @param Collection $appareils Collection d'appareils à synchroniser
     * @param string $type Type de synchronisation (logs, users, time, all)
     * @return array Résultats de la synchronisation
     */
    public function synchroniserAppareils(Collection $appareils, string $type = 'all'): array
    {
        $results = [
            'total' => $appareils->count(),
            'success' => 0,
            'errors' => 0,
            'details' => []
        ];
        
        foreach ($appareils as $appareil) {
            try {
                $appareilResult = [
                    'id' => $appareil->id,
                    'nom' => $appareil->nom,
                    'adresse_ip' => $appareil->adresse_ip,
                    'success' => true,
                    'operations' => []
                ];
                
                // Synchroniser les logs
                if ($type === 'all' || $type === 'logs') {
                    $syncResult = $this->synchroniserLogsAppareil($appareil);
                    $appareilResult['operations']['logs'] = $syncResult;
                    
                    if (!($syncResult['success'] ?? false)) {
                        $appareilResult['success'] = false;
                    }
                }
                
                // Synchroniser les utilisateurs
                if ($type === 'all' || $type === 'users') {
                    $syncResult = $this->synchroniserUtilisateursAppareil($appareil);
                    $appareilResult['operations']['users'] = $syncResult;
                    
                    if (!($syncResult['success'] ?? false)) {
                        $appareilResult['success'] = false;
                    }
                }
                
                // Synchroniser l'heure
                if ($type === 'all' || $type === 'time') {
                    $syncResult = $this->synchroniserHeureAppareil($appareil);
                    $appareilResult['operations']['time'] = $syncResult;
                    
                    if (!($syncResult['success'] ?? false)) {
                        $appareilResult['success'] = false;
                    }
                }
                
                // Mettre à jour les statistiques
                if ($appareilResult['success']) {
                    $results['success']++;
                    
                    // Mettre à jour la date de dernière synchronisation automatique
                    $appareil->derniere_sync_auto = Carbon::now();
                    $appareil->save();
                    
                    $this->logService->creerLog(
                        $appareil,
                        null,
                        'sync_auto',
                        [
                            'message' => 'Synchronisation automatique réussie',
                            'type' => $type
                        ],
                        'success'
                    );
                } else {
                    $results['errors']++;
                    
                    $this->logService->creerLog(
                        $appareil,
                        null,
                        'sync_auto',
                        [
                            'message' => 'Échec de synchronisation automatique',
                            'type' => $type,
                            'details' => $appareilResult['operations']
                        ],
                        'error'
                    );
                }
                
                $results['details'][] = $appareilResult;
            } catch (\Exception $e) {
                $results['errors']++;
                
                Log::error('Erreur lors de la synchronisation automatique', [
                    'appareil_id' => $appareil->id,
                    'error' => $e->getMessage()
                ]);
                
                $this->logService->creerLog(
                    $appareil,
                    null,
                    'sync_auto',
                    [
                        'message' => 'Erreur de synchronisation automatique',
                        'erreur' => $e->getMessage()
                    ],
                    'error'
                );
                
                $results['details'][] = [
                    'id' => $appareil->id,
                    'nom' => $appareil->nom,
                    'adresse_ip' => $appareil->adresse_ip,
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * Synchronise les logs d'un appareil
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    protected function synchroniserLogsAppareil(AppareilBiometrique $appareil): array
    {
        try {
            return $this->statsService->synchroniserLogs($appareil);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation automatique des logs', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Synchronise les utilisateurs d'un appareil
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    protected function synchroniserUtilisateursAppareil(AppareilBiometrique $appareil): array
    {
        try {
            return $this->statsService->synchroniserUtilisateurs($appareil);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation automatique des utilisateurs', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Synchronise l'heure d'un appareil
     *
     * @param AppareilBiometrique $appareil
     * @return array
     */
    protected function synchroniserHeureAppareil(AppareilBiometrique $appareil): array
    {
        try {
            return $this->statsService->synchroniserHeure($appareil);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la synchronisation automatique de l\'heure', [
                'appareil_id' => $appareil->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Vérifie si un appareil doit être synchronisé selon sa configuration
     *
     * @param AppareilBiometrique $appareil
     * @param string $type Type de synchronisation (logs, users, time)
     * @return bool
     */
    public function doitEtreSynchronise(AppareilBiometrique $appareil, string $type): bool
    {
        if (!$appareil->sync_auto_enabled) {
            return false;
        }
        
        if ($appareil->statut !== 'actif') {
            return false;
        }
        
        // Vérifier si la dernière synchronisation est assez ancienne
        if (!$appareil->derniere_sync_auto) {
            return true;
        }
        
        $lastSync = Carbon::parse($appareil->derniere_sync_auto);
        $now = Carbon::now();
        
        switch ($type) {
            case 'logs':
                $interval = $appareil->sync_logs_interval ?? 60; // Minutes
                return $lastSync->diffInMinutes($now) >= $interval;
                
            case 'users':
                $interval = $appareil->sync_users_interval ?? 1440; // Minutes (24h)
                return $lastSync->diffInMinutes($now) >= $interval;
                
            case 'time':
                $interval = $appareil->sync_time_interval ?? 1440; // Minutes (24h)
                return $lastSync->diffInMinutes($now) >= $interval;
                
            default:
                return true;
        }
    }
}
