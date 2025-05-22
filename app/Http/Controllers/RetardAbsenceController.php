<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RetardAbsenceService;
use App\Services\PointageService;
use App\Models\User;
use App\Models\Presence;
use App\Models\Notification;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RetardAbsenceController extends Controller
{
    /**
     * Service de gestion des retards et absences.
     *
     * @var RetardAbsenceService
     */
    protected $retardAbsenceService;

    /**
     * Service de pointage.
     *
     * @var PointageService
     */
    protected $pointageService;

    /**
     * Crée une nouvelle instance du contrôleur.
     *
     * @param RetardAbsenceService $retardAbsenceService
     * @param PointageService $pointageService
     * @return void
     */
    public function __construct(RetardAbsenceService $retardAbsenceService, PointageService $pointageService)
    {
        $this->retardAbsenceService = $retardAbsenceService;
        $this->pointageService = $pointageService;
        $this->middleware('auth');
    }

    /**
     * Affiche le tableau de bord des retards et absences.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Vérifier les permissions
        $this->authorize('viewAny', Presence::class);

        $user = Auth::user();
        $employeur = $user->employeur;
        
        // Paramètres de filtrage
        $dateDebut = $request->input('date_debut') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_fin')) 
            : Carbon::now()->endOfDay();
            
        $departementId = $request->input('departement_id');
        $userId = $request->input('user_id');
        $type = $request->input('type', 'all'); // retard, absence, sortie_manquante, all
        
        // Récupérer les statistiques
        $stats = $this->getStatistiques($employeur->id, $dateDebut, $dateFin, $departementId, $userId, $type);
        
        // Récupérer les départements pour le filtre
        $departements = $employeur->departements;
        
        // Récupérer les employés pour le filtre
        $employes = User::where('employeur_id', $employeur->id)
            ->where('actif', true)
            ->when($departementId, function ($query) use ($departementId) {
                return $query->where('departement_id', $departementId);
            })
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
            
        // Récupérer les notifications récentes
        $notifications = Notification::whereIn('user_id', $employes->pluck('id'))
            ->whereIn('type_notification', ['retard', 'absence', 'sortie_manquante'])
            ->whereBetween('created_at', [$dateDebut, $dateFin])
            ->when($type !== 'all', function ($query) use ($type) {
                return $query->where('type_notification', $type);
            })
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('presence.retard-absence.index', compact(
            'stats', 
            'departements', 
            'employes', 
            'notifications', 
            'dateDebut', 
            'dateFin', 
            'departementId', 
            'userId', 
            'type'
        ));
    }

    /**
     * Affiche les détails des retards d'un employé.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function detailsRetards(Request $request, $userId)
    {
        // Vérifier les permissions
        $this->authorize('view', Presence::class);

        $user = User::findOrFail($userId);
        
        // Paramètres de filtrage
        $dateDebut = $request->input('date_debut') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_fin')) 
            : Carbon::now()->endOfDay();
            
        // Récupérer les informations de retard
        $retardInfo = $this->pointageService->calculerRetards(
            $user,
            $dateDebut,
            $dateFin
        );
        
        // Récupérer les présences pour la période
        $presences = Presence::where('user_id', $userId)
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->orderBy('date_heure')
            ->get()
            ->groupBy(function($presence) {
                return $presence->date_heure->format('Y-m-d');
            });
            
        return view('presence.retard-absence.details-retards', compact(
            'user', 
            'retardInfo', 
            'presences', 
            'dateDebut', 
            'dateFin'
        ));
    }

    /**
     * Affiche les détails des absences d'un employé.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function detailsAbsences(Request $request, $userId)
    {
        // Vérifier les permissions
        $this->authorize('view', Presence::class);

        $user = User::findOrFail($userId);
        
        // Paramètres de filtrage
        $dateDebut = $request->input('date_debut') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_fin')) 
            : Carbon::now()->endOfDay();
            
        // Créer une période de dates
        $periode = CarbonPeriod::create($dateDebut, $dateFin);
        
        // Récupérer les jours de travail de l'employé
        $joursTravail = $user->joursTravail()->pluck('jour')->toArray();
        
        // Récupérer les congés de l'employé
        $conges = $user->conges()
            ->where('statut', 'approuve')
            ->where(function($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('date_debut', [$dateDebut, $dateFin])
                      ->orWhereBetween('date_fin', [$dateDebut, $dateFin])
                      ->orWhere(function($q) use ($dateDebut, $dateFin) {
                          $q->where('date_debut', '<=', $dateDebut)
                            ->where('date_fin', '>=', $dateFin);
                      });
            })
            ->get();
            
        // Récupérer les présences pour la période
        $presences = Presence::where('user_id', $userId)
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->orderBy('date_heure')
            ->get()
            ->groupBy(function($presence) {
                return $presence->date_heure->format('Y-m-d');
            });
            
        // Déterminer les jours d'absence
        $absences = [];
        
        foreach ($periode as $date) {
            // Ignorer les jours où l'employé ne travaille pas
            if (!in_array($date->dayOfWeekIso, $joursTravail)) {
                continue;
            }
            
            // Ignorer les jours de congé
            $estEnConge = false;
            foreach ($conges as $conge) {
                if ($date->between($conge->date_debut, $conge->date_fin)) {
                    $estEnConge = true;
                    break;
                }
            }
            
            if ($estEnConge) {
                continue;
            }
            
            // Vérifier si l'employé a pointé ce jour-là
            $dateStr = $date->format('Y-m-d');
            if (!isset($presences[$dateStr]) || $presences[$dateStr]->isEmpty()) {
                $absences[] = [
                    'date' => $date->copy(),
                    'notification' => Notification::where('user_id', $userId)
                        ->where('type_notification', 'absence')
                        ->whereDate('created_at', $date)
                        ->first()
                ];
            }
        }
            
        return view('presence.retard-absence.details-absences', compact(
            'user', 
            'absences', 
            'dateDebut', 
            'dateFin'
        ));
    }

    /**
     * Affiche les détails des sorties manquantes d'un employé.
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\View\View
     */
    public function detailsSortiesManquantes(Request $request, $userId)
    {
        // Vérifier les permissions
        $this->authorize('view', Presence::class);

        $user = User::findOrFail($userId);
        
        // Paramètres de filtrage
        $dateDebut = $request->input('date_debut') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_debut')) 
            : Carbon::now()->startOfMonth();
            
        $dateFin = $request->input('date_fin') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date_fin')) 
            : Carbon::now()->endOfDay();
            
        // Récupérer les présences pour la période
        $presences = Presence::where('user_id', $userId)
            ->whereBetween('date_heure', [$dateDebut, $dateFin])
            ->orderBy('date_heure')
            ->get()
            ->groupBy(function($presence) {
                return $presence->date_heure->format('Y-m-d');
            });
            
        // Déterminer les jours avec sorties manquantes
        $sortiesManquantes = [];
        
        foreach ($presences as $date => $pointages) {
            $dernierPointage = $pointages->last();
            
            // Si le dernier pointage est une entrée ou une fin de pause, il manque un pointage de sortie
            if (in_array($dernierPointage->type, ['entree', 'pause_fin'])) {
                $sortiesManquantes[] = [
                    'date' => Carbon::parse($date),
                    'dernier_pointage' => $dernierPointage,
                    'notification' => Notification::where('user_id', $userId)
                        ->where('type_notification', 'sortie_manquante')
                        ->whereDate('created_at', $date)
                        ->first()
                ];
            }
        }
            
        return view('presence.retard-absence.details-sorties', compact(
            'user', 
            'sortiesManquantes', 
            'dateDebut', 
            'dateFin'
        ));
    }

    /**
     * Exécute une vérification manuelle des retards et absences.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifierManuellement(Request $request)
    {
        // Vérifier les permissions
        $this->authorize('manage', Presence::class);

        $type = $request->input('type', 'all');
        $date = $request->input('date') 
            ? Carbon::createFromFormat('Y-m-d', $request->input('date')) 
            : Carbon::today();
            
        try {
            switch ($type) {
                case 'retards':
                    $stats = $this->retardAbsenceService->verifierRetards($date);
                    $message = "Vérification des retards effectuée : {$stats['retards_detectes']} retards détectés, {$stats['notifications_envoyees']} notifications envoyées.";
                    break;
                    
                case 'absences':
                    $stats = $this->retardAbsenceService->verifierAbsences($date);
                    $message = "Vérification des absences effectuée : {$stats['absences_detectees']} absences détectées, {$stats['notifications_envoyees']} notifications envoyées.";
                    break;
                    
                case 'sorties':
                    $stats = $this->retardAbsenceService->verifierSortiesManquantes($date);
                    $message = "Vérification des sorties manquantes effectuée : {$stats['sorties_manquantes']} sorties manquantes détectées, {$stats['notifications_envoyees']} notifications envoyées.";
                    break;
                    
                case 'all':
                default:
                    $stats = $this->retardAbsenceService->executerToutesVerifications($date);
                    $message = "Vérification complète effectuée : {$stats['retards']['retards_detectes']} retards, {$stats['absences']['absences_detectees']} absences, {$stats['sorties_manquantes']['sorties_manquantes']} sorties manquantes. Total de {$stats['total_notifications']} notifications envoyées.";
                    break;
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la vérification : ' . $e->getMessage());
        }
    }

    /**
     * Récupère les statistiques de retards et absences.
     *
     * @param int $employeurId
     * @param Carbon $dateDebut
     * @param Carbon $dateFin
     * @param int|null $departementId
     * @param int|null $userId
     * @param string $type
     * @return array
     */
    protected function getStatistiques($employeurId, $dateDebut, $dateFin, $departementId = null, $userId = null, $type = 'all')
    {
        // Requête de base pour les utilisateurs
        $usersQuery = User::where('employeur_id', $employeurId)
            ->where('actif', true)
            ->when($departementId, function ($query) use ($departementId) {
                return $query->where('departement_id', $departementId);
            })
            ->when($userId, function ($query) use ($userId) {
                return $query->where('id', $userId);
            });
            
        $totalEmployes = $usersQuery->count();
        
        // Requête de base pour les notifications
        $notificationsQuery = Notification::whereIn('user_id', $usersQuery->pluck('id'))
            ->whereBetween('created_at', [$dateDebut, $dateFin]);
            
        // Statistiques par type de notification
        $statsRetards = $this->getStatsParType($notificationsQuery, 'retard', $type);
        $statsAbsences = $this->getStatsParType($notificationsQuery, 'absence', $type);
        $statsSorties = $this->getStatsParType($notificationsQuery, 'sortie_manquante', $type);
        
        // Statistiques par département
        $statsParDepartement = [];
        
        if (!$departementId) {
            $departements = DB::table('departements')
                ->where('employeur_id', $employeurId)
                ->get();
                
            foreach ($departements as $departement) {
                $employesDepartement = User::where('employeur_id', $employeurId)
                    ->where('departement_id', $departement->id)
                    ->where('actif', true)
                    ->pluck('id');
                    
                $notifsDepartement = Notification::whereIn('user_id', $employesDepartement)
                    ->whereBetween('created_at', [$dateDebut, $dateFin]);
                    
                $statsParDepartement[$departement->id] = [
                    'nom' => $departement->nom,
                    'total_employes' => count($employesDepartement),
                    'retards' => $this->getStatsParType($notifsDepartement, 'retard', $type),
                    'absences' => $this->getStatsParType($notifsDepartement, 'absence', $type),
                    'sorties_manquantes' => $this->getStatsParType($notifsDepartement, 'sortie_manquante', $type)
                ];
            }
        }
        
        // Statistiques par jour
        $statsParJour = [];
        $periode = CarbonPeriod::create($dateDebut, $dateFin);
        
        foreach ($periode as $date) {
            $dateStr = $date->format('Y-m-d');
            $notifsJour = clone $notificationsQuery;
            
            $statsParJour[$dateStr] = [
                'date' => $date->copy(),
                'retards' => $this->getStatsParType($notifsJour->whereDate('created_at', $date), 'retard', $type),
                'absences' => $this->getStatsParType($notifsJour->whereDate('created_at', $date), 'absence', $type),
                'sorties_manquantes' => $this->getStatsParType($notifsJour->whereDate('created_at', $date), 'sortie_manquante', $type)
            ];
        }
        
        return [
            'periode' => [
                'debut' => $dateDebut,
                'fin' => $dateFin
            ],
            'total_employes' => $totalEmployes,
            'retards' => $statsRetards,
            'absences' => $statsAbsences,
            'sorties_manquantes' => $statsSorties,
            'par_departement' => $statsParDepartement,
            'par_jour' => $statsParJour
        ];
    }

    /**
     * Récupère les statistiques pour un type de notification.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $typeNotification
     * @param string $typeFiltre
     * @return array
     */
    protected function getStatsParType($query, $typeNotification, $typeFiltre)
    {
        if ($typeFiltre !== 'all' && $typeFiltre !== $typeNotification) {
            return [
                'count' => 0,
                'employes_distincts' => 0
            ];
        }
        
        $cloneQuery = clone $query;
        
        return [
            'count' => $cloneQuery->where('type_notification', $typeNotification)->count(),
            'employes_distincts' => $cloneQuery->where('type_notification', $typeNotification)->distinct('user_id')->count('user_id')
        ];
    }
}
