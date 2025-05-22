<?php

namespace App\Http\Controllers;

use App\Models\PlanAbonnement;
use App\Services\PlanAbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PlanAbonnementController extends Controller
{
    protected $planAbonnementService;
    
    public function __construct(PlanAbonnementService $planAbonnementService)
    {
        $this->planAbonnementService = $planAbonnementService;
        $this->middleware(['auth', 'verified']);
    }
    
    /**
     * Afficher la liste des plans d'abonnement
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('viewAny', PlanAbonnement::class);
        
        $plansAbonnement = PlanAbonnement::parPriorite()->get();
        
        return view('app.plan_abonnement.index', compact('plansAbonnement'));
    }
    
    /**
     * Afficher le formulaire de création d'un plan d'abonnement
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', PlanAbonnement::class);
        
        return view('app.plan_abonnement.create');
    }
    
    /**
     * Enregistrer un nouveau plan d'abonnement
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', PlanAbonnement::class);
        
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'prix_mensuel' => 'required|numeric|min:0',
            'prix_annuel' => 'required|numeric|min:0',
            'duree_essai' => 'nullable|integer|min:0',
            'nombre_employes_min' => 'required|integer|min:0',
            'nombre_employes_max' => 'required|integer|min:0',
            'cout_par_employe' => 'nullable|numeric|min:0',
            'fonctionnalites' => 'nullable|array',
            'statut' => 'required|in:actif,inactif',
            'devise' => 'required|string|max:10',
            'priorite' => 'nullable|integer|min:0',
            'periode_facturation' => 'nullable|string|max:50'
        ]);
        
        try {
            $planAbonnement = PlanAbonnement::create($validated);
            
            return redirect()->route('plan-abonnements.show', $planAbonnement)
                ->with('success', 'Plan d\'abonnement créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la création du plan d\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher les détails d'un plan d'abonnement
     *
     * @param PlanAbonnement $planAbonnement
     * @return \Illuminate\View\View
     */
    public function show(PlanAbonnement $planAbonnement)
    {
        $this->authorize('view', $planAbonnement);
        
        $abonnements = $planAbonnement->abonnements()->with('entreprise')->get();
        $entreprisesActives = $planAbonnement->entreprisesActives;
        
        return view('app.plan_abonnement.show', compact('planAbonnement', 'abonnements', 'entreprisesActives'));
    }
    
    /**
     * Afficher le formulaire de modification d'un plan d'abonnement
     *
     * @param PlanAbonnement $planAbonnement
     * @return \Illuminate\View\View
     */
    public function edit(PlanAbonnement $planAbonnement)
    {
        $this->authorize('update', $planAbonnement);
        
        return view('app.plan_abonnement.edit', compact('planAbonnement'));
    }
    
    /**
     * Mettre à jour un plan d'abonnement
     *
     * @param Request $request
     * @param PlanAbonnement $planAbonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PlanAbonnement $planAbonnement)
    {
        $this->authorize('update', $planAbonnement);
        
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'description' => 'nullable|string',
            'prix_mensuel' => 'required|numeric|min:0',
            'prix_annuel' => 'required|numeric|min:0',
            'duree_essai' => 'nullable|integer|min:0',
            'nombre_employes_min' => 'required|integer|min:0',
            'nombre_employes_max' => 'required|integer|min:0',
            'cout_par_employe' => 'nullable|numeric|min:0',
            'fonctionnalites' => 'nullable|array',
            'statut' => 'required|in:actif,inactif',
            'devise' => 'required|string|max:10',
            'priorite' => 'nullable|integer|min:0',
            'periode_facturation' => 'nullable|string|max:50'
        ]);
        
        try {
            $planAbonnement->update($validated);
            
            return redirect()->route('plan-abonnements.show', $planAbonnement)
                ->with('success', 'Plan d\'abonnement mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du plan d\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Supprimer un plan d'abonnement
     *
     * @param PlanAbonnement $planAbonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PlanAbonnement $planAbonnement)
    {
        $this->authorize('delete', $planAbonnement);
        
        // Vérifier si le plan a des abonnements actifs
        if ($planAbonnement->abonnements()->actif()->exists()) {
            return back()->with('error', 'Ce plan d\'abonnement ne peut pas être supprimé car il est utilisé par des abonnements actifs.');
        }
        
        try {
            $planAbonnement->delete();
            
            return redirect()->route('plan-abonnements.index')
                ->with('success', 'Plan d\'abonnement supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du plan d\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Comparer deux plans d'abonnement
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function comparer(Request $request)
    {
        $this->authorize('viewAny', PlanAbonnement::class);
        
        $validated = $request->validate([
            'plan_1' => 'required|exists:plan_abonnements,id',
            'plan_2' => 'required|exists:plan_abonnements,id',
        ]);
        
        $plan1 = PlanAbonnement::findOrFail($validated['plan_1']);
        $plan2 = PlanAbonnement::findOrFail($validated['plan_2']);
        
        $comparaison = $this->planAbonnementService->comparerFonctionnalites($plan1, $plan2);
        
        return view('app.plan_abonnement.comparer', compact('plan1', 'plan2', 'comparaison'));
    }
}
