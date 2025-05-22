<?php

namespace App\Http\Controllers\Biometrique;

use App\Http\Controllers\Controller;
use App\Models\AppareilBiometrique;
use App\Models\LogAppareilBiometrique;
use App\Services\Biometrique\LogAppareilBiometriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Contrôleur pour la gestion des logs d'appareils biométriques
 */
class LogAppareilBiometriqueController extends Controller
{
    /**
     * @var LogAppareilBiometriqueService
     */
    protected $logService;

    /**
     * Constructeur
     *
     * @param LogAppareilBiometriqueService $logService
     */
    public function __construct(LogAppareilBiometriqueService $logService)
    {
        $this->logService = $logService;
        $this->middleware('auth');
        $this->middleware('permission:gerer_appareils_biometriques');
    }

    /**
     * Affiche la liste des logs d'appareils biométriques
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = LogAppareilBiometrique::query();

        // Filtres
        if ($request->has('appareil_id') && $request->appareil_id) {
            $query->where('appareil_biometrique_id', $request->appareil_id);
        }

        if ($request->has('type_evenement') && $request->type_evenement) {
            $query->where('type_evenement', $request->type_evenement);
        }

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $logs = $query->with(['appareil', 'user'])->paginate(20);

        return view('biometrique.logs.index', [
            'logs' => $logs,
            'appareils' => AppareilBiometrique::orderBy('nom')->get(),
            'types_evenements' => LogAppareilBiometrique::distinct('type_evenement')->pluck('type_evenement'),
            'statuts' => LogAppareilBiometrique::distinct('statut')->pluck('statut')
        ]);
    }

    /**
     * Affiche les détails d'un log
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $log = LogAppareilBiometrique::with(['appareil', 'user'])->findOrFail($id);
        
        return view('biometrique.logs.show', [
            'log' => $log
        ]);
    }

    /**
     * Supprime un log
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->logService->deleteLog($id);
        
        return redirect()->route('biometrique.logs.index')
            ->with('success', 'Log supprimé avec succès');
    }

    /**
     * Supprime tous les logs d'un appareil
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appareil_id' => 'required|exists:appareil_biometriques,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $this->logService->clearLogs($request->appareil_id);
        
        return redirect()->route('biometrique.logs.index')
            ->with('success', 'Logs supprimés avec succès');
    }

    /**
     * Exporte les logs en CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = LogAppareilBiometrique::query();

        // Appliquer les mêmes filtres que pour l'index
        if ($request->has('appareil_id') && $request->appareil_id) {
            $query->where('appareil_biometrique_id', $request->appareil_id);
        }

        if ($request->has('type_evenement') && $request->type_evenement) {
            $query->where('type_evenement', $request->type_evenement);
        }

        if ($request->has('statut') && $request->statut) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_debut') && $request->date_debut) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin') && $request->date_fin) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('details', 'like', "%{$search}%");
            });
        }

        $logs = $query->with(['appareil', 'user'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="logs_appareils_biometriques.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Appareil',
                'Type d\'événement',
                'Message',
                'Statut',
                'Utilisateur',
                'Date',
                'Détails'
            ]);
            
            // Données
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->appareil ? $log->appareil->nom : 'N/A',
                    $log->type_evenement,
                    $log->message,
                    $log->statut,
                    $log->user ? $log->user->name : 'N/A',
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->details
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Affiche les statistiques des logs
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function stats(Request $request)
    {
        // Période
        $dateDebut = $request->get('date_debut', now()->subDays(30)->format('Y-m-d'));
        $dateFin = $request->get('date_fin', now()->format('Y-m-d'));
        
        // Statistiques par appareil
        $statsByDevice = $this->logService->getStatsByDevice($dateDebut, $dateFin);
        
        // Statistiques par type d'événement
        $statsByEventType = $this->logService->getStatsByEventType($dateDebut, $dateFin);
        
        // Statistiques par statut
        $statsByStatus = $this->logService->getStatsByStatus($dateDebut, $dateFin);
        
        // Évolution dans le temps
        $timelineStats = $this->logService->getTimelineStats($dateDebut, $dateFin);
        
        return view('biometrique.logs.stats', [
            'statsByDevice' => $statsByDevice,
            'statsByEventType' => $statsByEventType,
            'statsByStatus' => $statsByStatus,
            'timelineStats' => $timelineStats,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);
    }
}
