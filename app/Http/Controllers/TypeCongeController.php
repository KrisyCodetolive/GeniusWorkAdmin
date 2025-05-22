<?php

namespace App\Http\Controllers;

use App\Models\TypeConge;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TypeCongeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage,App\Models\TypeConge')->except(['index', 'show']);
    }

    /**
     * Afficher la liste des types de congés
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $entrepriseId = $user->employeur->entreprise_id;
        
        $typesConge = TypeConge::where(function($query) use ($entrepriseId) {
                $query->whereNull('entreprise_id')
                    ->orWhere('entreprise_id', $entrepriseId);
            })
            ->orderBy('nom')
            ->paginate(10);
            
        return view('app.conge.types.index', compact('typesConge'));
    }

    /**
     * Afficher le formulaire de création d'un type de congé
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = Auth::user();
        $entreprises = Entreprise::where('id', $user->employeur->entreprise_id)->get();
        
        return view('app.conge.types.create', compact('entreprises'));
    }

    /**
     * Enregistrer un nouveau type de congé
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duree_max_annuelle' => 'nullable|numeric|min:0',
            'necessite_justificatif' => 'boolean',
            'est_paye' => 'boolean',
            'deductible_solde' => 'boolean',
            'delai_demande_prealable' => 'nullable|integer|min:0',
            'conditions_eligibilite' => 'nullable|json',
            'configuration' => 'nullable|json',
            'statut' => 'required|in:actif,inactif',
        ]);

        TypeConge::create($request->all());
        
        return redirect()->route('type-conge.index')
            ->with('success', 'Le type de congé a été créé avec succès.');
    }

    /**
     * Afficher les détails d'un type de congé
     *
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\View\View
     */
    public function show(TypeConge $typeConge)
    {
        $this->authorize('view', $typeConge);
        
        return view('app.conge.types.show', compact('typeConge'));
    }

    /**
     * Afficher le formulaire d'édition d'un type de congé
     *
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\View\View
     */
    public function edit(TypeConge $typeConge)
    {
        $this->authorize('update', $typeConge);
        
        $user = Auth::user();
        $entreprises = Entreprise::where('id', $user->employeur->entreprise_id)->get();
        
        return view('app.conge.types.edit', compact('typeConge', 'entreprises'));
    }

    /**
     * Mettre à jour un type de congé
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TypeConge $typeConge)
    {
        $this->authorize('update', $typeConge);
        
        $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'duree_max_annuelle' => 'nullable|numeric|min:0',
            'necessite_justificatif' => 'boolean',
            'est_paye' => 'boolean',
            'deductible_solde' => 'boolean',
            'delai_demande_prealable' => 'nullable|integer|min:0',
            'conditions_eligibilite' => 'nullable|json',
            'configuration' => 'nullable|json',
            'statut' => 'required|in:actif,inactif',
        ]);

        $typeConge->update($request->all());
        
        return redirect()->route('type-conge.index')
            ->with('success', 'Le type de congé a été mis à jour avec succès.');
    }

    /**
     * Supprimer un type de congé
     *
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TypeConge $typeConge)
    {
        $this->authorize('delete', $typeConge);
        
        // Vérifier si le type de congé est utilisé
        if ($typeConge->conges()->exists()) {
            return back()->with('error', 'Ce type de congé ne peut pas être supprimé car il est utilisé par des demandes de congé.');
        }
        
        $typeConge->delete();
        
        return redirect()->route('type-conge.index')
            ->with('success', 'Le type de congé a été supprimé avec succès.');
    }

    /**
     * Activer/désactiver un type de congé
     *
     * @param  \App\Models\TypeConge  $typeConge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatut(TypeConge $typeConge)
    {
        $this->authorize('update', $typeConge);
        
        $typeConge->update([
            'statut' => $typeConge->statut === 'actif' ? 'inactif' : 'actif'
        ]);
        
        $message = $typeConge->statut === 'actif' 
            ? 'Le type de congé a été activé avec succès.' 
            : 'Le type de congé a été désactivé avec succès.';
            
        return back()->with('success', $message);
    }
}
