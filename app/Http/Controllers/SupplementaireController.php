<?php

namespace App\Http\Controllers;

use App\Models\Supplementaire;
use App\Models\Employeur;
use App\Models\User;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\HeuresSupplementairesService;

class SupplementaireController extends Controller
{
    protected $heuresSupplementairesService;

    public function __construct(HeuresSupplementairesService $heuresSupplementairesService)
    {
        $this->heuresSupplementairesService = $heuresSupplementairesService;
        $this->middleware('auth');
    }

    /**
     * Affiche la liste des heures supplémentaires
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasRole(['admin', 'manager', 'supervisor']);
        
        $query = Supplementaire::with(['employeur', 'validateur']);
        
        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->periode($request->date_debut, $request->date_fin);
        }
        
        // Si l'utilisateur n'est pas un manager, il ne voit que ses propres heures supplémentaires
        if (!$isManager) {
            $employeur = Employeur::where('user_id', $user->id)->first();
            if ($employeur) {
                $query->where('employeur_id', $employeur->id);
            } else {
                return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas associé à un employeur.');
            }
        } elseif ($request->has('employeur_id')) {
            $query->where('employeur_id', $request->employeur_id);
        }
        
        $supplementaires = $query->orderBy('date', 'desc')->paginate(15);
        
        return view('app.supplementaire.index', [
            'supplementaires' => $supplementaires,
            'isManager' => $isManager
        ]);
    }

    /**
     * Affiche le formulaire de création d'heures supplémentaires
     */
    public function create()
    {
        $user = Auth::user();
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        if (!$employeur && !$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à créer des heures supplémentaires.');
        }
        
        return view('app.supplementaire.create', [
            'employeur' => $employeur
        ]);
    }

    /**
     * Enregistre une nouvelle demande d'heures supplémentaires
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employeur_id' => 'required|exists:employeurs,id',
            'date' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'motif' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $date = Carbon::parse($request->date);
        $heureDebut = Carbon::parse($request->date . ' ' . $request->heure_debut);
        $heureFin = Carbon::parse($request->date . ' ' . $request->heure_fin);
        
        // Si l'heure de fin est avant l'heure de début, on suppose que c'est le jour suivant
        if ($heureFin->lt($heureDebut)) {
            $heureFin->addDay();
        }
        
        $nombreHeures = $heureDebut->diffInMinutes($heureFin) / 60;
        
        $employeur = Employeur::findOrFail($request->employeur_id);
        
        $supplementaire = new Supplementaire([
            'employeur_id' => $request->employeur_id,
            'date' => $date,
            'heure_debut' => $heureDebut,
            'heure_fin' => $heureFin,
            'nombre_heures' => $nombreHeures,
            'taux_majoration' => 50, // Valeur par défaut, peut être modifiée
            'montant' => 0, // Sera calculé plus tard
            'motif' => $request->motif,
            'statut' => 'en_attente'
        ]);
        
        $supplementaire->save();
        $supplementaire->calculerMontant();
        
        return redirect()->route('supplementaires.show', $supplementaire->id)
            ->with('success', 'Demande d\'heures supplémentaires enregistrée avec succès.');
    }

    /**
     * Affiche les détails d'une demande d'heures supplémentaires
     */
    public function show(Supplementaire $supplementaire)
    {
        $user = Auth::user();
        $isManager = $user->hasRole(['admin', 'manager', 'supervisor']);
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        // Vérifier si l'utilisateur a le droit de voir cette demande
        if (!$isManager && (!$employeur || $employeur->id !== $supplementaire->employeur_id)) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
        }
        
        $presence = Presence::where('supplementaire_id', $supplementaire->id)->first();
        
