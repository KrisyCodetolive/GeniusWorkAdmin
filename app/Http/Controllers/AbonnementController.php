<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\PlanAbonnement;
use App\Services\AbonnementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AbonnementController extends Controller
{
    protected $abonnementService;
    
    public function __construct(AbonnementService $abonnementService)
    {
        $this->abonnementService = $abonnementService;
        $this->middleware(['auth', 'verified']);
    }
    
    /**
     * Afficher la liste des abonnements d'une entreprise
     *
     * @param Entreprise $entreprise
     * @return \Illuminate\View\View
     */
    public function index(Entreprise $entreprise)
    {
        $this->authorize('viewAny', [Abonnement::class, $entreprise]);
        
        $abonnements = $entreprise->abonnements()->with('planAbonnement')->latest()->get();
        $abonnementActif = $this->abonnementService->getAbonnementActif($entreprise);
        
        return view('app.abonnement.index', compact('entreprise', 'abonnements', 'abonnementActif'));
    }
    
    /**
     * Afficher le formulaire de création d'un abonnement
     *
     * @param Entreprise $entreprise
     * @return \Illuminate\View\View
     */
    public function create(Entreprise $entreprise)
    {
        $this->authorize('create', [Abonnement::class, $entreprise]);
        
        $plansAbonnement = PlanAbonnement::actif()->parPriorite()->get();
        
        return view('app.abonnement.create', compact('entreprise', 'plansAbonnement'));
    }
    
    /**
     * Enregistrer un nouvel abonnement
     *
     * @param Request $request
     * @param Entreprise $entreprise
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Entreprise $entreprise)
    {
        $this->authorize('create', [Abonnement::class, $entreprise]);
        
        $validated = $request->validate([
            'plan_abonnement_id' => 'required|exists:plan_abonnements,id',
            'date_debut' => 'nullable|date',
            'type_periode' => 'required|in:mensuel,annuel,essai',
            'code_promo' => 'nullable|string|max:50',
            'mode_paiement' => 'nullable|string|max:50',
            'reference_paiement' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'renouvellement_automatique' => 'boolean',
            'facture_automatique' => 'boolean',
        ]);
        
        $planAbonnement = PlanAbonnement::findOrFail($validated['plan_abonnement_id']);
        
        try {
            $abonnement = $this->abonnementService->creerAbonnement($entreprise, $planAbonnement, $validated);
            
            return redirect()->route('abonnements.show', [$entreprise, $abonnement])
                ->with('success', 'Abonnement créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la création de l\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher les détails d'un abonnement
     *
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\View\View
     */
    public function show(Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('view', [$abonnement, $entreprise]);
        
        $abonnement->load('planAbonnement', 'codePromo', 'facturations');
        
        return view('app.abonnement.show', compact('entreprise', 'abonnement'));
    }
    
    /**
     * Afficher le formulaire de modification d'un abonnement
     *
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\View\View
     */
    public function edit(Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        $plansAbonnement = PlanAbonnement::actif()->parPriorite()->get();
        
        return view('app.abonnement.edit', compact('entreprise', 'abonnement', 'plansAbonnement'));
    }
    
    /**
     * Mettre à jour un abonnement
     *
     * @param Request $request
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        $validated = $request->validate([
            'plan_abonnement_id' => 'required|exists:plan_abonnements,id',
            'type_periode' => 'required|in:mensuel,annuel,essai',
            'code_promo' => 'nullable|string|max:50',
            'mode_paiement' => 'nullable|string|max:50',
            'reference_paiement' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'renouvellement_automatique' => 'boolean',
            'facture_automatique' => 'boolean',
        ]);
        
        $planAbonnement = PlanAbonnement::findOrFail($validated['plan_abonnement_id']);
        
        try {
            if ($abonnement->plan_abonnement_id != $planAbonnement->id) {
                // Changement de plan
                $this->abonnementService->changerPlanAbonnement($abonnement, $planAbonnement, $validated);
            } else {
                // Mise à jour simple
                $abonnement->update($validated);
            }
            
            return redirect()->route('abonnements.show', [$entreprise, $abonnement])
                ->with('success', 'Abonnement mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour de l\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Activer un abonnement
     *
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activer(Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        try {
            $this->abonnementService->changerStatutAbonnement($abonnement, true);
            
            return redirect()->route('abonnements.show', [$entreprise, $abonnement])
                ->with('success', 'Abonnement activé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'activation de l\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Désactiver un abonnement
     *
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function desactiver(Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        try {
            $this->abonnementService->changerStatutAbonnement($abonnement, false);
            
            return redirect()->route('abonnements.show', [$entreprise, $abonnement])
                ->with('success', 'Abonnement désactivé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la désactivation de l\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher le formulaire de renouvellement d'un abonnement
     *
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\View\View
     */
    public function renouvelerForm(Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        return view('app.abonnement.renouveler', compact('entreprise', 'abonnement'));
    }
    
    /**
     * Renouveler un abonnement
     *
     * @param Request $request
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function renouveler(Request $request, Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        $validated = $request->validate([
            'date_debut' => 'nullable|date',
            'type_periode' => 'required|in:mensuel,annuel',
            'code_promo' => 'nullable|string|max:50',
            'mode_paiement' => 'nullable|string|max:50',
            'reference_paiement' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $this->abonnementService->renouvelerAbonnement($abonnement, $validated);
            
            return redirect()->route('abonnements.show', [$entreprise, $abonnement])
                ->with('success', 'Abonnement renouvelé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors du renouvellement de l\'abonnement: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher le formulaire de changement de plan
     *
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\View\View
     */
    public function changerPlanForm(Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        $plansAbonnement = PlanAbonnement::actif()->parPriorite()->get();
        
        return view('app.abonnement.changer_plan', compact('entreprise', 'abonnement', 'plansAbonnement'));
    }
    
    /**
     * Changer le plan d'un abonnement
     *
     * @param Request $request
     * @param Entreprise $entreprise
     * @param Abonnement $abonnement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changerPlan(Request $request, Entreprise $entreprise, Abonnement $abonnement)
    {
        $this->authorize('update', [$abonnement, $entreprise]);
        
        $validated = $request->validate([
            'plan_abonnement_id' => 'required|exists:plan_abonnements,id',
            'type_periode' => 'required|in:mensuel,annuel',
            'code_promo' => 'nullable|string|max:50',
        ]);
        
        $planAbonnement = PlanAbonnement::findOrFail($validated['plan_abonnement_id']);
        
        try {
            $this->abonnementService->changerPlanAbonnement($abonnement, $planAbonnement, $validated);
            
            return redirect()->route('abonnements.show', [$entreprise, $abonnement])
                ->with('success', 'Plan d\'abonnement changé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors du changement de plan: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher le tableau de bord des abonnements pour l'administrateur
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $this->authorize('viewAny', Abonnement::class);
        
        $abonnementsActifs = Abonnement::actif()->with('entreprise', 'planAbonnement')->get();
        $abonnementsExpires = Abonnement::expire()->with('entreprise', 'planAbonnement')->get();
        $abonnementsExpirantBientot = $this->abonnementService->getAbonnementsExpirantBientot();
        
        $statistiques = [
            'total_actifs' => $abonnementsActifs->count(),
            'total_expires' => $abonnementsExpires->count(),
            'expirant_bientot' => $abonnementsExpirantBientot->count(),
            'revenus_mensuels' => $abonnementsActifs->where('type_periode', 'mensuel')->sum('montant'),
            'revenus_annuels' => $abonnementsActifs->where('type_periode', 'annuel')->sum('montant'),
        ];
        
        return view('app.abonnement.dashboard', compact('abonnementsActifs', 'abonnementsExpires', 'abonnementsExpirantBientot', 'statistiques'));
    }
}
