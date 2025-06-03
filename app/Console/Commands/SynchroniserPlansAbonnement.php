<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TarificationService;
use Illuminate\Support\Facades\Log;

class SynchroniserPlansAbonnement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abonnements:synchroniser-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les plans d\'abonnement selon la structure tarifaire définie';

    /**
     * Execute the console command.
     */
    public function handle(TarificationService $tarificationService)
    {
        $this->info('Début de la synchronisation des plans d\'abonnement...');

        try {
            $plans = $tarificationService->synchroniserPlansAbonnement();
            
            $this->info('Synchronisation terminée avec succès !');
            $this->info('Nombre de plans synchronisés : ' . $plans->count());
            
            // Afficher un tableau récapitulatif des plans
            $headers = ['Nom', 'Min Employés', 'Max Employés', 'Coût Fixe', 'Coût par Employé'];
            $rows = [];
            
            foreach ($plans as $plan) {
                $rows[] = [
                    $plan->nom,
                    $plan->nombre_employes_min,
                    $plan->nombre_employes_max,
                    number_format($plan->prix_mensuel, 0, '.', ' ') . ' ' . $plan->devise,
                    number_format($plan->cout_par_employe, 0, '.', ' ') . ' ' . $plan->devise,
                ];
            }
            
            $this->table($headers, $rows);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Erreur lors de la synchronisation des plans d\'abonnement : ' . $e->getMessage());
            Log::error('Erreur lors de la synchronisation des plans d\'abonnement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
