<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Services\RapportFinancierService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PDF;

class RapportFinancierController extends Controller
{
    /**
     * Le service de rapport financier.
     *
     * @var RapportFinancierService
     */
    protected $rapportFinancierService;

    /**
     * Crée une nouvelle instance du contrôleur.
     *
     * @param RapportFinancierService $rapportFinancierService
     * @return void
     */
    public function __construct(RapportFinancierService $rapportFinancierService)
    {
        $this->rapportFinancierService = $rapportFinancierService;
        $this->middleware('auth');
    }

    /**
     * Affiche le tableau de bord des rapports financiers.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $this->authorize('viewAny', Facturation::class);
        
        // Récupérer les données pour le tableau de bord
        $dateDebut = Carbon::now()->startOfMonth();
        $dateFin = Carbon::now();
        
        // Générer le rapport global
        $rapportGlobal = $this->rapportFinancierService->genererRapportGlobal([
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ]);
        
        // Générer les prévisions financières
        $previsionsFinancieres = $this->rapportFinancierService->genererPrevisionsFinancieres([
            'nombre_mois' => 3,
        ]);
        
        return view('rapports.dashboard', [
            'rapportGlobal' => $rapportGlobal,
            'previsionsFinancieres' => $previsionsFinancieres,
        ]);
    }

    /**
     * Affiche le rapport financier pour une entreprise spécifique.
     *
     * @param Request $request
     * @param Entreprise $entreprise
     * @return \Illuminate\View\View
     */
    public function entreprise(Request $request, Entreprise $entreprise)
    {
        $this->authorize('view', [Facturation::class, $entreprise]);
        
        // Récupérer les paramètres de la requête
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin')) 
            : Carbon::now();
        
        // Générer le rapport financier
        $rapport = $this->rapportFinancierService->genererRapportFinancier($entreprise, [
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ]);
        
        return view('rapports.entreprise', [
            'entreprise' => $entreprise,
            'rapport' => $rapport,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ]);
    }

    /**
     * Génère un rapport financier global.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function global(Request $request)
    {
        $this->authorize('viewAny', Facturation::class);
        
        // Récupérer les paramètres de la requête
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->startOfYear();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin')) 
            : Carbon::now();
        
        // Générer le rapport global
        $rapportGlobal = $this->rapportFinancierService->genererRapportGlobal([
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ]);
        
        return view('rapports.global', [
            'rapportGlobal' => $rapportGlobal,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
        ]);
    }

    /**
     * Génère un rapport de prévisions financières.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function previsions(Request $request)
    {
        $this->authorize('viewAny', Facturation::class);
        
        // Récupérer les paramètres de la requête
        $nombreMois = $request->input('nombre_mois', 3);
        
        // Générer les prévisions financières
        $previsionsFinancieres = $this->rapportFinancierService->genererPrevisionsFinancieres([
            'nombre_mois' => $nombreMois,
        ]);
        
        return view('rapports.previsions', [
            'previsionsFinancieres' => $previsionsFinancieres,
            'nombreMois' => $nombreMois,
        ]);
    }

    /**
     * Télécharge un rapport financier au format PDF.
     *
     * @param Request $request
     * @param Entreprise|null $entreprise
     * @return \Illuminate\Http\Response
     */
    public function telechargerPDF(Request $request, Entreprise $entreprise = null)
    {
        if ($entreprise) {
            $this->authorize('view', [Facturation::class, $entreprise]);
            
            // Récupérer les paramètres de la requête
            $dateDebut = $request->input('date_debut') 
                ? Carbon::parse($request->input('date_debut')) 
                : Carbon::now()->startOfMonth();
                
            $dateFin = $request->input('date_fin') 
                ? Carbon::parse($request->input('date_fin')) 
                : Carbon::now();
            
            // Générer le rapport financier
            $rapport = $this->rapportFinancierService->genererRapportFinancier($entreprise, [
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]);
            
            // Générer le PDF
            $pdf = PDF::loadView('rapports.pdf.entreprise', [
                'entreprise' => $entreprise,
                'rapport' => $rapport,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
            ]);
            
            $filename = "rapport_financier_{$entreprise->id}_{$dateDebut->format('Y-m-d')}_{$dateFin->format('Y-m-d')}.pdf";
        } else {
            $this->authorize('viewAny', Facturation::class);
            
            // Récupérer les paramètres de la requête
            $dateDebut = $request->input('date_debut') 
                ? Carbon::parse($request->input('date_debut')) 
                : Carbon::now()->startOfYear();
                
            $dateFin = $request->input('date_fin') 
                ? Carbon::parse($request->input('date_fin')) 
                : Carbon::now();
            
            // Générer le rapport global
            $rapportGlobal = $this->rapportFinancierService->genererRapportGlobal([
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]);
            
            // Générer le PDF
            $pdf = PDF::loadView('rapports.pdf.global', [
                'rapportGlobal' => $rapportGlobal,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin,
            ]);
            
            $filename = "rapport_financier_global_{$dateDebut->format('Y-m-d')}_{$dateFin->format('Y-m-d')}.pdf";
        }
        
        return $pdf->download($filename);
    }

