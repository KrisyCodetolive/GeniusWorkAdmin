<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Supplementaire;
use App\Models\Conge;
use App\Models\Employeur;
use App\Models\Departement;
use App\Models\Visite;
use App\Models\Site;
use App\Services\PresenceService;
use App\Services\HeuresSupplementairesService;
use App\Services\Visite\RapportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PresencesExport;
use App\Exports\CongesExport;
use App\Exports\SupplementairesExport;

class RapportController extends Controller
{
    protected $presenceService;
    protected $heuresSupplementairesService;
    protected $rapportService;

    public function __construct(
        PresenceService $presenceService,
        HeuresSupplementairesService $heuresSupplementairesService,
        RapportService $rapportService
    ) {
        $this->presenceService = $presenceService;
        $this->heuresSupplementairesService = $heuresSupplementairesService;
        $this->rapportService = $rapportService;
        $this->middleware('auth');
    }

    /**
     * Affiche la page principale des rapports
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('viewAny', Rapport::class);
        
        $user = Auth::user();
        $departements = [];
        
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            $departements = Departement::where('employeur_id', $user->employeur_id)
                ->orderBy('nom')
                ->get();
        }
        
        return view('app.rapport.index', compact('departements'));
    }

    /**
     * Génère un rapport de présence
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function presences(Request $request)
    {
        $this->authorize('viewAny', Presence::class);
        
        $user = Auth::user();
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin'))->endOfDay() 
            : Carbon::now()->endOfDay();
            
        $departementId = $request->input('departement_id');
        $format = $request->input('format', 'html');
        
        // Récupérer les employés concernés
        $employesQuery = Employeur::where('employeur_id', $user->employeur_id);
        
        if ($departementId) {
            $employesQuery->where('departement_id', $departementId);
        }
        
        $employes = $employesQuery->get();
        
        // Préparer les données du rapport
        $donnees = [];
        $totalPresences = 0;
        $totalAbsences = 0;
        $totalRetards = 0;
        
        foreach ($employes as $employe) {
            $presences = Presence::where('employeur_id', $employe->id)
                ->whereBetween('date_heure', [$dateDebut, $dateFin])
                ->get();
                
            $heuresPresence = $this->presenceService->calculerHeuresPresence($employe, $dateDebut, $dateFin);
            $retards = $this->presenceService->calculerRetards($employe, $dateDebut, $dateFin);
            
            $joursPresence = count($presences->groupBy(function ($item) {
                return $item->date_heure->format('Y-m-d');
            }));
            
            $joursOuvrables = $dateDebut->diffInDaysFiltered(function (Carbon $date) {
                return $date->isWeekday();
            }, $dateFin);
            
            $joursAbsence = $joursOuvrables - $joursPresence;
            
            $donnees[] = [
                'employe' => $employe,
                'heures_presence' => $heuresPresence,
                'retards' => $retards,
                'jours_presence' => $joursPresence,
                'jours_absence' => $joursAbsence,
                'presences' => $presences
            ];
            
            $totalPresences += $joursPresence;
            $totalAbsences += $joursAbsence;
            $totalRetards += count($retards['details_jours']);
        }
        
        $statistiques = [
            'total_employes' => count($employes),
            'total_presences' => $totalPresences,
            'total_absences' => $totalAbsences,
            'total_retards' => $totalRetards,
            'taux_presence' => $joursOuvrables > 0 ? round($totalPresences / ($totalPresences + $totalAbsences) * 100, 2) : 0,
            'taux_retard' => $totalPresences > 0 ? round($totalRetards / $totalPresences * 100, 2) : 0
        ];
        
        // Générer le rapport selon le format demandé
        if ($format === 'pdf') {
            $pdf = PDF::loadView('app.rapport.presences_pdf', [
                'donnees' => $donnees,
                'statistiques' => $statistiques,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ]);
            
            return $pdf->download('rapport_presences_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(
                new PresencesExport($donnees, $statistiques, $dateDebut, $dateFin),
                'rapport_presences_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.xlsx'
            );
        }
        
        // Format HTML par défaut
        return view('app.rapport.presences', [
            'donnees' => $donnees,
            'statistiques' => $statistiques,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'departementId' => $departementId
        ]);
    }

    /**
     * Génère un rapport de congés
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function conges(Request $request)
    {
        $this->authorize('viewAny', Conge::class);
        
        $user = Auth::user();
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->startOfYear();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin'))->endOfDay() 
            : Carbon::now()->endOfYear();
            
        $departementId = $request->input('departement_id');
        $format = $request->input('format', 'html');
        
        // Récupérer les employés concernés
        $employesQuery = Employeur::where('employeur_id', $user->employeur_id);
        
        if ($departementId) {
            $employesQuery->where('departement_id', $departementId);
        }
        
        $employes = $employesQuery->get();
        
        // Préparer les données du rapport
        $donnees = [];
        $totalCongesUtilises = 0;
        $totalCongesRestants = 0;
        
        foreach ($employes as $employe) {
            $conges = Conge::where('employeur_id', $employe->id)
                ->where('statut', 'approuve')
                ->whereBetween('date_debut', [$dateDebut, $dateFin])
                ->get();
                
            $joursUtilises = 0;
            foreach ($conges as $conge) {
                $joursUtilises += $conge->date_debut->diffInDaysFiltered(function (Carbon $date) {
                    return $date->isWeekday();
                }, $conge->date_fin);
            }
            
            $congesRestants = $employe->conges_annuels - $joursUtilises;
            
            $donnees[] = [
                'employe' => $employe,
                'conges' => $conges,
                'jours_utilises' => $joursUtilises,
                'jours_restants' => $congesRestants
            ];
            
            $totalCongesUtilises += $joursUtilises;
            $totalCongesRestants += $congesRestants;
        }
        
        $statistiques = [
            'total_employes' => count($employes),
            'total_conges_utilises' => $totalCongesUtilises,
            'total_conges_restants' => $totalCongesRestants,
            'moyenne_conges_utilises' => count($employes) > 0 ? round($totalCongesUtilises / count($employes), 2) : 0
        ];
        
        // Générer le rapport selon le format demandé
        if ($format === 'pdf') {
            $pdf = PDF::loadView('app.rapport.conges_pdf', [
                'donnees' => $donnees,
                'statistiques' => $statistiques,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ]);
            
            return $pdf->download('rapport_conges_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(
                new CongesExport($donnees, $statistiques, $dateDebut, $dateFin),
                'rapport_conges_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.xlsx'
            );
        }
        
        // Format HTML par défaut
        return view('app.rapport.conges', [
            'donnees' => $donnees,
            'statistiques' => $statistiques,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'departementId' => $departementId
        ]);
    }

    /**
     * Génère un rapport d'heures supplémentaires
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function supplementaires(Request $request)
    {
        $this->authorize('viewAny', Supplementaire::class);
        
        $user = Auth::user();
        $dateDebut = $request->input('date_debut') 
            ? Carbon::parse($request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::parse($request->input('date_fin'))->endOfDay() 
            : Carbon::now()->endOfMonth();
            
        $departementId = $request->input('departement_id');
        $format = $request->input('format', 'html');
        
        // Récupérer les employés concernés
        $employesQuery = Employeur::where('employeur_id', $user->employeur_id);
        
        if ($departementId) {
            $employesQuery->where('departement_id', $departementId);
        }
        
        $employes = $employesQuery->get();
        
        // Préparer les données du rapport
        $donnees = [];
        $totalMinutesSupplementaires = 0;
        
        foreach ($employes as $employe) {
            $supplementaires = Supplementaire::where('employeur_id', $employe->id)
                ->where('statut', 'approuve')
                ->whereBetween('date', [$dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d')])
                ->get();
                
            $minutesSupplementaires = $supplementaires->sum('duree_minutes');
            
            $donnees[] = [
                'employe' => $employe,
                'supplementaires' => $supplementaires,
                'minutes_supplementaires' => $minutesSupplementaires,
                'heures_formatees' => $this->formatMinutesEnHeures($minutesSupplementaires)
            ];
            
            $totalMinutesSupplementaires += $minutesSupplementaires;
        }
        
        // Regrouper par département pour les statistiques
        $supplementairesParDepartement = [];
        
        if (count($employes) > 0) {
            $departements = Departement::where('employeur_id', $user->employeur_id)->get();
            
            foreach ($departements as $departement) {
                $minutesDepartement = 0;
                
                foreach ($donnees as $donnee) {
                    if ($donnee['employe']->departement_id === $departement->id) {
                        $minutesDepartement += $donnee['minutes_supplementaires'];
                    }
                }
                
                $supplementairesParDepartement[] = [
                    'departement' => $departement,
                    'minutes_supplementaires' => $minutesDepartement,
                    'heures_formatees' => $this->formatMinutesEnHeures($minutesDepartement)
                ];
            }
        }
        
        $statistiques = [
            'total_employes' => count($employes),
            'total_minutes_supplementaires' => $totalMinutesSupplementaires,
            'total_heures_formatees' => $this->formatMinutesEnHeures($totalMinutesSupplementaires),
            'moyenne_minutes_par_employe' => count($employes) > 0 ? round($totalMinutesSupplementaires / count($employes), 2) : 0,
            'supplementaires_par_departement' => $supplementairesParDepartement
        ];
        
        // Générer le rapport selon le format demandé
        if ($format === 'pdf') {
            $pdf = PDF::loadView('app.rapport.supplementaires_pdf', [
                'donnees' => $donnees,
                'statistiques' => $statistiques,
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ]);
            
            return $pdf->download('rapport_supplementaires_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(
                new SupplementairesExport($donnees, $statistiques, $dateDebut, $dateFin),
                'rapport_supplementaires_' . $dateDebut->format('Y-m-d') . '_' . $dateFin->format('Y-m-d') . '.xlsx'
            );
        }
        
        // Format HTML par défaut
        return view('app.rapport.supplementaires', [
            'donnees' => $donnees,
            'statistiques' => $statistiques,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'departementId' => $departementId
        ]);
    }
    
    /**
     * Formate un nombre de minutes en format heures:minutes
     *
     * @param int $minutes Nombre de minutes
     * @return string
     */
    private function formatMinutesEnHeures(int $minutes): string
    {
        $heures = floor($minutes / 60);
        $minutesRestantes = $minutes % 60;
        
        return sprintf('%02d:%02d', $heures, $minutesRestantes);
    }
    
    /**
     * Génère un rapport des visites
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function visites(Request $request)
    {
        $dateDebut = $request->input('date_debut', now()->startOfMonth()->format('Y-m-d'));
        $dateFin = $request->input('date_fin', now()->format('Y-m-d'));
        $siteId = $request->input('site_id');
        
        $user = Auth::user();
        $entrepriseId = null;
        
        // Si l'utilisateur n'est pas super admin ou support, limiter à son entreprise
        if (!$user->isSuperAdmin() && !$user->isSupport()) {
            $entrepriseId = $user->entreprise_id;
        }
        
        return $this->rapportService->genererRapportPDF($dateDebut, $dateFin, $siteId, $entrepriseId);
    }
}
