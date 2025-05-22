<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entreprise;
use App\Http\Controllers\RapportPresenceController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenererRapportPresenceQuotidien extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rapport:generer-presence-quotidien 
                            {--date= : Date du rapport (format Y-m-d), par défaut hier}
                            {--entreprise= : ID de l\'entreprise spécifique}
                            {--envoyer : Envoyer le rapport par email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Génère automatiquement les rapports de présence quotidiens pour toutes les entreprises';

    /**
     * @var RapportPresenceController
     */
    protected $rapportController;

    /**
     * Create a new command instance.
     */
    public function __construct(RapportPresenceController $rapportController)
    {
        parent::__construct();
        $this->rapportController = $rapportController;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateOption = $this->option('date');
        $entrepriseId = $this->option('entreprise');
        $envoyerEmail = $this->option('envoyer');
        
        // Déterminer la date du rapport (par défaut: hier)
        $date = $dateOption 
            ? Carbon::createFromFormat('Y-m-d', $dateOption) 
            : Carbon::yesterday();
        
        if (!$date) {
            $this->error("Format de date invalide. Utilisez le format Y-m-d (ex: 2025-05-18)");
            return 1;
        }

        $this->info("Démarrage de la génération des rapports de présence pour le {$date->format('d/m/Y')}...");

        try {
            // Si un ID d'entreprise est spécifié, on ne traite que cette entreprise
            if ($entrepriseId) {
                $entreprises = Entreprise::where('id', $entrepriseId)->get();
                if ($entreprises->isEmpty()) {
                    $this->error("Entreprise avec ID {$entrepriseId} non trouvée.");
                    return 1;
                }
            } else {
                // Récupérer toutes les entreprises actives
                $entreprises = Entreprise::where('statut', 'actif')->get();
            }

            $totalRapports = 0;
            $totalErreurs = 0;

            foreach ($entreprises as $entreprise) {
                $this->info("Génération du rapport pour l'entreprise: {$entreprise->nom}");
                
                try {
                    // Créer une requête simulée avec les paramètres nécessaires
                    $request = new Request([
                        'type_rapport' => 'journalier',
                        'date_debut' => $date->format('Y-m-d'),
                        'date_fin' => $date->format('Y-m-d'),
                        // Pas de site_id pour avoir un rapport global de l'entreprise
                    ]);
                    
                    // Simuler l'authentification de l'utilisateur admin de l'entreprise
                    $adminUser = $entreprise->users()
                        ->whereHas('roles', function ($query) {
                            $query->where('name', 'admin');
                        })
                        ->first();
                    
                    if (!$adminUser) {
                        $this->warn("Aucun utilisateur admin trouvé pour l'entreprise {$entreprise->nom}. Rapport ignoré.");
                        $totalErreurs++;
                        continue;
                    }
                    
                    // Générer le rapport PDF
                    $data = $this->prepareReportData($entreprise, $date, null);
                    
                    // Générer le PDF
                    $pdf = PDF::loadView('rapports.presences.pdf', $data);
                    
                    // Définir le nom du fichier
                    $fileName = 'rapport_presence_' . $entreprise->id . '_' . $date->format('Y-m-d') . '.pdf';
                    $filePath = 'rapports/presences/' . $fileName;
                    
                    // Enregistrer le PDF dans le stockage
                    Storage::put($filePath, $pdf->output());
                    
                    $this->info("Rapport enregistré: {$filePath}");
                    
                    // Envoyer par email si demandé
                    if ($envoyerEmail) {
                        $this->envoyerRapportParEmail($entreprise, $adminUser, $filePath, $date);
                    }
                    
                    $totalRapports++;
                    
                } catch (\Exception $e) {
                    $this->error("Erreur lors de la génération du rapport pour l'entreprise {$entreprise->nom}: " . $e->getMessage());
                    Log::error("Erreur de génération de rapport: " . $e->getMessage(), [
                        'entreprise_id' => $entreprise->id,
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                    $totalErreurs++;
                }
            }

            $this->info("Génération terminée. Total des rapports générés: {$totalRapports}");
            if ($totalErreurs > 0) {
                $this->warn("Erreurs rencontrées: {$totalErreurs}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Erreur globale lors de la génération des rapports: " . $e->getMessage());
            Log::error("Erreur dans GenererRapportPresenceQuotidien: " . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Prépare les données pour le rapport
     */
    protected function prepareReportData($entreprise, $date, $site = null)
    {
        // Préparer les dates
        $dateDebut = $date->copy()->startOfDay();
        $dateFin = $date->copy()->endOfDay();
        
        // Construire la requête de base pour les présences
        $query = \App\Models\Presence::query()
            ->whereHas('employeur', function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            })
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
                      ->orWhereBetween('date_heure_sortie', [$dateDebut, $dateFin]);
            });
        
        // Filtrer par site si spécifié
        if ($site) {
            $query->where('site_id', $site->id);
        }
        
        // Récupérer les présences
        $presences = $query->with(['employeur', 'site', 'validateur'])->get();
        
        // Calculer les statistiques
        $stats = $this->rapportController->calculerStatistiques($presences, $dateDebut, $dateFin, $entreprise->id);
        
        // Préparer les données pour le rapport
        return [
            'entreprise' => $entreprise,
            'site' => $site,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'presences' => $presences,
            'stats' => $stats,
        ];
    }
    
    /**
     * Envoie le rapport par email
     */
    protected function envoyerRapportParEmail($entreprise, $adminUser, $filePath, $date)
    {
        try {
            // Vérifier si le service de notification est disponible
            $notificationService = app(\App\Services\NotificationService::class);
            
            $notificationService->envoyerRapportPresence(
                $adminUser,
                $entreprise,
                $filePath,
                $date
            );
            
            $this->info("Email envoyé à {$adminUser->email} avec le rapport de présence.");
            
        } catch (\Exception $e) {
            $this->error("Erreur lors de l'envoi de l'email: " . $e->getMessage());
            Log::error("Erreur d'envoi d'email de rapport: " . $e->getMessage());
        }
    }
}
