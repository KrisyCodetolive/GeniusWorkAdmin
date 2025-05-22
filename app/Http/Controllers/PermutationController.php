<?php

namespace App\Http\Controllers;

use App\Models\Permutation;
use App\Models\Employeur;
use App\Models\PlageHoraire;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PermutationController extends Controller
{
    /**
     * Affiche la liste des permutations
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasRole(['admin', 'manager', 'supervisor']);
        
        $query = Permutation::with(['employeur1', 'employeur2', 'plageHoraire1', 'plageHoraire2', 'validateur']);
        
        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->periode($request->date_debut, $request->date_fin);
        }
        
        // Si l'utilisateur n'est pas un manager, il ne voit que ses propres permutations
        if (!$isManager) {
            $employeur = Employeur::where('user_id', $user->id)->first();
            if ($employeur) {
                $query->parEmployeur($employeur->id);
            } else {
                return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas associé à un employeur.');
            }
        } elseif ($request->has('employeur_id')) {
            $query->parEmployeur($request->employeur_id);
        }
        
        $permutations = $query->orderBy('date', 'desc')->paginate(15);
        
        return view('app.permutation.index', [
            'permutations' => $permutations,
            'isManager' => $isManager
        ]);
    }

    /**
     * Affiche le formulaire de création de permutation
     */
    public function create()
    {
        $user = Auth::user();
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        if (!$employeur && !$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à créer des permutations.');
        }
        
        $employeurs = Employeur::where('id', '!=', $employeur->id ?? null)->get();
        $plagesHoraires = PlageHoraire::all();
        
        return view('app.permutation.create', [
            'employeur' => $employeur,
            'employeurs' => $employeurs,
            'plagesHoraires' => $plagesHoraires
        ]);
    }

    /**
     * Enregistre une nouvelle demande de permutation
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employeur_1_id' => 'required|exists:employeurs,id',
            'employeur_2_id' => 'required|exists:employeurs,id|different:employeur_1_id',
            'plage_horaire_1_id' => 'required|exists:plage_horaires,id',
            'plage_horaire_2_id' => 'required|exists:plage_horaires,id',
            'date' => 'required|date|after_or_equal:today',
            'motif' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $permutation = new Permutation([
            'employeur_1_id' => $request->employeur_1_id,
            'employeur_2_id' => $request->employeur_2_id,
            'plage_horaire_1_id' => $request->plage_horaire_1_id,
            'plage_horaire_2_id' => $request->plage_horaire_2_id,
            'date' => Carbon::parse($request->date),
            'motif' => $request->motif,
            'statut' => 'en_attente'
        ]);
        
        $permutation->save();
        
        return redirect()->route('permutations.show', $permutation->id)
            ->with('success', 'Demande de permutation enregistrée avec succès.');
    }

    /**
     * Affiche les détails d'une demande de permutation
     */
    public function show(Permutation $permutation)
    {
        $user = Auth::user();
        $isManager = $user->hasRole(['admin', 'manager', 'supervisor']);
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        // Vérifier si l'utilisateur a le droit de voir cette demande
        if (!$isManager && (!$employeur || !$permutation->concerne($employeur))) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à voir cette demande.');
        }
        
        return view('app.permutation.show', [
            'permutation' => $permutation,
            'isManager' => $isManager
        ]);
    }

    /**
     * Affiche le formulaire de modification d'une demande de permutation
     */
    public function edit(Permutation $permutation)
    {
        $user = Auth::user();
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        // Vérifier si l'utilisateur a le droit de modifier cette demande
        if ((!$employeur || !$permutation->concerne($employeur)) && !$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à modifier cette demande.');
        }
        
        // Vérifier si la demande est encore modifiable
        if ($permutation->statut !== 'en_attente') {
            return redirect()->route('permutations.show', $permutation->id)
                ->with('error', 'Cette demande ne peut plus être modifiée car elle a déjà été traitée.');
        }
        
        $employeurs = Employeur::all();
        $plagesHoraires = PlageHoraire::all();
        
        return view('app.permutation.edit', [
            'permutation' => $permutation,
            'employeurs' => $employeurs,
            'plagesHoraires' => $plagesHoraires
        ]);
    }

    /**
     * Met à jour une demande de permutation
     */
    public function update(Request $request, Permutation $permutation)
    {
        $validator = Validator::make($request->all(), [
            'employeur_1_id' => 'required|exists:employeurs,id',
            'employeur_2_id' => 'required|exists:employeurs,id|different:employeur_1_id',
            'plage_horaire_1_id' => 'required|exists:plage_horaires,id',
            'plage_horaire_2_id' => 'required|exists:plage_horaires,id',
            'date' => 'required|date|after_or_equal:today',
            'motif' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Vérifier si la demande est encore modifiable
        if ($permutation->statut !== 'en_attente') {
            return redirect()->route('permutations.show', $permutation->id)
                ->with('error', 'Cette demande ne peut plus être modifiée car elle a déjà été traitée.');
        }
        
        $permutation->update([
            'employeur_1_id' => $request->employeur_1_id,
            'employeur_2_id' => $request->employeur_2_id,
            'plage_horaire_1_id' => $request->plage_horaire_1_id,
            'plage_horaire_2_id' => $request->plage_horaire_2_id,
            'date' => Carbon::parse($request->date),
            'motif' => $request->motif,
        ]);
        
        return redirect()->route('permutations.show', $permutation->id)
            ->with('success', 'Demande de permutation mise à jour avec succès.');
    }

    /**
     * Supprime une demande de permutation
     */
    public function destroy(Permutation $permutation)
    {
        $user = Auth::user();
        $employeur = Employeur::where('user_id', $user->id)->first();
        
        // Vérifier si l'utilisateur a le droit de supprimer cette demande
        if ((!$employeur || !$permutation->concerne($employeur)) && !$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à supprimer cette demande.');
        }
        
        // Vérifier si la demande est encore supprimable
        if ($permutation->statut !== 'en_attente') {
            return redirect()->route('permutations.show', $permutation->id)
                ->with('error', 'Cette demande ne peut plus être supprimée car elle a déjà été traitée.');
        }
        
        $permutation->delete();
        
        return redirect()->route('permutations.index')
            ->with('success', 'Demande de permutation supprimée avec succès.');
    }

    /**
     * Valide une demande de permutation
     */
    public function valider(Request $request, Permutation $permutation)
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a le droit de valider cette demande
        if (!$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à valider cette demande.');
        }
        
        // Vérifier si la demande est encore validable
        if ($permutation->statut !== 'en_attente') {
            return redirect()->route('permutations.show', $permutation->id)
                ->with('error', 'Cette demande a déjà été traitée.');
        }
        
        $permutation->valider($user, $request->commentaire);
        
        return redirect()->route('permutations.show', $permutation->id)
            ->with('success', 'Demande de permutation validée avec succès.');
    }

    /**
     * Rejette une demande de permutation
     */
    public function rejeter(Request $request, Permutation $permutation)
    {
        $validator = Validator::make($request->all(), [
            'commentaire' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a le droit de rejeter cette demande
        if (!$user->hasRole(['admin', 'manager', 'supervisor'])) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas autorisé à rejeter cette demande.');
        }
        
        // Vérifier si la demande est encore rejettable
        if ($permutation->statut !== 'en_attente') {
            return redirect()->route('permutations.show', $permutation->id)
                ->with('error', 'Cette demande a déjà été traitée.');
        }
        
        $permutation->rejeter($user, $request->commentaire);
        
        return redirect()->route('permutations.show', $permutation->id)
            ->with('success', 'Demande de permutation rejetée.');
    }

    /**
     * Affiche le tableau de bord des permutations
     */
    public function dashboard()
    {
        $user = Auth::user();
        $isManager = $user->hasRole(['admin', 'manager', 'supervisor']);
        
        if ($isManager) {
            // Pour les managers, afficher les statistiques globales
            $enAttente = Permutation::enAttente()->count();
            $approuve = Permutation::approuve()->count();
            $rejete = Permutation::rejete()->count();
            
            $permutationsRecentes = Permutation::with(['employeur1', 'employeur2', 'plageHoraire1', 'plageHoraire2', 'validateur'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return view('app.permutation.dashboard', [
                'enAttente' => $enAttente,
                'approuve' => $approuve,
                'rejete' => $rejete,
                'permutationsRecentes' => $permutationsRecentes,
                'isManager' => $isManager
            ]);
        } else {
            // Pour les employés, afficher leurs propres statistiques
            $employeur = Employeur::where('user_id', $user->id)->first();
            
            if (!$employeur) {
                return redirect()->route('dashboard')->with('error', 'Vous n\'êtes pas associé à un employeur.');
            }
            
            $query = Permutation::parEmployeur($employeur->id);
            
            $enAttente = (clone $query)->enAttente()->count();
            $approuve = (clone $query)->approuve()->count();
            $rejete = (clone $query)->rejete()->count();
            
            $permutationsRecentes = (clone $query)->with(['employeur1', 'employeur2', 'plageHoraire1', 'plageHoraire2', 'validateur'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return view('app.permutation.dashboard', [
                'enAttente' => $enAttente,
                'approuve' => $approuve,
                'rejete' => $rejete,
                'permutationsRecentes' => $permutationsRecentes,
                'isManager' => $isManager
            ]);
        }
    }
}
