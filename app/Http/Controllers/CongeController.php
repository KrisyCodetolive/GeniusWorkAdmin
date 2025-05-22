<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\TypeConge;
use App\Models\SoldeConge;
use App\Services\CongeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CongeController extends Controller
{
    protected $congeService;

    public function __construct(CongeService $congeService)
    {
        $this->congeService = $congeService;
        $this->middleware('auth');
    }

    /**
     * Afficher la liste des congés de l'utilisateur
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $conges = Conge::where('employeur_id', $user->employeur->id)
            ->with(['typeConge', 'validateur'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $anneeActuelle = date('Y');
        $soldes = SoldeConge::where('user_id', $user->id)
            ->where('annee', $anneeActuelle)
            ->with('typeConge')
            ->get();

        return view('app.conge.index', compact('conges', 'soldes'));
    }

    /**
     * Afficher le formulaire de demande de congé
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $typesConge = TypeConge::actif()
            ->where(function($query) use ($user) {
                $query->whereNull('entreprise_id')
                    ->orWhere('entreprise_id', $user->employeur->entreprise_id);
            })
            ->get();

        $anneeActuelle = date('Y');
        $soldes = SoldeConge::where('user_id', $user->id)
            ->where('annee', $anneeActuelle)
            ->with('typeConge')
            ->get();

        return view('app.conge.create', compact('typesConge', 'soldes'));
    }

    /**
     * Enregistrer une nouvelle demande de congé
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'type_conge_id' => 'required|exists:type_conges,id',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
            'justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $conge = $this->congeService->creerDemande($request->all(), Auth::user());
            return redirect()->route('conge.index')->with('success', 'Votre demande de congé a été enregistrée avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une demande de congé
     *
     * @param  \App\Models\Conge  $conge
     * @return \Illuminate\View\View
     */
    public function show(Conge $conge)
    {
        $this->authorize('view', $conge);
        
        return view('app.conge.show', compact('conge'));
    }

    /**
     * Annuler une demande de congé
     *
     * @param  \App\Models\Conge  $conge
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function annuler(Conge $conge, Request $request)
    {
        $this->authorize('annuler', $conge);
        
        $request->validate([
            'commentaire' => 'nullable|string|max:255',
        ]);

        try {
            $this->congeService->annulerDemande($conge, $request->commentaire);
            return redirect()->route('conge.index')->with('success', 'Votre demande de congé a été annulée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher le tableau de bord des congés (pour les managers)
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $this->authorize('viewDashboard', Conge::class);
        
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        // Récupérer les demandes en attente pour l'entreprise
        $demandesEnAttente = Conge::whereHas('employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->with(['employeur.user', 'typeConge'])
            ->enAttente()
            ->orderBy('date_debut', 'asc')
            ->get();
            
        // Récupérer les congés approuvés pour le mois en cours
        $moisActuel = Carbon::now();
        $congesMoisActuel = Conge::whereHas('employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->with(['employeur.user', 'typeConge'])
            ->approuve()
            ->where(function($query) use ($moisActuel) {
                $debut = $moisActuel->copy()->startOfMonth();
                $fin = $moisActuel->copy()->endOfMonth();
                $query->whereBetween('date_debut', [$debut, $fin])
                    ->orWhereBetween('date_fin', [$debut, $fin])
                    ->orWhere(function($q) use ($debut, $fin) {
                        $q->where('date_debut', '<=', $debut)
                          ->where('date_fin', '>=', $fin);
                    });
            })
            ->get();
            
        // Statistiques
        $stats = [
            'demandes_en_attente' => $demandesEnAttente->count(),
            'conges_mois_actuel' => $congesMoisActuel->count(),
            'jours_conges_mois' => $congesMoisActuel->sum('duree_jours'),
        ];
        
        return view('app.conge.dashboard', compact('demandesEnAttente', 'congesMoisActuel', 'stats'));
    }

    /**
     * Afficher la liste des demandes à valider (pour les managers)
     *
     * @return \Illuminate\View\View
     */
    public function validation()
    {
        $this->authorize('valider', Conge::class);
        
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        $demandes = Conge::whereHas('employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->with(['employeur.user', 'typeConge'])
            ->enAttente()
            ->orderBy('date_debut', 'asc')
            ->paginate(15);
            
        return view('app.conge.validation', compact('demandes'));
    }

    /**
     * Approuver une demande de congé
     *
     * @param  \App\Models\Conge  $conge
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approuver(Conge $conge, Request $request)
    {
        $this->authorize('valider', Conge::class);
        
        $request->validate([
            'commentaire' => 'nullable|string|max:255',
        ]);

        try {
            $this->congeService->approuverDemande($conge, Auth::user(), $request->commentaire);
            return redirect()->route('conge.validation')->with('success', 'La demande de congé a été approuvée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rejeter une demande de congé
     *
     * @param  \App\Models\Conge  $conge
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejeter(Conge $conge, Request $request)
    {
        $this->authorize('valider', Conge::class);
        
        $request->validate([
            'commentaire' => 'required|string|max:255',
        ]);

        try {
            $this->congeService->rejeterDemande($conge, Auth::user(), $request->commentaire);
            return redirect()->route('conge.validation')->with('success', 'La demande de congé a été rejetée.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher le calendrier des congés
     *
     * @return \Illuminate\View\View
     */
    public function calendrier()
    {
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        // Récupérer tous les congés approuvés pour l'entreprise
        $conges = Conge::whereHas('employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->with(['employeur.user', 'typeConge'])
            ->approuve()
            ->get();
            
        // Formater les données pour le calendrier
        $evenements = [];
        foreach ($conges as $conge) {
            $evenements[] = [
                'id' => $conge->id,
                'title' => $conge->employeur->user->name . ' - ' . $conge->typeConge->nom,
                'start' => $conge->date_debut,
                'end' => Carbon::parse($conge->date_fin)->addDay()->format('Y-m-d'), // +1 jour pour l'affichage correct
                'color' => $this->getColorForCongeType($conge->typeConge->id),
                'url' => route('conge.show', $conge->id)
            ];
        }
            
        return view('app.conge.calendrier', compact('evenements'));
    }

    /**
     * Afficher les statistiques de congés
     *
     * @return \Illuminate\View\View
     */
    public function statistiques()
    {
        $this->authorize('viewStatistiques', Conge::class);
        
        $user = Auth::user();
        $anneeActuelle = date('Y');
        
        $stats = $this->congeService->getStatistiquesUtilisateur($user, $anneeActuelle);
        
        return view('app.conge.statistiques', compact('stats', 'anneeActuelle'));
    }

    /**
     * Obtenir une couleur pour un type de congé
     *
     * @param int $typeCongeId
     * @return string
     */
    protected function getColorForCongeType($typeCongeId)
    {
        $colors = [
            '#4CAF50', // Vert
            '#2196F3', // Bleu
            '#FF9800', // Orange
            '#9C27B0', // Violet
            '#F44336', // Rouge
            '#009688', // Teal
            '#795548', // Marron
            '#607D8B', // Bleu gris
        ];
        
        // Utiliser le modulo pour s'assurer d'avoir une couleur même si on a plus de types que de couleurs
        return $colors[$typeCongeId % count($colors)];
    }
}
