<?php

namespace App\Http\Controllers;

use App\Models\CodePromo;
use App\Services\CodePromoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CodePromoController extends Controller
{
    protected $codePromoService;
    
    public function __construct(CodePromoService $codePromoService)
    {
        $this->codePromoService = $codePromoService;
        $this->middleware(['auth', 'verified']);
    }
    
    /**
     * Afficher la liste des codes promo
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->authorize('viewAny', CodePromo::class);
        
        $codesPromo = CodePromo::latest()->get();
        
        return view('app.code_promo.index', compact('codesPromo'));
    }
    
    /**
     * Afficher le formulaire de création d'un code promo
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $this->authorize('create', CodePromo::class);
        
        return view('app.code_promo.create');
    }
    
    /**
     * Enregistrer un nouveau code promo
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', CodePromo::class);
        
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:code_promos,code',
            'description' => 'nullable|string',
            'reduction' => 'required|numeric|min:0|max:100',
            'date_debut' => 'required|date',
            'date_expiration' => 'required|date|after:date_debut',
            'nombre_utilisations_max' => 'nullable|integer|min:0',
            'actif' => 'boolean',
            'prefix' => 'nullable|string|max:10',
            'length' => 'nullable|integer|min:4|max:20',
        ]);
        
        try {
            $codePromo = $this->codePromoService->creer($validated);
            
            return redirect()->route('code-promos.show', $codePromo)
                ->with('success', 'Code promo créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la création du code promo: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher les détails d'un code promo
     *
     * @param CodePromo $codePromo
     * @return \Illuminate\View\View
     */
    public function show(CodePromo $codePromo)
    {
        $this->authorize('view', $codePromo);
        
        $abonnements = $codePromo->abonnements()->with('entreprise')->get();
        
        return view('app.code_promo.show', compact('codePromo', 'abonnements'));
    }
    
    /**
     * Afficher le formulaire de modification d'un code promo
     *
     * @param CodePromo $codePromo
     * @return \Illuminate\View\View
     */
    public function edit(CodePromo $codePromo)
    {
        $this->authorize('update', $codePromo);
        
        return view('app.code_promo.edit', compact('codePromo'));
    }
    
    /**
     * Mettre à jour un code promo
     *
     * @param Request $request
     * @param CodePromo $codePromo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CodePromo $codePromo)
    {
        $this->authorize('update', $codePromo);
        
        $validated = $request->validate([
            'description' => 'nullable|string',
            'reduction' => 'required|numeric|min:0|max:100',
            'date_debut' => 'required|date',
            'date_expiration' => 'required|date|after:date_debut',
            'nombre_utilisations_max' => 'nullable|integer|min:0',
            'actif' => 'boolean',
        ]);
        
        try {
            $codePromo->update($validated);
            
            return redirect()->route('code-promos.show', $codePromo)
                ->with('success', 'Code promo mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour du code promo: ' . $e->getMessage());
        }
    }
    
    /**
     * Supprimer un code promo
     *
     * @param CodePromo $codePromo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CodePromo $codePromo)
    {
        $this->authorize('delete', $codePromo);
        
        try {
            $codePromo->delete();
            
            return redirect()->route('code-promos.index')
                ->with('success', 'Code promo supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression du code promo: ' . $e->getMessage());
        }
    }
    
    /**
     * Désactiver un code promo
     *
     * @param CodePromo $codePromo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function desactiver(CodePromo $codePromo)
    {
        $this->authorize('update', $codePromo);
        
        try {
            $codePromo->update(['actif' => false]);
            
            return redirect()->route('code-promos.show', $codePromo)
                ->with('success', 'Code promo désactivé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la désactivation du code promo: ' . $e->getMessage());
        }
    }
    
    /**
     * Activer un code promo
     *
     * @param CodePromo $codePromo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activer(CodePromo $codePromo)
    {
        $this->authorize('update', $codePromo);
        
        try {
            $codePromo->update(['actif' => true]);
            
            return redirect()->route('code-promos.show', $codePromo)
                ->with('success', 'Code promo activé avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'activation du code promo: ' . $e->getMessage());
        }
    }
    
    /**
     * Prolonger la date d'expiration d'un code promo
     *
     * @param Request $request
     * @param CodePromo $codePromo
     * @return \Illuminate\Http\RedirectResponse
     */
    public function prolonger(Request $request, CodePromo $codePromo)
    {
        $this->authorize('update', $codePromo);
        
        $validated = $request->validate([
            'jours' => 'required|integer|min:1',
        ]);
        
        try {
            $this->codePromoService->prolonger($codePromo->code, $validated['jours']);
            
            return redirect()->route('code-promos.show', $codePromo)
                ->with('success', 'Date d\'expiration prolongée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la prolongation de la date d\'expiration: ' . $e->getMessage());
        }
    }
    
    /**
     * Vérifier la validité d'un code promo
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifier(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'montant' => 'required|numeric|min:0',
        ]);
        
        $resultat = $this->codePromoService->valider($validated['code'], $validated['montant']);
        
        return response()->json($resultat);
    }
}
