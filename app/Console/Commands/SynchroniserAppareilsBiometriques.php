<?php

namespace App\Console\Commands;

use App\Models\AppareilBiometrique;
use App\Services\Biometrique\AppareilBiometriqueStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SynchroniserAppareilsBiometriques extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'biometrique:sync 
                            {--type=all : Type de synchronisation (all, logs, users, time)}
                            {--id= : ID spécifique d\'un appareil à synchroniser}
                            {--site= : ID d\'un site pour synchroniser tous ses appareils}
                            {--statut=actif : Statut des appareils à synchroniser (actif, inactif, maintenance, erreur, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les appareils biométriques (logs, utilisateurs, heure)';

    /**
     * @var AppareilBiometriqueStatsService
     */
    protected $statsService;

    /**
     * Create a new command instance.
     *
     * @param AppareilBiometriqueStatsService $statsService
     * @return void
     */
    public function __construct(AppareilBiometriqueStatsService $statsService)
    {
        parent::__construct();
        $this->statsService = $statsService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->option('type');
        $appareilId = $this->option('id');
        $siteId = $this->option('site');
        $statut = $this->option('statut');

        $this->info('Démarrage de la synchronisation des appareils biométriques...');
        $this->info('Type: ' . $type);

        try {
            // Récupérer les appareils à synchroniser
            $appareils = $this->getAppareilsToSync($appareilId, $siteId, $statut);
            
            if ($appareils->isEmpty()) {
                $this->warn('Aucun appareil à synchroniser.');
                return 0;
            }

            $this->info('Nombre d\'appareils à synchroniser: ' . $appareils->count());
            
            $successCount = 0;
            $errorCount = 0;

            // Synchroniser chaque appareil
            foreach ($appareils as $appareil) {
                $this->info('Synchronisation de l\'appareil: ' . $appareil->nom . ' (' . $appareil->adresse_ip . ')');
                
                try {
                    $results = [];
                    
                    // Synchroniser selon le type demandé
                    if ($type === 'all' || $type === 'logs') {
                        $this->info('- Synchronisation des logs...');
                        $logResult = $this->statsService->synchroniserLogs($appareil);
                        $results['logs'] = $logResult;
                        $this->displaySyncResult('logs', $logResult);
                    }
                    
                    if ($type === 'all' || $type === 'users') {
                        $this->info('- Synchronisation des utilisateurs...');
                        $userResult = $this->statsService->synchroniserUtilisateurs($appareil);
                        $results['users'] = $userResult;
                        $this->displaySyncResult('users', $userResult);
                    }
                    
                    if ($type === 'all' || $type === 'time') {
                        $this->info('- Synchronisation de l\'heure...');
                        $timeResult = $this->statsService->synchroniserHeure($appareil);
                        $results['time'] = $timeResult;
                        $this->displaySyncResult('time', $timeResult);
                    }
                    
                    // Vérifier si toutes les synchronisations ont réussi
                    $allSuccess = collect($results)->every(function ($result) {
                        return $result['success'] ?? false;
                    });
                    
                    if ($allSuccess) {
                        $successCount++;
                        $this->info('Synchronisation réussie pour l\'appareil: ' . $appareil->nom);
                    } else {
                        $errorCount++;
                        $this->error('Échec de synchronisation pour l\'appareil: ' . $appareil->nom);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error('Erreur lors de la synchronisation de l\'appareil ' . $appareil->nom . ': ' . $e->getMessage());
                    Log::error('Erreur de synchronisation automatique', [
                        'appareil_id' => $appareil->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $this->line('-------------------------------------');
            }
            
            $this->info('Synchronisation terminée.');
            $this->info('Appareils synchronisés avec succès: ' . $successCount);
            $this->info('Appareils avec erreurs: ' . $errorCount);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur générale: ' . $e->getMessage());
            Log::error('Erreur générale de synchronisation automatique', [
                'error' => $e->getMessage()
            ]);
            
            return 1;
        }
    }

    /**
     * Récupère les appareils à synchroniser selon les critères
     *
     * @param string|null $appareilId
     * @param string|null $siteId
     * @param string $statut
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAppareilsToSync($appareilId = null, $siteId = null, $statut = 'actif')
    {
        $query = AppareilBiometrique::query();
        
        // Filtrer par ID d'appareil si spécifié
        if ($appareilId) {
            $query->where('id', $appareilId);
            return $query->get();
        }
        
        // Filtrer par site si spécifié
        if ($siteId) {
            $query->where('site_id', $siteId);
        }
        
        // Filtrer par statut
        if ($statut !== 'all') {
            $query->where('statut', $statut);
        }
        
        // Filtrer par configuration de synchronisation automatique
        $query->where('sync_auto_enabled', true);
        
        return $query->get();
    }

    /**
     * Affiche le résultat d'une synchronisation
     *
     * @param string $type
     * @param array $result
     * @return void
     */
    protected function displaySyncResult($type, $result)
    {
        if ($result['success'] ?? false) {
            $this->info('  ✓ ' . ucfirst($type) . ' synchronisés avec succès');
            
            if (isset($result['stats'])) {
                foreach ($result['stats'] as $key => $value) {
                    $this->line('    - ' . str_replace('_', ' ', $key) . ': ' . $value);
                }
            }
        } else {
            $this->error('  ✗ Échec de synchronisation des ' . $type);
            $this->error('    - Message: ' . ($result['message'] ?? 'Erreur inconnue'));
        }
    }
}