        return view('app.supplementaire.show', [
            'supplementaire' => $supplementaire,
            'presence' => $presence,
            'isManager' => $isManager
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une demande d'heures supplémentaires
     */
    public function edit(Supplementaire $supplementaire)
    {
        $user = Auth::user();
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        // Vérifier si l'utilisateur a le droit de modifier cette demande
        if ((!$employeur || $employeur->id !== $supplementaire->employeur_id) && !$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à modifier cette demande.');
        }
        
        // Vérifier si la demande est encore modifiable
        if ($supplementaire->statut !== 'en_attente') {
            return redirect()->route('supplementaires.show', $supplementaire->id)
                ->with('error', 'Cette demande ne peut plus être modifiée car elle a déjà été traitée.');
        }
        
        return view('app.supplementaire.edit', [
            'supplementaire' => $supplementaire
        ]);
    }

    /**
     * Met à jour une demande d'heures supplémentaires
     */
    public function update(Request $request, Supplementaire $supplementaire)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin' => 'required|date_format:H:i|after:heure_debut',
            'motif' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Vérifier si la demande est encore modifiable
        if ($supplementaire->statut !== 'en_attente') {
            return redirect()->route('supplementaires.show', $supplementaire->id)
                ->with('error', 'Cette demande ne peut plus être modifiée car elle a déjà été traitée.');
        }
        
        $date = Carbon::parse($request->date);
        $heureDebut = Carbon::parse($request->date . ' ' . $request->heure_debut);
        $heureFin = Carbon::parse($request->date . ' ' . $request->heure_fin);
        
        // Si l'heure de fin est avant l'heure de début, on suppose que c'est le jour suivant
        if ($heureFin->lt($heureDebut)) {
            $heureFin->addDay();
        }
        
        $nombreHeures = $heureDebut->diffInMinutes($heureFin) / 60;
        
        $supplementaire->update([
            'date' => $date,
            'heure_debut' => $heureDebut,
            'heure_fin' => $heureFin,
            'nombre_heures' => $nombreHeures,
            'motif' => $request->motif,
        ]);
        
        $supplementaire->calculerMontant();
        
        return redirect()->route('supplementaires.show', $supplementaire->id)
            ->with('success', 'Demande d\'heures supplémentaires mise à jour avec succès.');
    }

    /**
     * Supprime une demande d'heures supplémentaires
     */
    public function destroy(Supplementaire $supplementaire)
    {
        $user = Auth::user();
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        // Vérifier si l'utilisateur a le droit de supprimer cette demande
        if ((!$employeur || $employeur->id !== $supplementaire->employeur_id) && !$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à supprimer cette demande.');
        }
        
        // Vérifier si la demande est encore supprimable
        if ($supplementaire->statut !== 'en_attente') {
            return redirect()->route('supplementaires.show', $supplementaire->id)
                ->with('error', 'Cette demande ne peut plus être supprimée car elle a déjà été traitée.');
        }
        
        $supplementaire->delete();
        
        return redirect()->route('supplementaires.index')
            ->with('success', 'Demande d\'heures supplémentaires supprimée avec succès.');
    }

    /**
     * Valide une demande d'heures supplémentaires
     */
    public function valider(Supplementaire $supplementaire)
    {
        $this->authorize('valider', $supplementaire);

        $validateurId = auth()->user()->id;
        $commentaire = request('commentaire');

        $success = $this->heuresSupplementairesService->validerHeureSupplementaire(
            $supplementaire, 
            $validateurId, 
            $commentaire
        );

        if ($success) {
            return redirect()->route('supplementaires.show', $supplementaire->id)
                ->with('success', 'Les heures supplémentaires ont été validées avec succès.');
        }

        return redirect()->route('supplementaires.show', $supplementaire->id)
            ->with('error', 'Impossible de valider ces heures supplémentaires.');
    }

    /**
     * Rejette une demande d'heures supplémentaires
     */
    public function rejeter(Supplementaire $supplementaire)
    {
        $this->authorize('rejeter', $supplementaire);

        $validateurId = auth()->user()->id;
        $commentaire = request('commentaire');

        if (!$commentaire) {
            return redirect()->back()
                ->with('error', 'Un commentaire est requis pour rejeter des heures supplémentaires.')
                ->withInput();
        }

        $success = $this->heuresSupplementairesService->rejeterHeureSupplementaire(
            $supplementaire, 
            $validateurId, 
            $commentaire
        );

        if ($success) {
            return redirect()->route('supplementaires.show', $supplementaire->id)
                ->with('success', 'Les heures supplémentaires ont été rejetées.');
        }

        return redirect()->route('supplementaires.show', $supplementaire->id)
            ->with('error', 'Impossible de rejeter ces heures supplémentaires.');
    }

    /**
     * Affiche le tableau de bord des heures supplémentaires
     */
    public function dashboard()
    {
        $user = Auth::user();
        $isManager = $user->hasRole(['admin', 'manager', 'supervisor']);
        
        if ($isManager) {
            // Pour les managers, afficher les statistiques globales
            $enAttente = Supplementaire::enAttente()->count();
            $approuve = Supplementaire::approuve()->count();
            $rejete = Supplementaire::rejete()->count();
            
            $supplementairesRecents = Supplementaire::with(['employeur', 'validateur'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return view('app.supplementaire.dashboard', [
                'enAttente' => $enAttente,
                'approuve' => $approuve,
                'rejete' => $rejete,
                'supplementairesRecents' => $supplementairesRecents,
                'isManager' => $isManager
            ]);
        } else {
            // Pour les employés, afficher leurs propres statistiques
            $employeur = Employeur::where('user_id', $user->id)->first();
            
            if (!$employeur) {
                return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas associé à un employeur.');
            }
            
            $query = Supplementaire::where('employeur_id', $employeur->id);
            
            $enAttente = (clone $query)->enAttente()->count();
            $approuve = (clone $query)->approuve()->count();
            $rejete = (clone $query)->rejete()->count();
            
            $supplementairesRecents = (clone $query)->with('validateur')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return view('app.supplementaire.dashboard', [
                'enAttente' => $enAttente,
                'approuve' => $approuve,
                'rejete' => $rejete,
                'supplementairesRecents' => $supplementairesRecents,
                'isManager' => $isManager
            ]);
        }
    }
}
