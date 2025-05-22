<?php

namespace App\Http\Controllers;

use App\Models\Facturation;
use App\Models\Entreprise;
use App\Models\FraisUsage;
use App\Services\FacturationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use Carbon\Carbon;

class FacturationController extends Controller
{
    protected $facturationService;
    
    public function __construct(FacturationService $facturationService)
    {
        $this->facturationService = $facturationService;
    }
    
    /**
     * Afficher la liste des factures
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $request->input('entreprise_id');
        $statut = $request->input('statut');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        
        $query = Facturation::with(['entreprise', 'abonnement']);
        
        // Filtrer par entreprise si spécifié
        if ($entrepriseId) {
            $query->where('entreprise_id', $entrepriseId);
        } else if (!$user->hasRole('admin')) {
            // Si l'utilisateur n'est pas admin, limiter aux factures de son entreprise
            $query->where('entreprise_id', $user->entreprise_id);
        }
        
        // Filtrer par statut de paiement
        if ($statut) {
            $query->where('statut_paiement', $statut);
        }
        
        // Filtrer par période
        if ($dateDebut) {
            $query->where('date_facturation', '>=', Carbon::parse($dateDebut));
        }
        
        if ($dateFin) {
            $query->where('date_facturation', '<=', Carbon::parse($dateFin));
        }
        
        $factures = $query->latest()->paginate(15);
        $entreprises = Entreprise::all();
        
        return view('app.facturation.index', compact('factures', 'entreprises'));
    }
    
    /**
     * Afficher les détails d'une facture
     *
     * @param Facturation $facturation
     * @return \Illuminate\View\View
     */
    public function show(Facturation $facturation)
    {
        $this->authorize('view', $facturation);
        
        $fraisUsages = $facturation->fraisUsages;
        
        return view('app.facturation.show', compact('facturation', 'fraisUsages'));
    }
    
    /**
     * Télécharger la facture en PDF
     *
     * @param Facturation $facturation
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Facturation $facturation)
    {
       // $this->authorize('view', $facturation);
        
        $pdf = PDF::loadView('pdf.facture', ['facture' => $facturation]);
        
        // Configuration pour un format A4
        $pdf->setPaper('A4');
        
        // Nom du fichier
        $filename = sprintf(
            'facture_%s_%s.pdf',
            $facturation->numero_facture,
            $facturation->entreprise->nom
        );
        
        // Téléchargement du PDF
        return $pdf->download($filename);
    }
    
    /**
     * Télécharger la facture (alias de downloadPdf)
     *
     * @param Facturation $facturation
     * @return \Illuminate\Http\Response
     */
    public function telecharger(Facturation $facturation)
    {
        return $this->downloadPdf($facturation);
    }
    
    /**
     * Marquer une facture comme payée
     *
     * @param Request $request
     * @param Facturation $facturation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function marquerCommePaye(Request $request, Facturation $facturation)
    {
        $this->authorize('update', $facturation);
        
        $validated = $request->validate([
            'mode_paiement' => 'required|string',
            'reference_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        try {
            $this->facturationService->marquerCommePaye($facturation, $validated);
            
            return redirect()->route('facturations.show', $facturation)
                ->with('success', 'La facture a été marquée comme payée avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }
    
    /**
     * Marquer toutes les factures en attente comme payées
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function payerToutes(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        $validated = $request->validate([
            'mode_paiement' => 'required|string',
            'reference_paiement' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        
        try {
            // Récupérer toutes les factures en attente
            $factures = Facturation::where('entreprise_id', $entrepriseId)
                ->where('statut_paiement', 'en_attente')
                ->get();
                
            $count = 0;
            foreach ($factures as $facture) {
                $this->facturationService->marquerCommePaye($facture, $validated);
                $count++;
            }
            
            return redirect()->route('facturations.paiement')
                ->with('success', "$count factures ont été marquées comme payées avec succès.");
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }
    
    /**
     * Envoyer la facture par email
     *
     * @param Request $request
     * @param Facturation $facturation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function envoyerParEmail(Request $request, Facturation $facturation)
    {
        $this->authorize('view', $facturation);
        
        $validated = $request->validate([
            'email' => 'nullable|email',
        ]);
        
        try {
            $email = $validated['email'] ?? null;
            $resultat = $this->facturationService->envoyerFactureParEmail($facturation, $email);
            
            if ($resultat) {
                return redirect()->route('facturations.show', $facturation)
                    ->with('success', 'Facture envoyée par email avec succès.');
            } else {
                return back()->with('error', 'Erreur lors de l\'envoi de la facture par email.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi de la facture par email: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher les frais d'usage pour une entreprise
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fraisUsage(Request $request)
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
        
        return view('app.facturation.frais_usage', compact('fraisUsages', 'entreprises', 'typeFrais'));
    }
    
    /**
     * Générer les factures automatiques (accessible uniquement aux administrateurs)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function genererFacturesAutomatiques()
    {
        $this->authorize('genererFactures', Facturation::class);
        
        try {
            $facturesGenerees = $this->facturationService->genererFacturesAutomatiques();
            $count = count($facturesGenerees);
            
            return redirect()->route('facturations.index')
                ->with('success', "{$count} factures ont été générées automatiquement.");
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la génération automatique des factures: ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher le tableau de bord de facturation
     *
     * @return \Illuminate\View\View
     */
    /**
     * Afficher la page de paiement des factures en attente
     *
     * @return \Illuminate\View\View
     */
    public function paiement()
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        
        // Récupérer les factures en attente de paiement
        $facturesEnAttente = Facturation::where('entreprise_id', $entrepriseId)
            ->where('statut_paiement', 'en_attente')
            ->orderBy('date_echeance')
            ->get();
            
