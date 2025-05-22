<?php

namespace App\Http\Controllers\Paie;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paie\ConfigurationPaie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigurationPaieController extends Controller
{
    /**
     * Afficher la liste des configurations de paie
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Récupérer l'entreprise de l'utilisateur connecté
        $entrepriseId = Auth::user()->entreprise_id;

        // Récupérer les configurations de paie de l'entreprise
        $configurations = ConfigurationPaie::where('entreprise_id', $entrepriseId)
                                         ->orderBy('est_defaut', 'desc')
                                         ->orderBy('nom')
                                         ->get();

        return view('paie.configurations.index', compact('configurations'));
    }

    /**
     * Afficher le formulaire de création d'une configuration de paie
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('paie.configurations.create');
    }

    /**
     * Enregistrer une nouvelle configuration de paie
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valider les données
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'est_defaut' => 'nullable|boolean',
            'smig' => 'required|numeric|min:0',
            'plafond_cnps' => 'required|numeric|min:0',
            'taux_cnps_employe' => 'required|numeric|min:0|max:100',
            'taux_cnps_employeur' => 'required|numeric|min:0|max:100',
            'taux_prestations_familiales' => 'required|numeric|min:0|max:100',
            'taux_accident_travail' => 'required|numeric|min:0|max:100',
            'taux_assurance_maladie' => 'required|numeric|min:0|max:100',
            'abattement_igr' => 'required|numeric|min:0|max:100',
            'baremes_igr' => 'required|array',
            'parametres_indemnites' => 'required|array',
            'parametres_primes' => 'required|array'
        ]);

        try {
            // Ajouter l'entreprise de l'utilisateur connecté
            $validated['entreprise_id'] = Auth::user()->entreprise_id;
            
            // Convertir les valeurs booléennes
            $validated['est_defaut'] = $request->has('est_defaut');

            // Créer la configuration
            DB::beginTransaction();

            $configuration = ConfigurationPaie::create($validated);

            DB::commit();

            return redirect()->route('paie.configurations.index')
                           ->with('success', 'La configuration de paie a été créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la configuration de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Une erreur est survenue lors de la création de la configuration de paie : ' . $e->getMessage());
        }
    }

    /**
     * Afficher une configuration de paie
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Récupérer la configuration
        $configuration = ConfigurationPaie::findOrFail($id);

        // Vérifier que la configuration appartient à l'entreprise de l'utilisateur connecté
        if ($configuration->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Vous n\'avez pas accès à cette configuration de paie.');
        }

        return view('paie.configurations.show', compact('configuration'));
    }

    /**
     * Afficher le formulaire d'édition d'une configuration de paie
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Récupérer la configuration
        $configuration = ConfigurationPaie::findOrFail($id);

        // Vérifier que la configuration appartient à l'entreprise de l'utilisateur connecté
        if ($configuration->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Vous n\'avez pas accès à cette configuration de paie.');
        }

        return view('paie.configurations.edit', compact('configuration'));
    }

    /**
     * Mettre à jour une configuration de paie
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Récupérer la configuration
        $configuration = ConfigurationPaie::findOrFail($id);

        // Vérifier que la configuration appartient à l'entreprise de l'utilisateur connecté
        if ($configuration->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Vous n\'avez pas accès à cette configuration de paie.');
        }

        // Valider les données
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'est_defaut' => 'nullable|boolean',
            'smig' => 'required|numeric|min:0',
            'plafond_cnps' => 'required|numeric|min:0',
            'taux_cnps_employe' => 'required|numeric|min:0|max:100',
            'taux_cnps_employeur' => 'required|numeric|min:0|max:100',
            'taux_prestations_familiales' => 'required|numeric|min:0|max:100',
            'taux_accident_travail' => 'required|numeric|min:0|max:100',
            'taux_assurance_maladie' => 'required|numeric|min:0|max:100',
            'abattement_igr' => 'required|numeric|min:0|max:100',
            'baremes_igr' => 'required|array',
            'parametres_indemnites' => 'required|array',
            'parametres_primes' => 'required|array'
        ]);

        try {
            // Convertir les valeurs booléennes
            $validated['est_defaut'] = $request->has('est_defaut');

            // Mettre à jour la configuration
            DB::beginTransaction();

            $configuration->update($validated);

            DB::commit();

            return redirect()->route('paie.configurations.index')
                           ->with('success', 'La configuration de paie a été mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour de la configuration de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Une erreur est survenue lors de la mise à jour de la configuration de paie : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une configuration de paie
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Récupérer la configuration
        $configuration = ConfigurationPaie::findOrFail($id);

        // Vérifier que la configuration appartient à l'entreprise de l'utilisateur connecté
        if ($configuration->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Vous n\'avez pas accès à cette configuration de paie.');
        }

        // Vérifier si c'est la seule configuration de l'entreprise
        $nbConfigurations = ConfigurationPaie::where('entreprise_id', $configuration->entreprise_id)->count();
        if ($nbConfigurations <= 1) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Vous ne pouvez pas supprimer la seule configuration de paie de votre entreprise.');
        }

        // Vérifier si la configuration est utilisée par des bulletins de paie
        if ($configuration->bulletins()->exists()) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Cette configuration de paie est utilisée par des bulletins de paie et ne peut pas être supprimée.');
        }

        try {
            // Supprimer la configuration
            DB::beginTransaction();

            $configuration->delete();

            DB::commit();

            return redirect()->route('paie.configurations.index')
                           ->with('success', 'La configuration de paie a été supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la configuration de paie', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de la suppression de la configuration de paie : ' . $e->getMessage());
        }
    }

    /**
     * Définir une configuration comme configuration par défaut
     *
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function setDefault($id)
    {
        // Récupérer la configuration
        $configuration = ConfigurationPaie::findOrFail($id);

        // Vérifier que la configuration appartient à l'entreprise de l'utilisateur connecté
        if ($configuration->entreprise_id !== Auth::user()->entreprise_id) {
            return redirect()->route('paie.configurations.index')
                           ->with('error', 'Vous n\'avez pas accès à cette configuration de paie.');
        }

        try {
            // Définir la configuration comme configuration par défaut
            DB::beginTransaction();

            // Désactiver toutes les autres configurations par défaut
            ConfigurationPaie::where('entreprise_id', $configuration->entreprise_id)
                           ->where('id', '!=', $configuration->id)
                           ->where('est_defaut', true)
                           ->update(['est_defaut' => false]);

            // Définir cette configuration comme configuration par défaut
            $configuration->update(['est_defaut' => true]);

            DB::commit();

            return redirect()->route('paie.configurations.index')
                           ->with('success', 'La configuration de paie a été définie comme configuration par défaut.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la définition de la configuration de paie par défaut', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->with('error', 'Une erreur est survenue lors de la définition de la configuration de paie par défaut : ' . $e->getMessage());
        }
    }
}
