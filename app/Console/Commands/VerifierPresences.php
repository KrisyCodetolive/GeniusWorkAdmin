<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Presence\RetardAbsenceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VerifierPresences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'presence:verifier 
                            {type? : Type de vérification (retards, absences, sorties, all)}
                            {--date= : Date à vérifier (format Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les retards, absences et sorties manquantes des employeurs';

    /**
     * Service de gestion des retards et absences.
     *
     * @var RetardAbsenceService
     */
    protected $retardAbsenceService;

    /**
     * Create a new command instance.
     *
     * @param RetardAbsenceService $retardAbsenceService
     * @return void
     */
    public function __construct(RetardAbsenceService $retardAbsenceService)
    {
        parent::__construct();
        $this->retardAbsenceService = $retardAbsenceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type') ?? 'all';
        $dateOption = $this->option('date');
        
        $date = $dateOption ? Carbon::createFromFormat('Y-m-d', $dateOption) : Carbon::today();
        
        if (!$date) {
            $this->error('Format de date invalide. Utilisez le format Y-m-d.');
            return 1;
        }
        
        $this->info('Début de la vérification des présences pour le ' . $date->format('d/m/Y'));
        
        try {
            switch ($type) {
                case 'retards':
                    $this->verifierRetards($date);
                    break;
                    
                case 'absences':
                    $this->verifierAbsences($date);
                    break;
                    
                case 'sorties':
                    $this->verifierSortiesManquantes($date);
                    break;
                    
                case 'all':
                default:
                    $this->executerToutesVerifications($date);
                    break;
            }
            
            $this->info('Vérification des présences terminée avec succès.');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Erreur lors de la vérification des présences: ' . $e->getMessage());
            Log::error('Erreur lors de la vérification des présences: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Vérifie les retards des employeurs.
     *
     * @param Carbon $date
     * @return void
     */
    protected function verifierRetards(Carbon $date)
    {
        $this->info('Vérification des retards...');
        $stats = $this->retardAbsenceService->verifierRetards($date);
        
        $this->info("Résultats de la vérification des retards:");
        $this->info("- Employeurs vérifiés: {$stats['total_employeurs']}");
        $this->info("- Retards détectés: {$stats['retards_detectes']}");
        $this->info("- Notifications envoyées: {$stats['notifications_envoyees']}");
        $this->info("- Erreurs: {$stats['erreurs']}");
    }
    
    /**
     * Vérifie les absences des employeurs.
     *
     * @param Carbon $date
     * @return void
     */
    protected function verifierAbsences(Carbon $date)
    {
        $this->info('Vérification des absences...');
        $stats = $this->retardAbsenceService->verifierAbsences($date);
        
        $this->info("Résultats de la vérification des absences:");
        $this->info("- Employeurs vérifiés: {$stats['total_employeurs']}");
        $this->info("- Absences détectées: {$stats['absences_detectees']}");
        $this->info("- Notifications envoyées: {$stats['notifications_envoyees']}");
        $this->info("- Erreurs: {$stats['erreurs']}");
    }
    
    /**
     * Vérifie les sorties manquantes des employeurs.
     *
     * @param Carbon $date
     * @return void
     */
    protected function verifierSortiesManquantes(Carbon $date)
    {
        $this->info('Vérification des sorties manquantes...');
        $stats = $this->retardAbsenceService->verifierSortiesManquantes($date);
        
        $this->info("Résultats de la vérification des sorties manquantes:");
        $this->info("- Employeurs vérifiés: {$stats['total_employeurs']}");
        $this->info("- Sorties manquantes: {$stats['sorties_manquantes']}");
        $this->info("- Notifications envoyées: {$stats['notifications_envoyees']}");
        $this->info("- Erreurs: {$stats['erreurs']}");
    }
    
    /**
     * Exécute toutes les vérifications.
     *
     * @param Carbon $date
     * @return void
     */
    protected function executerToutesVerifications(Carbon $date)
    {
        $this->info('Exécution de toutes les vérifications...');
        $stats = $this->retardAbsenceService->executerToutesVerifications($date);
        
        $this->info("Résultats des vérifications pour le {$stats['date']}:");
        
        $this->info("Retards:");
        $this->info("- Employeurs vérifiés: {$stats['retards']['total_employeurs']}");
        $this->info("- Retards détectés: {$stats['retards']['retards_detectes']}");
        $this->info("- Notifications envoyées: {$stats['retards']['notifications_envoyees']}");
        
        $this->info("Absences:");
        $this->info("- Employeurs vérifiés: {$stats['absences']['total_employeurs']}");
        $this->info("- Absences détectées: {$stats['absences']['absences_detectees']}");
        $this->info("- Notifications envoyées: {$stats['absences']['notifications_envoyees']}");
        
        $this->info("Sorties manquantes:");
        $this->info("- Employeurs vérifiés: {$stats['sorties_manquantes']['total_employeurs']}");
        $this->info("- Sorties manquantes: {$stats['sorties_manquantes']['sorties_manquantes']}");
        $this->info("- Notifications envoyées: {$stats['sorties_manquantes']['notifications_envoyees']}");
        
        $this->info("Total des notifications envoyées: {$stats['total_notifications']}");
        $this->info("Total des erreurs: {$stats['total_erreurs']}");
    }
}
