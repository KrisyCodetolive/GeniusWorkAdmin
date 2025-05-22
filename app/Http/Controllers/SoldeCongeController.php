<?php

namespace App\Http\Controllers;

use App\Models\SoldeConge;
use App\Models\TypeConge;
use App\Models\User;
use App\Services\CongeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SoldeCongeController extends Controller
{
    protected $congeService;

    public function __construct(CongeService $congeService)
    {
        $this->congeService = $congeService;
        $this->middleware('auth');
        $this->middleware('can:manage,App\Models\SoldeConge');
    }

    /**
     * Afficher la liste des soldes de congés
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        $annee = $request->input('annee', date('Y'));
        $typeCongeId = $request->input('type_conge_id');
        
        $query = SoldeConge::whereHas('user.employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->with(['user', 'typeConge'])
            ->where('annee', $annee);
            
        if ($typeCongeId) {
            $query->where('type_conge_id', $typeCongeId);
        }
        
        $soldes = $query->paginate(15);
        
        $typesConge = TypeConge::where(function($query) use ($entrepriseId) {
                $query->whereNull('entreprise_id')
                    ->orWhere('entreprise_id', $entrepriseId);
            })
            ->actif()
            ->orderBy('nom')
            ->get();
            
        return view('app.conge.soldes.index', compact('soldes', 'typesConge', 'annee'));
    }

    /**
     * Afficher le formulaire d'attribution de solde
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        $users = User::whereHas('employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->orderBy('name')
            ->get();
            
        $typesConge = TypeConge::where(function($query) use ($entrepriseId) {
                $query->whereNull('entreprise_id')
                    ->orWhere('entreprise_id', $entrepriseId);
            })
            ->actif()
            ->where('deductible_solde', true)
            ->orderBy('nom')
            ->get();
            
        $annees = range(date('Y') - 1, date('Y') + 1);
        
        return view('app.conge.soldes.create', compact('users', 'typesConge', 'annees'));
    }

    /**
     * Enregistrer un nouveau solde de congé
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type_conge_id' => 'required|exists:type_conges,id',
            'annee' => 'required|integer|min:2000|max:2100',
            'solde' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string',
        ]);

        try {
            $this->congeService->initialiserSoldeConge(
                $request->user_id,
                $request->type_conge_id,
                $request->annee,
                $request->solde,
                $request->commentaire
            );
            
            return redirect()->route('solde-conge.index', ['annee' => $request->annee])
                ->with('success', 'Le solde de congé a été attribué avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'ajustement de solde
     *
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\View\View
     */
    public function edit(SoldeConge $soldeConge)
    {
        return view('app.conge.soldes.edit', compact('soldeConge'));
    }

    /**
     * Mettre à jour un solde de congé
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, SoldeConge $soldeConge)
    {
        $request->validate([
            'operation' => 'required|in:ajouter,reinitialiser',
            'jours' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string',
        ]);

        try {
            if ($request->operation === 'ajouter') {
                $soldeConge->ajouterSolde($request->jours, $request->commentaire);
            } else {
                $soldeConge->reinitialiserSolde($request->jours, $request->commentaire);
            }
            
            return redirect()->route('solde-conge.index', ['annee' => $soldeConge->annee])
                ->with('success', 'Le solde de congé a été mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'attribution en masse
     *
     * @return \Illuminate\View\View
     */
    public function createMasse()
    {
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        $typesConge = TypeConge::where(function($query) use ($entrepriseId) {
                $query->whereNull('entreprise_id')
                    ->orWhere('entreprise_id', $entrepriseId);
            })
            ->actif()
            ->where('deductible_solde', true)
            ->orderBy('nom')
            ->get();
            
        $annees = range(date('Y') - 1, date('Y') + 1);
        
        return view('app.conge.soldes.create-masse', compact('typesConge', 'annees'));
    }

    /**
     * Enregistrer une attribution en masse
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeMasse(Request $request)
    {
        $request->validate([
            'type_conge_id' => 'required|exists:type_conges,id',
            'annee' => 'required|integer|min:2000|max:2100',
            'solde' => 'required|numeric|min:0',
            'commentaire' => 'nullable|string',
        ]);

        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        $users = User::whereHas('employeur', function($query) use ($entrepriseId) {
                $query->where('entreprise_id', $entrepriseId);
            })
            ->get();
            
        $count = 0;
        
        foreach ($users as $user) {
            try {
                $this->congeService->initialiserSoldeConge(
                    $user->id,
                    $request->type_conge_id,
                    $request->annee,
                    $request->solde,
                    $request->commentaire
                );
                $count++;
            } catch (\Exception $e) {
                // Continuer malgré les erreurs
                continue;
            }
        }
        
        return redirect()->route('solde-conge.index', ['annee' => $request->annee])
            ->with('success', "Le solde de congé a été attribué à {$count} utilisateurs avec succès.");
    }

    /**
     * Afficher l'historique des ajustements d'un solde
     *
     * @param  \App\Models\SoldeConge  $soldeConge
     * @return \Illuminate\View\View
     */
    public function historique(SoldeConge $soldeConge)
    {
        // On suppose que l'historique est stocké dans le champ commentaire
        // Dans une implémentation plus avancée, on pourrait avoir une table dédiée
        
        return view('app.conge.soldes.historique', compact('soldeConge'));
    }
}
