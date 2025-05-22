<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Affiche la liste des notifications de l'utilisateur
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Affiche le détail d'une notification
     *
     * @param string $id ID de la notification
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = Auth::user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        // Marquer comme lue si pas déjà lue
        if (!$notification->estLue()) {
            $notification->marquerCommeLue();
        }
        
        return view('notifications.show', compact('notification'));
    }

    /**
     * Marque une notification comme lue
     *
     * @param Request $request
     * @param string $id ID de la notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        $notification->marquerCommeLue();
        
        return redirect()->back()->with('success', 'Notification marquée comme lue');
    }

    /**
     * Marque toutes les notifications comme lues
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        Notification::where('user_id', $user->id)
            ->nonLues()
            ->update([
                'date_lecture' => now(),
                'read_at' => now(),
                'lu' => true,
                'statut' => 'lu'
            ]);
            
        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues');
    }

    /**
     * Supprime une notification
     *
     * @param string $id ID de la notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
            
        $notification->delete();
        
        return redirect()->route('notifications.index')->with('success', 'Notification supprimée');
    }

    /**
     * Récupère les notifications non lues pour l'API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnreadNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 10);
        
        $notifications = Notification::where('user_id', $user->id)
            ->where('lu', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
        
        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications
        ]);
    }

    /**
     * Marque une notification comme lue via l'API
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiMarkAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|uuid'
        ]);
        
        $success = $this->notificationService->marquerCommeLue($request->notification_id);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marquée comme lue' : 'Notification non trouvée'
        ]);
    }

    /**
     * Affiche l'historique des notifications
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function historique(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);
        $offset = ($page - 1) * $limit;
        
        $notifications = $this->notificationService->getHistoriqueNotifications($user->id, $limit, $offset);
        $total = Notification::where('user_id', $user->id)->count();
        
        return view('notifications.historique', compact('notifications', 'total', 'page', 'limit'));
    }

    /**
     * Affiche les statistiques des notifications (pour les administrateurs)
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function statistiques(Request $request)
    {
        // Vérifier que l'utilisateur est administrateur
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'avez pas les droits pour accéder à cette page');
        }
        
        $entrepriseId = $user->entreprise_id;
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        
        $stats = $this->notificationService->getStatistiquesNotifications($entrepriseId, $dateDebut, $dateFin);
        
        return view('notifications.statistiques', compact('stats', 'dateDebut', 'dateFin'));
    }

    /**
     * Affiche l'historique des notifications pour une entreprise (pour les administrateurs)
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function historiqueEntreprise(Request $request)
    {
        // Vérifier que l'utilisateur est administrateur
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return redirect()->route('notifications.index')
                ->with('error', 'Vous n\'avez pas les droits pour accéder à cette page');
        }
        
        $entrepriseId = $user->entreprise_id;
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 100);
        $offset = ($page - 1) * $limit;
        
        $notifications = $this->notificationService->getHistoriqueNotificationsEntreprise($entrepriseId, $limit, $offset);
        
        return view('notifications.historique_entreprise', compact('notifications', 'page', 'limit'));
    }
}
