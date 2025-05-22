<?php

namespace App\Console\Commands;

use App\Models\Entreprise;
use App\Models\FraisUsage;
use App\Services\FacturationService;
use App\Services\FraisUsageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FacturerFraisUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facturation:frais-usage 
                            {--entreprise= : ID de l\'entreprise (facultatif, toutes les entreprises par défaut)}
                            {--type= : Type de frais à facturer (facultatif, tous les types par défaut)}
                            {--periode=mois : Période à facturer (mois, trimestre, annee)}
                            {--notifier : Envoyer une notification par email aux entreprises}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Facture automatiquement les frais d\'usage non facturés';

    /**
     * Le service de facturation.
     *
     * @var FacturationService
     */
    protected $facturationService;

    /**
     * Le service de frais d'usage.
     *
     * @var FraisUsageService
     */
    protected $fraisUsageService;

    /**
     * Create a new command instance.
     *
     * @param FacturationService $facturationService
     * @param FraisUsageService $fraisUsageService
     * @return void
     */
    public function __construct(FacturationService $facturationService, FraisUsageService $fraisUsageService)
    {
        parent::__construct();
        $this->facturationService = $facturationService;
        $this->fraisUsageService = $fraisUsageService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Début de la facturation automatique des frais d\'usage...');
        
        try {
            // Récupérer les options
            $entrepriseId = $this->option('entreprise');
            $typeFrais = $this->option('type');
            $periode = $this->option('periode');
            
            // Déterminer les dates de début et de fin selon la période
            $dateFin = Carbon::now();
            
            switch ($periode) {
                case 'mois':
                    $dateDebut = Carbon::now()->startOfMonth();
                    break;
                case 'trimestre':
                    $dateDebut = Carbon::now()->startOfQuarter();
                    break;
                case 'annee':
                    $dateDebut = Carbon::now()->startOfYear();
                    break;
                default:
                    $dateDebut = Carbon::now()->startOfMonth();
            }
            
            // Récupérer les entreprises
            $entreprises = $entrepriseId 
                ? Entreprise::where('id', $entrepriseId)->get() 
                : Entreprise::where('statut', 'actif')->get();
            
            $facturesGenerees = 0;
            $fraisFactures = 0;
            
            foreach ($entreprises as $entreprise) {
                $this->info("Traitement de l'entreprise: {$entreprise->nom}");
                
                // Récupérer les frais d'usage non facturés
                $options = [
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin
                ];
                
                if ($typeFrais) {
                    $options['type_frais'] = $typeFrais;
                }
                
                $fraisUsages = $this->fraisUsageService->getFraisNonFactures($entreprise, $options);
                
                if ($fraisUsages->isEmpty()) {
                    $this->info("Aucun frais d'usage à facturer pour {$entreprise->nom}");
                    continue;
                }
                
                $this->info("Facturation de {$fraisUsages->count()} frais d'usage pour {$entreprise->nom}");
                
                // Facturer les frais d'usage
                $facture = $this->fraisUsageService->facturer($fraisUsages, null, $this->facturationService);
                
                $facturesGenerees++;
                $fraisFactures += $fraisUsages->count();
                
                // Envoyer une notification par email si demandé
                if ($this->option('notifier')) {
                    $this->info("Envoi d'une notification par email à {$entreprise->nom}");
                    $this->facturationService->envoyerFactureParEmail($facture);
                }
            }
            
            $this->info("Facturation terminée: {$facturesGenerees} factures générées pour {$fraisFactures} frais d'usage.");
            
            Log::info("Facturation automatique des frais d'usage: {$facturesGenerees} factures générées pour {$fraisFactures} frais d'usage.");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Une erreur est survenue lors de la facturation des frais d\'usage: ' . $e->getMessage());
            Log::error('Erreur lors de la facturation automatique des frais d\'usage: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
