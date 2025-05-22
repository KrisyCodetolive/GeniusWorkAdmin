<?php

namespace App\Console\Commands;

use App\Services\FacturationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenererFacturesAutomatiques extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturation:generer-factures {--notifier : Envoyer une notification par email aux entreprises}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère automatiquement les factures mensuelles pour tous les abonnements actifs';

    /**
     * Le service de facturation.
     *
     * @var FacturationService
     */
    protected $facturationService;

    /**
     * Create a new command instance.
     *
     * @param FacturationService $facturationService
     * @return void
     */
    public function __construct(FacturationService $facturationService)
    {
        parent::__construct();
        $this->facturationService = $facturationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Début de la génération automatique des factures...');
        
        try {
            $facturesGenerees = $this->facturationService->genererFacturesAutomatiques();
            $count = count($facturesGenerees);
            
            $this->info("{$count} factures ont été générées avec succès.");
            
            // Envoyer des notifications par email si demandé
            if ($this->option('notifier') && $count > 0) {
                $this->info('Envoi des notifications par email...');
                
                $notificationsEnvoyees = 0;
                
                foreach ($facturesGenerees as $facture) {
                    if ($this->facturationService->envoyerFactureParEmail($facture)) {
                        $notificationsEnvoyees++;
                    }
                }
                
                $this->info("{$notificationsEnvoyees} notifications ont été envoyées avec succès.");
            }
            
            Log::info("Génération automatique des factures: {$count} factures générées.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Une erreur est survenue lors de la génération des factures: ' . $e->getMessage());
            Log::error('Erreur lors de la génération automatique des factures: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
