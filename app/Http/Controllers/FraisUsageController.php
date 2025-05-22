<?php

namespace App\Http\Controllers;

use App\Models\FraisUsage;
use App\Models\Entreprise;
use App\Models\Facturation;
use App\Services\FacturationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FraisUsageController extends Controller
{
    protected $facturationService;
    
    public function __construct(FacturationService $facturationService)
    {
        $this->facturationService = $facturationService;
        $this->middleware(['auth', 'verified']);
    }
    
    /**
     * Afficher la liste des frais d'usage
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $request->input('entreprise_id');
        $type = $request->input('type');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $statut = $request->input('statut');
        
        $query = FraisUsage::with(['entreprise', 'facturation']);
        
        // Filtrer par entreprise si spécifié
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        } else if (!$user->hasRole('admin')) {
            // Si l'utilisateur n'est pas admin, limiter aux frais de son entreprise
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        // Filtrer par type de frais
        if ($type) {
            $query->where('type_frais', $type);
        }
        
        // Filtrer par période
        if ($dateDebut) {
            $query->where('periode_debut', '>=', Carbon::parse($dateDebut));
        }
        
        if ($dateFin) {
            $query->where('periode_fin', '<=', Carbon::parse($dateFin));
        }
        
        // Filtrer par statut (facturé ou non)
        if ($statut === 'facture') {
            $query->whereNotNull('facturation_id');
        } else if ($statut === 'non_facture') {
            $query->whereNull('facturation_id');
        }
        
        $fraisUsages = $query->latest()->paginate(15);
        $entreprises = Entreprise::all();
        $typeFrais = FraisUsage::select('type_frais')->distinct()->pluck('type_frais');
        
        return view('app.frais_usage.index', compact('fraisUsages', 'entreprises', 'typeFrais'));
    }
    
    /**
     * Afficher le formulaire de création d'un frais d'usage
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', FraisUsage::class);
        
        $entreprises = Entreprise::all();
        
        return view('app.frais_usage.create', compact('entreprises'));
    }
    
    /**
     * Enregistrer un nouveau frais d'usage
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', FraisUsage::class);
        
        $validated = $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'type_frais' => 'required|string|max:50',
            'description' => 'required|string',
            'quantite' => 'required|numeric|min:0',
            'prix_unitaire' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after:periode_debut',
            'devise' => 'required|string|max:3',
        ]);
        
        try {
            // Calculer le montant total
            $validated['montant_total'] = $validated['quantite'] * $validated['prix_unitaire'];
            $validated['statut'] = 'non_facture';
            
            $fraisUsage = FraisUsage::create($validated);
            
            return redirect()->route('frais-usages.show', $fraisUsage)
                ->with('success', 'Frais d\'usage créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la création du frais d\'usage: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher les détails d'un frais d'usage
     *
     * @param FraisUsage $fraisUsage
     * @return \Illuminate\View\View
     */
    public function show(FraisUsage $fraisUsage)
    {
        $this->authorize('view', $fraisUsage);
        
        return view('app.frais_usage.show', compact('fraisUsage'));
    }
    
    /**
     * Afficher le formulaire de modification d'un frais d'usage
     *
     * @param FraisUsage $fraisUsage
     * @return \Illuminate\View\View
     */
    public function edit(FraisUsage $fraisUsage)
    {
        $this->authorize('update', $fraisUsage);
        
        $entreprises = Entreprise::all();
        
        return view('app.frais_usage.edit', compact('fraisUsage', 'entreprises'));
    }
    
    /**
     * Mettre à jour un frais d'usage
     *
     * @param Request $request
     * @param FraisUsage $fraisUsage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, FraisUsage $fraisUsage)
    {
        $this->authorize('update', $fraisUsage);
        
        // Vérifier si le frais est déjà facturé
        if ($fraisUsage->facturation_id) {
            return back()->with('error', 'Impossible de modifier un frais d\'usage déjà facturé.');
        }
        
        $validated = $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
            'type_frais' => 'required|string|max:50',
            'description' => 'required|string',
            'quantite' => 'required|numeric|min:0',
            'prix_unitaire' => 'required|numeric|min:0',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after:periode_debut',
            'devise' => 'required|string|max:3',
        ]);
        
        try {
            // Calculer le montant total
            $validated['montant_total'] = $validated['quantite'] * $validated['prix_unitaire'];
            
            $fraisUsage->update($validated);
            
            return redirect()->route('frais-usages.show', $fraisUsage)
                ->with('success', 'Frais d\'usage mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du frais d\'usage: ' . $e->getMessage());
        }
    }
    
    /**
     * Supprimer un frais d'usage
     *
     * @param FraisUsage $fraisUsage
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(FraisUsage $fraisUsage)
    {
        $this->authorize('delete', $fraisUsage);
        
        // Vérifier si le frais est déjà facturé
        if ($fraisUsage->facturation_id) {
            return back()->with('error', 'Impossible de supprimer un frais d\'usage déjà facturé.');
        }
        
        try {
            $fraisUsage->delete();
            
            return redirect()->route('frais-usages.index')
                ->with('success', 'Frais d\'usage supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du frais d\'usage: ' . $e->getMessage());
        }
    }
    
    /**
     * Facturer les frais d'usage sélectionnés
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function facturer(Request $request)
    {
        $this->authorize('create', Facturation::class);
        
        $validated = $request->validate([
            'frais_ids' => 'required|array',
            'frais_ids.*' => 'exists:frais_usages,id',
            'facturation_id' => 'nullable|exists:facturations,id',
        ]);
        
        try {
            $fraisIds = $validated['frais_ids'];
            $facturationId = $validated['facturation_id'] ?? null;
            
            // Récupérer les frais d'usage
            $fraisUsages = FraisUsage::whereIn('id', $fraisIds)
                ->whereNull('facturation_id')
                ->get();
            
            if ($fraisUsages->isEmpty()) {
                return back()->with('error', 'Aucun frais d\'usage valide à facturer.');
            }
            
            // Vérifier que tous les frais appartiennent à la même entreprise
            $entrepriseId = $fraisUsages->first()->entreprise_id;
            if ($fraisUsages->pluck('entreprise_id')->unique()->count() > 1) {
                return back()->with('error', 'Les frais d\'usage doivent appartenir à la même entreprise.');
            }
            
            // Si une facture existante est spécifiée
            if ($facturationId) {
                $facture = Facturation::findOrFail($facturationId);
                
                // Vérifier que la facture appartient à la même entreprise
                if ($facture->entreprise_id !== $entrepriseId) {
                    return back()->with('error', 'La facture sélectionnée n\'appartient pas à la même entreprise que les frais d\'usage.');
                }
                
                // Ajouter les frais à la facture existante
                $fraisArray = $fraisUsages->map(function ($frais) {
                    return [
                        'type_frais' => $frais->type_frais,
                        'description' => $frais->description,
                        'quantite' => $frais->quantite,
                        'prix_unitaire' => $frais->prix_unitaire,
                        'periode_debut' => $frais->periode_debut,
                        'periode_fin' => $frais->periode_fin,
                    ];
                })->toArray();
                
                $this->facturationService->ajouterFraisUsage($facture, $fraisArray);
                
                // Mettre à jour les frais d'usage
                foreach ($fraisUsages as $frais) {
                    $frais->update([
                        'facturation_id' => $facture->id,
                        'statut' => 'facture'
                    ]);
                }
                
                return redirect()->route('facturations.show', $facture)
                    ->with('success', 'Frais d\'usage ajoutés à la facture avec succès.');
            } else {
                // Créer une nouvelle facture pour les frais d'usage
                $entreprise = Entreprise::findOrFail($entrepriseId);
                
                // Créer une facture vide
                $facture = Facturation::create([
                    'entreprise_id' => $entrepriseId,
                    'numero_facture' => $this->facturationService->genererNumeroFacture($entreprise),
                    'date_facturation' => Carbon::now(),
                    'date_echeance' => Carbon::now()->addDays(30),
                    'montant_ht' => 0,
                    'taux_tva' => 20, // Taux par défaut
                    'montant_tva' => 0,
                    'montant_ttc' => 0,
                    'statut_paiement' => 'impaye',
                    'notes' => 'Facture de frais d\'usage',
                    'devise' => $fraisUsages->first()->devise
                ]);
                
                // Ajouter les frais à la facture
                $fraisArray = $fraisUsages->map(function ($frais) {
                    return [
                        'type_frais' => $frais->type_frais,
                        'description' => $frais->description,
                        'quantite' => $frais->quantite,
                        'prix_unitaire' => $frais->prix_unitaire,
                        'periode_debut' => $frais->periode_debut,
                        'periode_fin' => $frais->periode_fin,
                    ];
                })->toArray();
                
                $this->facturationService->ajouterFraisUsage($facture, $fraisArray);
                
                // Mettre à jour les frais d'usage
                foreach ($fraisUsages as $frais) {
                    $frais->update([
                        'facturation_id' => $facture->id,
                        'statut' => 'facture'
                    ]);
                }
                
                return redirect()->route('facturations.show', $facture)
                    ->with('success', 'Facture créée avec les frais d\'usage sélectionnés.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la facturation des frais d\'usage: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher le rapport des frais d'usage
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function rapport(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $request->input('entreprise_id');
        $periode = $request->input('periode', 'mois');
        
        // Déterminer les dates de début et de fin selon la période
        $dateFin = Carbon::now();
        
        switch ($periode) {
            case 'mois':
                $dateDebut = Carbon::now()->startOfMonth();
                break;
            case 'trimestre':
                $dateDebut = Carbon::now()->startOfQuarter();
                break;
            case 'annee':
                $dateDebut = Carbon::now()->startOfYear();
                break;
            default:
                $dateDebut = Carbon::now()->startOfMonth();
        }
        
        $query = FraisUsage::with(['entreprise', 'facturation']);
        
        // Filtrer par entreprise si spécifié
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        } else if (!$user->hasRole('admin')) {
            // Si l'utilisateur n'est pas admin, limiter aux frais de son entreprise
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        // Filtrer par période
        $query->where(function ($q) use ($dateDebut, $dateFin) {
            $q->whereBetween('periode_debut', [$dateDebut, $dateFin])
              ->orWhereBetween('periode_fin', [$dateDebut, $dateFin]);
        });
        
        // Regrouper les frais par type
        $fraisParType = $query->get()->groupBy('type_frais');
        
        // Calculer les totaux par type
        $totauxParType = [];
        foreach ($fraisParType as $type => $frais) {
            $totauxParType[$type] = [
                'quantite' => $frais->sum('quantite'),
                'montant_total' => $frais->sum('montant_total'),
                'count' => $frais->count(),
            ];
        }
        
        // Calculer le total global
        $totalGlobal = $query->sum('montant_total');
        
        $entreprises = Entreprise::all();
        
        return view('app.frais_usage.rapport', compact(
            'fraisParType',
            'totauxParType',
            'totalGlobal',
            'entreprises',
            'periode',
            'dateDebut',
            'dateFin'
        ));
    }
}
