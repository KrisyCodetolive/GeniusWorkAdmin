<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use App\Services\Tasks\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailyRoutineTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:generate-routine {entreprise_id? : ID de l\'entreprise spécifique (optionnel)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère les tâches routinières pour aujourd\'hui pour toutes les entreprises ou une entreprise spécifique';

    /**
     * @var TaskService
     */
    protected $taskService;

    /**
     * Create a new command instance.
     *
     * @param TaskService $taskService
     * @return void
     */
    public function __construct(TaskService $taskService)
    {
        parent::__construct();
        $this->taskService = $taskService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $entrepriseId = $this->argument('entreprise_id');
        
        try {
            if ($entrepriseId) {
                // Générer les tâches pour une entreprise spécifique
                $this->genererPourEntreprise($entrepriseId);
            } else {
                // Générer les tâches pour toutes les entreprises
                $this->genererPourToutesEntreprises();
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Une erreur est survenue lors de la génération des tâches routinières: " . $e->getMessage());
            Log::error('Erreur lors de la génération des tâches routinières', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Génère les tâches routinières pour une entreprise spécifique
     *
     * @param string $entrepriseId
     * @return void
     */
    protected function genererPourEntreprise(string $entrepriseId)
    {
        $this->info("Génération des tâches routinières pour l'entreprise {$entrepriseId}...");
        
        $tachesGenerees = $this->taskService->genererTachesRoutine($entrepriseId);
        
        $this->info("{$tachesGenerees->count()} tâches routinières générées avec succès pour l'entreprise {$entrepriseId}.");
    }
    
    /**
     * Génère les tâches routinières pour toutes les entreprises
     *
     * @return void
     */
    protected function genererPourToutesEntreprises()
    {
        $this->info("Génération des tâches routinières pour toutes les entreprises...");
        
        $entreprises = Entreprise::all();
        $totalTachesGenerees = 0;
        
        foreach ($entreprises as $entreprise) {
            $this->info("Traitement de l'entreprise {$entreprise->nom} (ID: {$entreprise->id})...");
            
            $tachesGenerees = $this->taskService->genererTachesRoutine($entreprise->id);
            $totalTachesGenerees += $tachesGenerees->count();
            
            $this->info("{$tachesGenerees->count()} tâches routinières générées pour l'entreprise {$entreprise->nom}.");
        }
        
        $this->info("Total: {$totalTachesGenerees} tâches routinières générées pour {$entreprises->count()} entreprises.");
    }
}
