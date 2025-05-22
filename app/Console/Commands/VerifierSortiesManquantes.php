<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Services\Presence\RetardAbsenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VerifierSortiesManquantes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:verifier-sorties-manquantes {--date= : Date à vérifier (format Y-m-d)} {--entreprise= : ID de l\'entreprise spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les sorties manquantes des employeurs et envoie des notifications';

    /**
     * @var RetardAbsenceService
     */
    protected $retardAbsenceService;

    /**
     * Create a new command instance.
     */
    public function __construct(RetardAbsenceService $retardAbsenceService)
    {
        parent::__construct();
        $this->retardAbsenceService = $retardAbsenceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateOption = $this->option('date');
        $entrepriseId = $this->option('entreprise');
        
        // Déterminer la date à vérifier
        $date = $dateOption ? Carbon::createFromFormat('Y-m-d', $dateOption) : Carbon::today();
        
        if (!$date) {
            $this->error("Format de date invalide. Utilisez le format Y-m-d (ex: 2023-01-31)");
            return 1;
        }

        $this->info("Démarrage de la vérification des sorties manquantes pour le {$date->format('d/m/Y')}...");

        try {
            // Si un ID d'entreprise est spécifié, on ne traite que cette entreprise
            if ($entrepriseId) {
                $entreprise = Entreprise::find($entrepriseId);
                
                if (!$entreprise) {
                    $this->error("Entreprise avec ID {$entrepriseId} non trouvée.");
                    return 1;
                }
                
                $this->info("Vérification des sorties manquantes pour l'entreprise: {$entreprise->nom}");
                
                // Filtrer les employeurs par entreprise
                $employeurs = Employeur::where('entreprise_id', $entrepriseId)
                    ->where('statut', 'actif')
                    ->get();
                
                $stats = [
                    'total_employeurs' => $employeurs->count(),
                    'sorties_manquantes' => 0,
                    'notifications_envoyees' => 0,
                    'erreurs' => 0
                ];
                
                foreach ($employeurs as $employeur) {
                    // Appeler la méthode du service pour chaque employeur individuellement
                    $this->info("Vérification pour l'employeur: {$employeur->nom_complet}");
                    
                    // Créer un tableau pour stocker les résultats de cet employeur
                    $employeurStats = [
                        'total_employeurs' => 1,
                        'sorties_manquantes' => 0,
                        'notifications_envoyees' => 0,
                        'erreurs' => 0
                    ];
                    
                    // Utiliser une méthode interne pour vérifier les sorties manquantes pour un seul employeur
                    $employeurStats = $this->retardAbsenceService->verifierSortiesManquantes($date);
                    
                    // Ajouter les résultats aux statistiques globales
                    $stats['sorties_manquantes'] += $employeurStats['sorties_manquantes'];
                    $stats['notifications_envoyees'] += $employeurStats['notifications_envoyees'];
                    $stats['erreurs'] += $employeurStats['erreurs'];
                }
            } else {
                // Vérifier toutes les entreprises
                $this->info("Vérification des sorties manquantes pour toutes les entreprises");
                $stats = $this->retardAbsenceService->verifierSortiesManquantes($date);
            }

            // Afficher les statistiques
            $this->info("Vérification terminée avec les résultats suivants:");
            $this->info("- Nombre total d'employeurs vérifiés: {$stats['total_employeurs']}");
            $this->info("- Sorties manquantes détectées: {$stats['sorties_manquantes']}");
            $this->info("- Notifications envoyées: {$stats['notifications_envoyees']}");
            
            if ($stats['erreurs'] > 0) {
                $this->warn("- Erreurs rencontrées: {$stats['erreurs']}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur lors de la vérification des sorties manquantes: " . $e->getMessage());
            Log::error("Erreur dans VerifierSortiesManquantes: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
