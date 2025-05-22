<?php

namespace App\Services\Visite;

use App\Models\Visite;
use App\Models\Site;
use App\Models\Entreprise;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RapportService
{
    /**
     * Génère un rapport PDF des visites pour une période donnée
     *
     * @param string $dateDebut Date de début au format Y-m-d
     * @param string $dateFin Date de fin au format Y-m-d
     * @param string|null $siteId ID du site (optionnel)
     * @param string|null $entrepriseId ID de l'entreprise (optionnel)
     * @return \Illuminate\Http\Response
     */
    public function genererRapportPDF($dateDebut, $dateFin, $siteId = null, $entrepriseId = null)
    {
        try {
            // Convertir les dates
            $debut = Carbon::parse($dateDebut)->startOfDay();
            $fin = Carbon::parse($dateFin)->endOfDay();
            
            // Récupérer les données
            $query = Visite::whereBetween('date_arrivee', [$debut, $fin])
                ->with(['visiteur', 'site', 'entreprise']);
            
            // Filtrer par site si spécifié
            if ($siteId) {
                $query->where('site_id', $siteId);
                $site = Site::find($siteId);
            }
            
            // Filtrer par entreprise si spécifié
            if ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
                $entreprise = Entreprise::find($entrepriseId);
            } elseif ($siteId && $site) {
                $entreprise = $site->entreprise;
            }
            
            // Récupérer les visites
            $visites = $query->orderBy('date_arrivee', 'desc')->get();
            
            // Préparer les statistiques
            $stats = [
                'total' => $visites->count(),
                'en_cours' => $visites->where('statut', 'en_cours')->count(),
                'terminees' => $visites->where('statut', 'terminee')->count(),
                'annulees' => $visites->where('statut', 'annulee')->count(),
            ];
            
            // Statistiques par site
            $statsBySite = [];
            if (!$siteId) {
                $statsBySite = $visites->groupBy('site.nom')->map(function ($group) {
                    return [
                        'nom' => $group->first()->site->nom,
                        'total' => $group->count(),
                        'en_cours' => $group->where('statut', 'en_cours')->count(),
                        'terminees' => $group->where('statut', 'terminee')->count(),
                        'annulees' => $group->where('statut', 'annulee')->count(),
                    ];
                })->values()->toArray();
            }
            
            // Statistiques par jour
            $statsByDay = $visites->groupBy(function ($visite) {
                return Carbon::parse($visite->date_arrivee)->format('Y-m-d');
            })->map(function ($group, $date) {
                return [
                    'date' => Carbon::parse($date)->format('d/m/Y'),
                    'total' => $group->count(),
                ];
            })->values()->toArray();
            
            // Générer le PDF
            $pdf = PDF::loadView('rapports.visites', [
                'visites' => $visites,
                'stats' => $stats,
                'statsBySite' => $statsBySite,
                'statsByDay' => $statsByDay,
                'dateDebut' => $debut->format('d/m/Y'),
                'dateFin' => $fin->format('d/m/Y'),
                'site' => $site ?? null,
                'entreprise' => $entreprise ?? null,
                'date_generation' => Carbon::now()->format('d/m/Y H:i:s'),
            ]);
            
            $fileName = 'rapport_visites_' . $debut->format('Ymd') . '_' . $fin->format('Ymd');
            if ($siteId && isset($site)) {
                $fileName .= '_' . Str::slug($site->nom);
            }
            $fileName .= '.pdf';
            
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du rapport de visites : ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Génère un rapport Excel des visites pour une période donnée
     *
     * @param string $dateDebut Date de début au format Y-m-d
     * @param string $dateFin Date de fin au format Y-m-d
     * @param string|null $siteId ID du site (optionnel)
     * @param string|null $entrepriseId ID de l'entreprise (optionnel)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function genererRapportExcel($dateDebut, $dateFin, $siteId = null, $entrepriseId = null)
    {
        // Cette méthode sera implémentée ultérieurement avec la bibliothèque Laravel Excel
        // Pour l'instant, nous utiliserons uniquement le format PDF
    }
}
