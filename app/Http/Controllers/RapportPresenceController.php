<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Site;
use App\Models\Entreprise;
use App\Models\Employeur;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class RapportPresenceController extends Controller
{
    /**
     * Affiche le formulaire de génération de rapport
     */
    public function index()
    {
        $user = Auth::user();
        
        // Récupérer les sites de l'entreprise de l'utilisateur
        $sites = Site::where('entreprise_id', $user->entreprise_id)->get();
        
        return view('rapports.presences.index', compact('sites'));
    }
    
    /**
     * Génère et affiche le rapport de présence
     */
    public function generer(Request $request)
    {
        $user = Auth::user();
        $entreprise = $user->entreprise;
        
        // Valider les données du formulaire
        $validatedData = $request->validate([
            'type_rapport' => 'required|in:journalier,mensuel,personnalise',
            'site_id' => 'nullable|exists:sites,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);
        
        // Préparer les dates
        $dateDebut = Carbon::parse($validatedData['date_debut'])->startOfDay();
        $dateFin = Carbon::parse($validatedData['date_fin'])->endOfDay();
        
        // Si le type de rapport est journalier, s'assurer que la date de fin est la même que la date de début
        if ($validatedData['type_rapport'] === 'journalier') {
            $dateFin = Carbon::parse($validatedData['date_debut'])->endOfDay();
        }
        
        // Si le type de rapport est mensuel, ajuster les dates pour couvrir tout le mois
        if ($validatedData['type_rapport'] === 'mensuel') {
            $dateDebut = Carbon::parse($validatedData['date_debut'])->startOfMonth()->startOfDay();
            $dateFin = Carbon::parse($validatedData['date_debut'])->endOfMonth()->endOfDay();
        }
        
        // Construire la requête de base
        $query = Presence::query()
            ->whereHas('employeur', function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            })
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
                      ->orWhereBetween('date_heure_sortie', [$dateDebut, $dateFin]);
            });
        
        // Filtrer par site si spécifié
        if (!empty($validatedData['site_id'])) {
            $query->where('site_id', $validatedData['site_id']);
            $site = Site::find($validatedData['site_id']);
        } else {
            $site = null;
        }
        
        // Récupérer les présences
        $presences = $query->with(['employeur', 'site', 'validateur'])->get();
        
        // Calculer les statistiques
        $stats = $this->calculerStatistiques($presences, $dateDebut, $dateFin, $entreprise->id);
        
        // Préparer les données pour le rapport
        $data = [
            'entreprise' => $entreprise,
            'site' => $site,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'presences' => $presences,
            'stats' => $stats,
            'typeRapport' => $validatedData['type_rapport'],
        ];
        
        // Stocker les données en session pour l'export PDF
        session(['rapport_presence_data' => $data]);
        
        return view('rapports.presences.rapport', $data);
    }
    
    /**
     * Calcule les statistiques pour le rapport
     */
    private function calculerStatistiques($presences, $dateDebut, $dateFin, $entrepriseId)
    {
        // Nombre total d'employés dans l'entreprise
        $totalEmployes = Employeur::where('entreprise_id', $entrepriseId)->count();
        
        // Nombre d'employés présents dans la période
        $employesPresentIds = $presences->pluck('employeur_id')->unique()->count();
        
        // Statistiques par statut
        $statuts = $presences->groupBy('statut')->map->count();
        
        // Statistiques par site
        $parSite = $presences->groupBy('site.nom')->map->count();
        
        // Statistiques de validation
        $validation = $presences->groupBy('statut_validation')->map->count();
        
        // Heures totales travaillées - méthode améliorée
        $tempsTotalMinutes = $presences->sum(function ($presence) {
            // Si la durée effective est déjà calculée, l'utiliser
            if (!empty($presence->duree_effective)) {
                return $presence->duree_effective;
            }
            
            // Sinon, calculer la durée à partir des dates d'entrée et de sortie
            if ($presence->date_heure_entree && $presence->date_heure_sortie) {
                return $presence->date_heure_entree->diffInMinutes($presence->date_heure_sortie);
            }
            
            return 0;
        });
        
        // Conversion en heures
        $heuresTravaillees = $tempsTotalMinutes / 60;
        
        // Minutes totales de retard
        $minutesRetard = $presences->sum('retard') ?? 0;
        
        // Taux de présence (employés présents / total employés)
        $tauxPresence = $totalEmployes > 0 ? ($employesPresentIds / $totalEmployes) * 100 : 0;
        
        // Présences par jour de la semaine
        $presencesParJour = $presences
            ->groupBy(function ($presence) {
                return Carbon::parse($presence->date_heure_entree)->format('l');
            })
            ->map->count();
            
        // Heures d'arrivée moyennes
        $heuresArrivee = $presences->groupBy(function ($presence) {
            return Carbon::parse($presence->date_heure_entree)->format('H');
        })->map->count();
        
        return [
            'total_employes' => $totalEmployes,
            'employes_presents' => $employesPresentIds,
            'taux_presence' => round($tauxPresence, 2),
            'statuts' => $statuts,
            'par_site' => $parSite,
            'validation' => $validation,
            'heures_travaillees' => round($heuresTravaillees, 2),
            'minutes_retard' => $minutesRetard,
            'presences_par_jour' => $presencesParJour,
            'heures_arrivee' => $heuresArrivee,
        ];
    }
    
    /**
     * Exporte le rapport au format PDF
     */
    public function exporterPdf(Request $request)
    {
        // Valider les paramètres
        $request->validate([
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after_or_equal:dateDebut',
            'site_id' => 'nullable|exists:sites,id',
        ]);
        
        $user = Auth::user();
        $entreprise = $user->entreprise;
        
        // Préparer les dates
        $dateDebut = Carbon::parse($request->dateDebut)->startOfDay();
        $dateFin = Carbon::parse($request->dateFin)->endOfDay();
        
        // Construire la requête de base
        $query = Presence::query()
            ->whereHas('employeur', function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            })
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_heure_entree', [$dateDebut, $dateFin])
                      ->orWhereBetween('date_heure_sortie', [$dateDebut, $dateFin]);
            });
        
        // Filtrer par site si spécifié
        if ($request->site_id) {
            $query->where('site_id', $request->site_id);
            $site = Site::find($request->site_id);
        } else {
            $site = null;
        }
        
        // Récupérer les présences
        $presences = $query->with(['employeur', 'site', 'validateur'])->get();
        
        // Calculer les statistiques
        $stats = $this->calculerStatistiques($presences, $dateDebut, $dateFin, $entreprise->id);
        
        // Préparer les données pour le rapport PDF
        $data = [
            'entreprise' => $entreprise,
            'site' => $site,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'presences' => $presences,
            'stats' => $stats,
        ];
        
        // Générer le PDF
        $pdf = PDF::loadView('rapports.presences.pdf', $data);
        
        // Définir le nom du fichier
        $fileName = 'rapport_presence_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.pdf';
        
        // Retourner le PDF pour téléchargement
        return $pdf->download($fileName);
    }
}