        // Calculer le montant total dû
        $montantTotal = $facturesEnAttente->sum('montant_ttc');
        
        // Récupérer les factures échues (date_echeance dépassée)
        $facturesEchues = $facturesEnAttente->filter(function($facture) {
            return $facture->date_echeance < now();
        });
        
        return view('app.facturation.paiement', compact('facturesEnAttente', 'facturesEchues', 'montantTotal'));
    }
    
    /**
     * Afficher le tableau de bord de facturation
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
            // Pour les administrateurs, afficher les statistiques globales
            $facturesPaye = Facturation::paye()->count();
            $facturesImpaye = Facturation::impaye()->count();
            $facturesEchues = Facturation::impaye()->echu()->count();
            $montantTotal = Facturation::sum('montant_ttc');
            $montantPaye = Facturation::paye()->sum('montant_ttc');
            $montantImpaye = Facturation::impaye()->sum('montant_ttc');
            
            // Statistiques par mois (6 derniers mois)
            $statistiquesMensuelles = [];
            for ($i = 0; $i < 6; $i++) {
                $mois = Carbon::now()->subMonths($i);
                $debut = $mois->copy()->startOfMonth();
                $fin = $mois->copy()->endOfMonth();
                
                $statistiquesMensuelles[] = [
                    'mois' => $mois->format('F Y'),
                    'montant_total' => Facturation::whereBetween('date_facturation', [$debut, $fin])->sum('montant_ttc'),
                    'montant_paye' => Facturation::paye()->whereBetween('date_facturation', [$debut, $fin])->sum('montant_ttc'),
                    'montant_impaye' => Facturation::impaye()->whereBetween('date_facturation', [$debut, $fin])->sum('montant_ttc'),
                ];
            }
            
            // Top 5 des entreprises par montant facturé
            $topEntreprises = Entreprise::withSum('facturations', 'montant_ttc')
                ->orderByDesc('facturations_sum_montant_ttc')
                ->limit(5)
                ->get();
            
        } else {
            // Pour les utilisateurs normaux, afficher les statistiques de leur entreprise
            $entrepriseId = $user->entreprise_id;
            
            $facturesPaye = Facturation::where('entreprise_id', $entrepriseId)->paye()->count();
            $facturesImpaye = Facturation::where('entreprise_id', $entrepriseId)->impaye()->count();
            $facturesEchues = Facturation::where('entreprise_id', $entrepriseId)->impaye()->echu()->count();
            $montantTotal = Facturation::where('entreprise_id', $entrepriseId)->sum('montant_ttc');
            $montantPaye = Facturation::where('entreprise_id', $entrepriseId)->paye()->sum('montant_ttc');
            $montantImpaye = Facturation::where('entreprise_id', $entrepriseId)->impaye()->sum('montant_ttc');
            
            // Statistiques par mois (6 derniers mois)
            $statistiquesMensuelles = [];
            for ($i = 0; $i < 6; $i++) {
                $mois = Carbon::now()->subMonths($i);
                $debut = $mois->copy()->startOfMonth();
                $fin = $mois->copy()->endOfMonth();
                
                $statistiquesMensuelles[] = [
                    'mois' => $mois->format('F Y'),
                    'montant_total' => Facturation::where('entreprise_id', $entrepriseId)
                        ->whereBetween('date_facturation', [$debut, $fin])->sum('montant_ttc'),
                    'montant_paye' => Facturation::where('entreprise_id', $entrepriseId)
                        ->paye()->whereBetween('date_facturation', [$debut, $fin])->sum('montant_ttc'),
                    'montant_impaye' => Facturation::where('entreprise_id', $entrepriseId)
                        ->impaye()->whereBetween('date_facturation', [$debut, $fin])->sum('montant_ttc'),
                ];
            }
            
            $topEntreprises = null;
        }
        
        return view('app.facturation.dashboard', compact(
            'facturesPaye',
            'facturesImpaye',
            'facturesEchues',
            'montantTotal',
            'montantPaye',
            'montantImpaye',
            'statistiquesMensuelles',
            'topEntreprises'
        ));
    }
}
