<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\Paie\BulletinPaieRepositoryInterface;
use App\Interfaces\Paie\CalculPaieServiceInterface;
use App\Models\Employeur;
use App\Models\Employe;
use App\Models\Paie\ConfigurationPaie;
use App\Models\Paie\BulletinPaie;
use App\Models\Paie\ElementPaie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class BulletinPaieController extends Controller
{
    /**
     * @var BulletinPaieRepositoryInterface
     */
    protected $bulletinRepository;

    /**
     * @var CalculPaieServiceInterface
     */
    protected $calculPaieService;
    
    /**
     * @var string
     */
    protected $wizardLayout = 'layouts.wizard';

    /**
     * Constructeur
     *
     * @param BulletinPaieRepositoryInterface $bulletinRepository
     * @param CalculPaieServiceInterface $calculPaieService
     */
    public function __construct(
        BulletinPaieRepositoryInterface $bulletinRepository,
        CalculPaieServiceInterface $calculPaieService
    ) {
        $this->bulletinRepository = $bulletinRepository;
        $this->calculPaieService = $calculPaieService;
    }

    /**
     * Afficher la liste des bulletins de paie
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Récupérer les filtres
        $filtres = [
            'entreprise_id' => $request->input('entreprise_id', Auth::user()->entreprise_id),
            'employeur_id' => $request->input('employeur_id'),
            'statut' => $request->input('statut'),
            'date_debut' => $request->input('date_debut'),
            'date_fin' => $request->input('date_fin'),
            'tri_par' => $request->input('tri_par', 'periode_fin'),
            'tri_direction' => $request->input('tri_direction', 'desc'),
            'limite' => $request->input('limite', 15)
        ];

        // Récupérer les bulletins
        $bulletins = $this->bulletinRepository->tous($filtres);

        // Récupérer la liste des employeurs pour le filtre
        $employeurs = Employeur::where('entreprise_id', $filtres['entreprise_id'])->get();

        return view('paie.bulletins.index', compact('bulletins', 'employeurs', 'filtres'));
    }

    /**
     * Afficher le formulaire de création d'un bulletin de paie
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        // Récupérer l'entreprise de l'utilisateur connecté
        $entrepriseId = Auth::user()->entreprise_id;

        // Récupérer les employeurs de l'entreprise
        $employeurs = Employeur::where('entreprise_id', $entrepriseId)
                              ->where('statut', 'actif')
                              ->orderBy('nom')
                              ->get();

        // Récupérer les configurations de paie de l'entreprise
        $configurations = ConfigurationPaie::where('entreprise_id', $entrepriseId)
                                         ->orderBy('est_defaut', 'desc')
                                         ->get();

        // Récupérer la configuration par défaut
        $configurationDefaut = ConfigurationPaie::where('entreprise_id', $entrepriseId)
            ->where('est_defaut', true)
            ->first();

        // Si aucune configuration par défaut n'existe, en créer une
        if (!$configurationDefaut) {
            $configurationDefaut = ConfigurationPaie::creerConfigurationDefaut($entrepriseId);
            $configurations->push($configurationDefaut);
        }

        return view('paie.bulletins.create', compact('employeurs', 'configurations', 'configurationDefaut'));
    }

    /**
     * Enregistrer un nouveau bulletin de paie
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valider les données
        $validated = $request->validate([
            'employeur_id' => 'required|exists:employeurs,id',
            'configuration_id' => 'required|exists:configurations_paie,id',
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'date_paiement' => 'required|date',
            'elements_supplementaires' => 'nullable|array'
        ]);

        try {
            // Récupérer l'employeur et la configuration
            $employeur = Employeur::findOrFail($validated['employeur_id']);
            $configuration = ConfigurationPaie::findOrFail($validated['configuration_id']);

            // Préparer les paramètres
            $parametres = [
                'periode_debut' => $validated['periode_debut'],
                'periode_fin' => $validated['periode_fin'],
                'date_paiement' => $validated['date_paiement'],
                'genere_par' => Auth::id(),
                'elements_supplementaires' => $validated['elements_supplementaires'] ?? []
            ];

            // Générer le bulletin de paie
            DB::beginTransaction();

            $bulletin = $this->calculPaieService->genererBulletinPaie($employeur, $configuration, $parametres);

            DB::commit();

            return redirect()->route('paie.bulletins.show', $bulletin->id)
                           ->with('success', 'Le bulletin de paie a été généré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la génération du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Une erreur est survenue lors de la génération du bulletin de paie : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un bulletin de paie
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Récupérer le bulletin
        $bulletin = $this->bulletinRepository->trouverParId($id);

        if (!$bulletin) {
            return redirect()->route('paie.bulletins.index')
                           ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
        }

        // Récupérer les éléments de paie par type
        $elementsSalaire = $bulletin->getElementsByType('salaire');
        $elementsIndemnites = $bulletin->getElementsByType('indemnite');
        $elementsPrimes = $bulletin->getElementsByType('prime');
        $elementsRetenues = $bulletin->getElementsByType('retenue_salariale');
        $elementsCharges = $bulletin->getElementsByType('charge_patronale');

        return view('paie.bulletins.show', compact(
            'bulletin',
            'elementsSalaire',
            'elementsIndemnites',
            'elementsPrimes',
            'elementsRetenues',
            'elementsCharges'
        ));
    }

    /**
     * Afficher le formulaire d'édition d'un bulletin de paie
     *
     * @param string $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        // Récupérer le bulletin
        $bulletin = $this->bulletinRepository->trouverParId($id);

        if (!$bulletin) {
            return redirect()->route('paie.bulletins.index')
                           ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
        }

        // Vérifier si le bulletin est en brouillon
        if (!$bulletin->estBrouillon()) {
            return redirect()->route('paie.bulletins.show', $bulletin->id)
                           ->with('error', 'Seuls les bulletins en brouillon peuvent être modifiés.');
        }

        // Récupérer les configurations de paie de l'entreprise
        $configurations = ConfigurationPaie::where('entreprise_id', $bulletin->entreprise_id)
                                         ->orderBy('est_defaut', 'desc')
                                         ->get();

        return view('paie.bulletins.edit', compact('bulletin', 'configurations'));
    }

    /**
     * Mettre à jour un bulletin de paie
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Valider les données
        $validated = $request->validate([
            'periode_debut' => 'required|date',
            'periode_fin' => 'required|date|after_or_equal:periode_debut',
            'date_paiement' => 'required|date',
            'elements' => 'nullable|array'
        ]);

        try {
            // Récupérer le bulletin
            $bulletin = $this->bulletinRepository->trouverParId($id);

            if (!$bulletin) {
                return redirect()->route('paie.bulletins.index')
                               ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
            }

            // Vérifier si le bulletin est en brouillon
            if (!$bulletin->estBrouillon()) {
                return redirect()->route('paie.bulletins.show', $bulletin->id)
                               ->with('error', 'Seuls les bulletins en brouillon peuvent être modifiés.');
            }

            // Mettre à jour le bulletin
            DB::beginTransaction();

            $this->bulletinRepository->mettreAJour($id, $validated);

            DB::commit();

            return redirect()->route('paie.bulletins.show', $bulletin->id)
                           ->with('success', 'Le bulletin de paie a été mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Une erreur est survenue lors de la mise à jour du bulletin de paie : ' . $e->getMessage());
        }
    }

    /**
     * Valider un bulletin de paie
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function valider($id)
    {
        try {
            // Récupérer le bulletin
            $bulletin = $this->bulletinRepository->trouverParId($id);

            if (!$bulletin) {
                return redirect()->route('paie.bulletins.index')
                               ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
            }

            // Vérifier si le bulletin est en brouillon
            if (!$bulletin->estBrouillon()) {
                return redirect()->route('paie.bulletins.show', $bulletin->id)
                               ->with('error', 'Seuls les bulletins en brouillon peuvent être validés.');
            }

            // Valider le bulletin
            DB::beginTransaction();

            $this->bulletinRepository->valider($id, Auth::id());

            DB::commit();

            return redirect()->route('paie.bulletins.show', $bulletin->id)
                           ->with('success', 'Le bulletin de paie a été validé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de la validation du bulletin de paie : ' . $e->getMessage());
        }
    }

    /**
     * Annuler un bulletin de paie
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function annuler(Request $request, $id)
    {
        // Valider les données
        $validated = $request->validate([
            'commentaire' => 'nullable|string|max:255'
        ]);

        try {
            // Récupérer le bulletin
            $bulletin = $this->bulletinRepository->trouverParId($id);

            if (!$bulletin) {
                return redirect()->route('paie.bulletins.index')
                               ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
            }

            // Vérifier si le bulletin est déjà annulé
            if ($bulletin->estAnnule()) {
                return redirect()->route('paie.bulletins.show', $bulletin->id)
                               ->with('error', 'Ce bulletin de paie est déjà annulé.');
            }

            // Annuler le bulletin
            DB::beginTransaction();

            $this->bulletinRepository->annuler($id, $validated['commentaire'] ?? null);

            DB::commit();

            return redirect()->route('paie.bulletins.show', $bulletin->id)
                           ->with('success', 'Le bulletin de paie a été annulé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'annulation du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de l\'annulation du bulletin de paie : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un bulletin de paie
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Récupérer le bulletin
            $bulletin = $this->bulletinRepository->trouverParId($id);

            if (!$bulletin) {
                return redirect()->route('paie.bulletins.index')
                               ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
            }

            // Vérifier si le bulletin est validé
            if ($bulletin->estValide()) {
                return redirect()->route('paie.bulletins.show', $bulletin->id)
                               ->with('error', 'Les bulletins validés ne peuvent pas être supprimés.');
            }

            // Supprimer le bulletin
            DB::beginTransaction();

            $this->bulletinRepository->supprimer($id);

            DB::commit();

            return redirect()->route('paie.bulletins.index')
                           ->with('success', 'Le bulletin de paie a été supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de la suppression du bulletin de paie : ' . $e->getMessage());
        }
    }

    /**
     * Générer le PDF d'un bulletin de paie
     *
     * @param string $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function pdf($id)
    {
        try {
            // Récupérer le bulletin
            $bulletin = $this->bulletinRepository->trouverParId($id);

            if (!$bulletin) {
                return redirect()->route('paie.bulletins.index')
                               ->with('error', 'Le bulletin de paie demandé n\'existe pas.');
            }

            // Récupérer les éléments de paie par type
            $elementsSalaire = $bulletin->getElementsByType('salaire');
            $elementsIndemnites = $bulletin->getElementsByType('indemnite');
            $elementsPrimes = $bulletin->getElementsByType('prime');
            $elementsRetenues = $bulletin->getElementsByType('retenue_salariale');
            $elementsCharges = $bulletin->getElementsByType('charge_patronale');

            // Générer le PDF
            $pdf = Pdf::loadView('paie.bulletins.pdf', compact(
                'bulletin',
                'elementsSalaire',
                'elementsIndemnites',
                'elementsPrimes',
                'elementsRetenues',
                'elementsCharges'
            ));

            // Définir le nom du fichier
            $nomFichier = 'bulletin-paie-' . Str::slug($bulletin->reference) . '.pdf';

            // Si le bulletin est validé, enregistrer le PDF
            if ($bulletin->estValide() && !$bulletin->fichier_pdf) {
                $cheminFichier = 'bulletins-paie/' . $nomFichier;
                Storage::disk('public')->put($cheminFichier, $pdf->output());

                // Mettre à jour le chemin du fichier PDF dans le bulletin
                $bulletin->update(['fichier_pdf' => $cheminFichier]);
            }

            // Retourner le PDF pour téléchargement
            return $pdf->download($nomFichier);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de la génération du PDF du bulletin de paie : ' . $e->getMessage());
        }
    }
    
    /**
     * Afficher l'interface de génération d'un bulletin de paie
     * 
     * @return \Illuminate\View\View
     */
    public function generateWizard()
    {
        // Récupérer l'entreprise de l'utilisateur connecté
        $entrepriseId = Auth::user()->entreprise_id;
        
        // Récupérer les employeurs de l'entreprise
        $employeurs = Employeur::where('entreprise_id', $entrepriseId)
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get();
            
        // Récupérer les configurations de paie disponibles
        $configurations = ConfigurationPaie::where('entreprise_id', $entrepriseId)
            ->orderBy('nom')
            ->get();
            
        // Récupérer la configuration par défaut (la plus récente active)
        $configurationDefaut = $configurations->where('est_defaut', true)->first();

        // Si aucune configuration par défaut n'existe, en créer une
        if (!$configurationDefaut) {
            $configurationDefaut = ConfigurationPaie::creerConfigurationDefaut($entrepriseId);
            $configurations->push($configurationDefaut);
        }
        
        return view('paie.wizard.generate', compact('employeurs', 'configurations', 'configurationDefaut'));
    }
    
    /**
     * Afficher l'interface de génération en masse de bulletins de paie
     * 
     * @return \Illuminate\View\View
     */
    public function generateMasseWizard()
    {
        // Récupérer l'entreprise de l'utilisateur connecté
        $entrepriseId = Auth::user()->entreprise_id;
        
        // Récupérer les employés de l'entreprise
        $employeurs = Employe::where('entreprise_id', $entrepriseId)
            ->where('statut', 'actif')
            ->select('id', 'nom', 'prenoms', 'matricule', 'poste', 'salaire_base')
            ->orderBy('nom')
            ->get()
            ->map(function ($employe) {
                $employe->nom_complet = $employe->nom . ' ' . $employe->prenoms;
                return $employe;
            });
            
        // Récupérer les configurations de paie disponibles
        $configurations = ConfigurationPaie::where('entreprise_id', $entrepriseId)
            ->orderBy('nom')
            ->get();
            
        // Récupérer la configuration par défaut
        $configurationDefaut = $configurations->where('est_defaut', true)->first();

        // Si aucune configuration par défaut n'existe, en créer une
        if (!$configurationDefaut) {
            $configurationDefaut = ConfigurationPaie::creerConfigurationDefaut($entrepriseId);
            $configurations->push($configurationDefaut);
        }
            
        return view('paie.wizard.generate-masse', compact('employeurs', 'configurations', 'configurationDefaut'));
    }
    
    /**
     * Traiter la génération d'un bulletin de paie
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processGenerate(Request $request)
    {
        try {
            // Valider les données
            $validated = $request->validate([
                'employeur_id' => 'required|exists:employeurs,id',
                'configuration_paie_id' => 'required|exists:paie_configurations,id',
                'periode_debut' => 'required|date',
                'periode_fin' => 'required|date|after_or_equal:periode_debut',
                'date_paiement' => 'required|date',
                'salaire_base' => 'required|numeric|min:0',
                'indemnites' => 'nullable|array',
                'indemnites.*.libelle' => 'required|string',
                'indemnites.*.type' => 'required|in:pourcentage,montant_fixe',
                'indemnites.*.taux' => 'required_if:indemnites.*.type,pourcentage|nullable|numeric',
                'indemnites.*.montant' => 'required_if:indemnites.*.type,montant_fixe|nullable|numeric',
                'indemnites.*.imposable' => 'boolean',
                'primes' => 'nullable|array',
                'primes.*.libelle' => 'required|string',
                'primes.*.type' => 'required|in:pourcentage,montant_fixe',
                'primes.*.taux' => 'required_if:primes.*.type,pourcentage|nullable|numeric',
                'primes.*.montant' => 'required_if:primes.*.type,montant_fixe|nullable|numeric',
                'primes.*.imposable' => 'boolean',
                'retenues' => 'nullable|array',
                'retenues.*.libelle' => 'required|string',
                'retenues.*.type' => 'required|in:pourcentage,montant_fixe',
                'retenues.*.taux' => 'required_if:retenues.*.type,pourcentage|nullable|numeric',
                'retenues.*.montant' => 'required_if:retenues.*.type,montant_fixe|nullable|numeric',
                'calcul_auto' => 'boolean',
                'valider_directement' => 'boolean',
            ]);
            
            // Récupérer l'employeur
            $employeur = Employeur::findOrFail($validated['employeur_id']);
            
            // Récupérer la configuration
            $configuration = ConfigurationPaie::findOrFail($validated['configuration_paie_id']);
            
            // Préparer les éléments supplémentaires
            $elementsSupplementaires = [
                'salaire_base' => $validated['salaire_base'],
                'indemnites' => $validated['indemnites'] ?? [],
                'primes' => $validated['primes'] ?? [],
                'retenues' => $validated['retenues'] ?? [],
            ];
            
            // Préparer les paramètres pour le service de calcul de paie
            $parametres = [
                'periode_debut' => $validated['periode_debut'],
                'periode_fin' => $validated['periode_fin'],
                'date_paiement' => $validated['date_paiement'],
                'genere_par' => Auth::id(),
                'entreprise_id' => Auth::user()->entreprise_id,
                'elements_supplementaires' => $elementsSupplementaires,
                'calcul_auto' => $validated['calcul_auto'] ?? true,
            ];
            
            // Générer le bulletin de paie
            $bulletin = $this->calculPaieService->genererBulletinPaie($employeur, $configuration, $parametres);
            
            // Valider directement le bulletin si demandé
            if ($validated['valider_directement'] ?? false) {
                $bulletin->update([
                    'statut' => 'validé',
                    'valide_par' => Auth::id(),
                    'date_validation' => now(),
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Bulletin de paie généré avec succès',
                'bulletin_id' => $bulletin->id,
                'redirect_url' => route('paie.bulletins.show', $bulletin->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du bulletin de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération du bulletin de paie : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Traiter la génération en masse de bulletins de paie
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processGenerateMasse(Request $request)
    {
        try {
            // Valider les données
            $validated = $request->validate([
                'employeur_ids' => 'required|array',
                'employeur_ids.*' => 'exists:employeurs,id',
                'configuration_paie_id' => 'required|exists:paie_configurations,id',
                'periode_debut' => 'required|date',
                'periode_fin' => 'required|date|after_or_equal:periode_debut',
                'date_paiement' => 'required|date',
                'utiliser_salaire_base_employe' => 'boolean',
                'salaire_base_commun' => 'required_if:utiliser_salaire_base_employe,false|nullable|numeric|min:0',
                'appliquer_indemnites_communes' => 'boolean',
                'indemnites_communes' => 'nullable|array',
                'indemnites_communes.*.libelle' => 'required|string',
                'indemnites_communes.*.type' => 'required|in:pourcentage,montant_fixe',
                'indemnites_communes.*.taux' => 'required_if:indemnites_communes.*.type,pourcentage|nullable|numeric',
                'indemnites_communes.*.montant' => 'required_if:indemnites_communes.*.type,montant_fixe|nullable|numeric',
                'indemnites_communes.*.imposable' => 'boolean',
                'appliquer_primes_communes' => 'boolean',
                'primes_communes' => 'nullable|array',
                'primes_communes.*.libelle' => 'required|string',
                'primes_communes.*.type' => 'required|in:pourcentage,montant_fixe',
                'primes_communes.*.taux' => 'required_if:primes_communes.*.type,pourcentage|nullable|numeric',
                'primes_communes.*.montant' => 'required_if:primes_communes.*.type,montant_fixe|nullable|numeric',
                'primes_communes.*.imposable' => 'boolean',
                'calcul_auto' => 'boolean',
                'valider_directement' => 'boolean',
            ]);
            
            // Récupérer la configuration
            $configuration = ConfigurationPaie::findOrFail($validated['configuration_paie_id']);
            
            // Récupérer les employés
            $employeurIds = $validated['employeur_ids'] ?? [];
            if (empty($employeurIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun employé sélectionné.'
                ], 400);
            }
            
            $employes = Employeur::whereIn('id', $employeurIds)->get();
            
            // Paramètres communs
            $periodeDebut = $validated['periode_debut'];
            $periodeFin = $validated['periode_fin'];
            $datePaiement = $validated['date_paiement'];
            $utiliserSalaireBaseEmploye = $validated['utiliser_salaire_base_employe'] ?? true;
            $salaireBaseCommun = $validated['salaire_base_commun'] ?? 0;
            $appliquerIndemnitesCommunes = $validated['appliquer_indemnites_communes'] ?? false;
            $indemnites = $appliquerIndemnitesCommunes ? ($validated['indemnites_communes'] ?? []) : [];
            $appliquerPrimesCommunes = $validated['appliquer_primes_communes'] ?? false;
            $primes = $appliquerPrimesCommunes ? ($validated['primes_communes'] ?? []) : [];
            $calculAuto = $validated['calcul_auto'] ?? true;
            $validerDirectement = $validated['valider_directement'] ?? false;
            
            // Générer les bulletins
            $bulletinsGeneres = [];
            
            DB::beginTransaction();
            
            foreach ($employes as $employe) {
                // Déterminer le salaire de base
                $salaireBase = $utiliserSalaireBaseEmploye ? ($employe->salaire_base ?? 0) : $salaireBaseCommun;
                
                // Préparer les éléments supplémentaires
                $elementsSupplementaires = [
                    'salaire_base' => $salaireBase,
                    'indemnites' => $indemnites,
                    'primes' => $primes,
                    'retenues' => [],
                ];
                
                // Préparer les paramètres pour le service de calcul de paie
                $parametres = [
                    'periode_debut' => $periodeDebut,
                    'periode_fin' => $periodeFin,
                    'date_paiement' => $datePaiement,
                    'genere_par' => Auth::id(),
                    'entreprise_id' => Auth::user()->entreprise_id,
                    'elements_supplementaires' => $elementsSupplementaires,
                    'calcul_auto' => $calculAuto,
                ];
                
                // Générer le bulletin de paie
                $bulletin = $this->calculPaieService->genererBulletinPaie($employe, $configuration, $parametres);
                
                // Valider directement le bulletin si demandé
                if ($validerDirectement) {
                    $bulletin->update([
                        'statut' => 'validé',
                        'valide_par' => Auth::id(),
                        'date_validation' => now(),
                    ]);
                }
                
                $bulletinsGeneres[] = $bulletin->id;
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($bulletinsGeneres) . ' bulletin(s) de paie généré(s)' . ($validerDirectement ? ' et validé(s)' : ''),
                'bulletins_ids' => $bulletinsGeneres,
                'redirect_url' => route('paie.bulletins.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la génération des bulletins de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la génération des bulletins de paie : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer les informations d'un employeur en AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeurInfo(Request $request)
    {
        try {
            $employeurId = $request->input('employeur_id');
            
            if (!$employeurId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de l\'employeur non fourni'
                ], 400);
            }
            
            $employeur = Employeur::findOrFail($employeurId);
            
            return response()->json([
                'success' => true,
                'employeur' => [
                    'id' => $employeur->id,
                    'nom_complet' => $employeur->nom_complet,
                    'matricule' => $employeur->matricule,
                    'salaire_base' => $employeur->salaire_base,
                    'date_embauche' => $employeur->date_embauche,
                    'poste' => $employeur->poste,
                    'departement' => $employeur->departement,
                    'categorie' => $employeur->categorie,
                    'echelon' => $employeur->echelon,
                    'nombre_enfants' => $employeur->nombre_enfants,
                    'situation_familiale' => $employeur->situation_familiale,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des informations de l\'employeur : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculer les éléments de paie en AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateElements(Request $request)
    {
        try {
            // Valider les données
            $validated = $request->validate([
                'employeur_id' => 'required|exists:employeurs,id',
                'configuration_paie_id' => 'required|exists:paie_configurations,id',
                'salaire_base' => 'required|numeric|min:0',
                'indemnites' => 'nullable|array',
                'primes' => 'nullable|array',
                'retenues' => 'nullable|array',
            ]);
            
            // Récupérer l'employeur et la configuration
            $employeur = Employeur::findOrFail($validated['employeur_id']);
            $configuration = ConfigurationPaie::findOrFail($validated['configuration_paie_id']);
            
            // Préparer les éléments
            $elements = [
                'salaire_base' => $validated['salaire_base'],
                'indemnites' => $validated['indemnites'] ?? [],
                'primes' => $validated['primes'] ?? [],
                'retenues' => $validated['retenues'] ?? [],
            ];
            
            // Calculer les éléments
            $resultats = $this->calculPaieService->calculerElements($employeur, $configuration, $elements);
            
            return response()->json([
                'success' => true,
                'resultats' => $resultats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des éléments de paie : ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Afficher le bulletin de paie avec le design Tailwind CSS
     * 
     * @param string $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showTailwind($id)
    {
        try {
            // Récupérer le bulletin
            $bulletin = $this->bulletinRepository->trouverParId($id);
            
            if (!$bulletin) {
                return redirect()->route('paie.bulletins.index')
                    ->with('error', 'Bulletin de paie non trouvé.');
            }
            
            // Vérifier les autorisations
            if (Auth::user()->entreprise_id != $bulletin->employeur->entreprise_id) {
                return redirect()->route('paie.bulletins.index')
                    ->with('error', 'Vous n\'avez pas l\'autorisation de visualiser ce bulletin.');
            }
            
            // Récupérer les éléments du bulletin
            $elementsSalaire = ElementPaie::where('bulletin_paie_id', $bulletin->id)
                ->where('type', 'salaire_base')
                ->get();
                
            $elementsIndemnites = ElementPaie::where('bulletin_paie_id', $bulletin->id)
                ->where('type', 'indemnite')
                ->get();
                
            $elementsPrimes = ElementPaie::where('bulletin_paie_id', $bulletin->id)
                ->where('type', 'prime')
                ->get();
                
            $elementsRetenues = ElementPaie::where('bulletin_paie_id', $bulletin->id)
                ->where('type', 'retenue')
                ->get();
                
            $elementsCharges = ElementPaie::where('bulletin_paie_id', $bulletin->id)
                ->where('type', 'charge_patronale')
                ->get();
            
            // Préparer les données pour la vue Tailwind
            $payrollData = [
                'employee' => [
                    'name' => $bulletin->employeur->nom_complet ?? 'N/A',
                    'position' => $bulletin->employeur->fonction ?? 'N/A',
                    'contractType' => $bulletin->employeur->type_contrat ?? 'N/A',
                    'contractHours' => $bulletin->employeur->horaire ?? 'Temps plein'
                ],
                'company' => [
                    'name' => $bulletin->employeur->entreprise->nom ?? 'N/A',
                    'siret' => $bulletin->employeur->entreprise->rccm ?? 'N/A'
                ],
                'period' => [
                    'start' => $bulletin->periode_debut ? \Carbon\Carbon::parse($bulletin->periode_debut)->format('d/m/Y') : 'N/A',
                    'end' => $bulletin->periode_fin ? \Carbon\Carbon::parse($bulletin->periode_fin)->format('d/m/Y') : 'N/A',
                    'paymentDate' => $bulletin->date_paiement ? \Carbon\Carbon::parse($bulletin->date_paiement)->format('d/m/Y') : 'N/A',
                    'number' => $bulletin->reference
                ],
                'earnings' => [
                    'baseSalary' => $elementsSalaire->sum('montant') ?? 0,
                    'overtime' => 0, // À adapter selon vos données
                    'bonus' => $elementsPrimes->sum('montant') ?? 0,
                    'benefits' => $elementsIndemnites->sum('montant') ?? 0,
                    'totalGross' => $bulletin->salaire_brut ?? 0
                ],
                'deductions' => [
                    'socialContrib' => $elementsRetenues->where('libelle', 'like', '%social%')->sum('montant') ?? 0,
                    'incomeTax' => $elementsRetenues->where('libelle', 'like', '%impôt%')->sum('montant') ?? 0,
                    'specialDeduction' => $elementsRetenues->whereNotIn('libelle', ['%social%', '%impôt%'])->sum('montant') ?? 0,
                    'totalDeductions' => $bulletin->total_retenues ?? 0
                ],
                'net' => [
                    'amount' => $bulletin->salaire_net ?? 0,
                    'inWords' => ''
                ],
                'leave' => [
                    'accrued' => $bulletin->employeur->conges_acquis ?? 0,
                    'taken' => $bulletin->employeur->conges_pris ?? 0,
                    'balance' => ($bulletin->employeur->conges_acquis ?? 0) - ($bulletin->employeur->conges_pris ?? 0)
                ],
                'tax' => [
                    'annualGross' => ($bulletin->salaire_brut ?? 0) * 12,
                    'rate' => $bulletin->employeur->taux_imposition ?? '0%',
                    'shares' => $bulletin->employeur->parts_fiscales ?? 1
                ],
                'payment' => [
                    'method' => $bulletin->mode_paiement ?? 'Virement bancaire'
                ],
                'generatedOn' => \Carbon\Carbon::now()->format('d/m/Y')
            ];
            
            return view('paie.bulletins.paie', compact('bulletin', 'payrollData'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du bulletin de paie Tailwind', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('paie.bulletins.index')
                ->with('error', 'Une erreur est survenue lors de l\'affichage du bulletin de paie : ' . $e->getMessage());
        }
    }
}