    /**
     * Exporte un rapport financier au format CSV.
     *
     * @param Request $request
     * @param Entreprise|null $entreprise
     * @return \Illuminate\Http\Response
     */
    public function exporterCSV(Request $request, Entreprise $entreprise = null)
    {
        if ($entreprise) {
            $this->authorize('view', [Facturation::class, $entreprise]);
            
            // Récupérer les paramètres de la requête
            $dateDebut = $request->input('date_debut') 
                ? Carbon::parse($request->input('date_debut')) 
                : Carbon::now()->startOfMonth();
                
            $dateFin = $request->input('date_fin') 
                ? Carbon::parse($request->input('date_fin')) 
                : Carbon::now();
            
            // Générer le rapport financier
            $rapport = $this->rapportFinancierService->genererRapportFinancier($entreprise, [
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]);
            
            // Préparer les données pour le CSV
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=rapport_financier_{$entreprise->id}_{$dateDebut->format('Y-m-d')}_{$dateFin->format('Y-m-d')}.csv",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            
            $callback = function() use ($rapport, $entreprise) {
                $file = fopen('php://output', 'w');
                
                // En-tête
                fputcsv($file, ['Rapport Financier', $entreprise->nom]);
                fputcsv($file, ['Période', $rapport['periode']['debut'], 'à', $rapport['periode']['fin']]);
                fputcsv($file, []);
                
                // Factures
                fputcsv($file, ['Factures']);
                fputcsv($file, ['Total facturé', $rapport['factures']['total']]);
                fputcsv($file, ['Total payé', $rapport['factures']['paye']]);
                fputcsv($file, ['Total impayé', $rapport['factures']['impaye']]);
                fputcsv($file, ['Nombre de factures', $rapport['factures']['count']]);
                fputcsv($file, ['Taux de recouvrement', $rapport['factures']['taux_recouvrement'] . '%']);
                fputcsv($file, ['Retard moyen de paiement', $rapport['factures']['retard_moyen'] . ' jours']);
                fputcsv($file, []);
                
                // Frais d'usage
                fputcsv($file, ['Frais d\'usage']);
                fputcsv($file, ['Total', $rapport['frais_usage']['total']]);
                fputcsv($file, ['Facturé', $rapport['frais_usage']['facture']]);
                fputcsv($file, ['Non facturé', $rapport['frais_usage']['non_facture']]);
                fputcsv($file, ['Nombre de frais', $rapport['frais_usage']['count']]);
                fputcsv($file, []);
                
                // Répartition par type
                fputcsv($file, ['Répartition par type de frais']);
                fputcsv($file, ['Type', 'Nombre', 'Total', 'Moyenne']);
                
                foreach ($rapport['frais_usage']['repartition_par_type'] as $type => $data) {
                    fputcsv($file, [$type, $data['count'], $data['total'], $data['moyenne']]);
                }
                
                fputcsv($file, []);
                
                // Évolution mensuelle
                fputcsv($file, ['Évolution mensuelle']);
                fputcsv($file, ['Mois', 'Total factures', 'Nombre factures', 'Total frais', 'Nombre frais']);
                
                foreach ($rapport['evolution_mensuelle'] as $mois => $data) {
                    fputcsv($file, [
                        $mois, 
                        $data['factures']['total'], 
                        $data['factures']['count'], 
                        $data['frais_usage']['total'], 
                        $data['frais_usage']['count']
                    ]);
                }
                
                fclose($file);
            };
            
            return Response::stream($callback, 200, $headers);
        } else {
            $this->authorize('viewAny', Facturation::class);
            
            // Récupérer les paramètres de la requête
            $dateDebut = $request->input('date_debut') 
                ? Carbon::parse($request->input('date_debut')) 
                : Carbon::now()->startOfYear();
                
            $dateFin = $request->input('date_fin') 
                ? Carbon::parse($request->input('date_fin')) 
                : Carbon::now();
            
            // Générer le rapport global
            $rapportGlobal = $this->rapportFinancierService->genererRapportGlobal([
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]);
            
            // Préparer les données pour le CSV
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=rapport_financier_global_{$dateDebut->format('Y-m-d')}_{$dateFin->format('Y-m-d')}.csv",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];
            
            $callback = function() use ($rapportGlobal) {
                $file = fopen('php://output', 'w');
                
                // En-tête
                fputcsv($file, ['Rapport Financier Global']);
                fputcsv($file, ['Période', $rapportGlobal['periode']['debut'], 'à', $rapportGlobal['periode']['fin']]);
                fputcsv($file, []);
                
                // Données globales
                fputcsv($file, ['Données globales']);
                fputcsv($file, ['Total facturé', $rapportGlobal['global']['total_facture']]);
                fputcsv($file, ['Total payé', $rapportGlobal['global']['total_paye']]);
                fputcsv($file, ['Total impayé', $rapportGlobal['global']['total_impaye']]);
                fputcsv($file, ['Total frais d\'usage', $rapportGlobal['global']['total_frais_usage']]);
                fputcsv($file, ['Nombre de factures', $rapportGlobal['global']['count_factures']]);
                fputcsv($file, ['Nombre de frais d\'usage', $rapportGlobal['global']['count_frais_usage']]);
                fputcsv($file, ['Taux de recouvrement', $rapportGlobal['global']['taux_recouvrement'] . '%']);
                fputcsv($file, []);
                
                // Top entreprises
                fputcsv($file, ['Top 5 des entreprises']);
                fputcsv($file, ['Entreprise', 'Montant total', 'Taux de recouvrement']);
                
                foreach ($rapportGlobal['top_entreprises'] as $entrepriseData) {
                    fputcsv($file, [
                        $entrepriseData['entreprise'], 
                        $entrepriseData['montant_total'], 
                        $entrepriseData['taux_recouvrement'] . '%'
                    ]);
                }
                
                fputcsv($file, []);
                
                // Évolution mensuelle
                fputcsv($file, ['Évolution mensuelle globale']);
                fputcsv($file, ['Mois', 'Total factures', 'Nombre factures', 'Total frais', 'Nombre frais']);
                
                foreach ($rapportGlobal['evolution_mensuelle'] as $mois => $data) {
                    fputcsv($file, [
                        $mois, 
                        $data['factures']['total'], 
                        $data['factures']['count'], 
                        $data['frais_usage']['total'], 
                        $data['frais_usage']['count']
                    ]);
                }
                
                fclose($file);
            };
            
            return Response::stream($callback, 200, $headers);
        }
    }
}
