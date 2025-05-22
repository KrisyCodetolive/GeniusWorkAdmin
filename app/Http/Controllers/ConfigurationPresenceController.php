<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationPresence;
use App\Models\Entreprise;
use App\Services\ConfigurationPresenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConfigurationPresenceController extends Controller
{
    /**
     * Le service de configuration de présence.
     *
     * @var ConfigurationPresenceService
     */
    protected $configurationPresenceService;

    /**
     * Crée une nouvelle instance du contrôleur.
     *
     * @param ConfigurationPresenceService $configurationPresenceService
     * @return void
     */
    public function __construct(ConfigurationPresenceService $configurationPresenceService)
    {
        $this->configurationPresenceService = $configurationPresenceService;
        $this->middleware('auth');
    }

    /**
     * Affiche le formulaire de configuration des présences.
     *
     * @param Entreprise $entreprise
     * @return \Illuminate\View\View
     */
    public function edit(Entreprise $entreprise)
    {
        $this->authorize('updateConfigurationPresence', $entreprise);
        
        $configuration = $this->configurationPresenceService->getConfiguration($entreprise);
        
        return view('configurations.presence.edit', [
            'entreprise' => $entreprise,
            'configuration' => $configuration,
        ]);
    }

    /**
     * Met à jour la configuration des présences.
     *
     * @param Request $request
     * @param Entreprise $entreprise
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Entreprise $entreprise)
    {
        $this->authorize('updateConfigurationPresence', $entreprise);
        
        $validator = Validator::make($request->all(), [
            'heures_supplementaires_actives' => 'boolean',
            'nombre_pointages_par_jour' => 'required|integer|min:1|max:10',
            'pauses_actives' => 'boolean',
            'annuler_presence_sans_sortie' => 'boolean',
            'delai_annulation_heures' => 'required|integer|min:1|max:72',
            'notifications_actives' => 'boolean',
            'notification_absence' => 'boolean',
            'notification_retard' => 'boolean',
            'notification_conge' => 'boolean',
            'message_absence' => 'nullable|string|max:500',
            'message_retard' => 'nullable|string|max:500',
            'message_conge' => 'nullable|string|max:500',
        ]);
        
        if ($validator->fails()) {
            return redirect()
                ->route('configurations.presence.edit', $entreprise)
                ->withErrors($validator)
                ->withInput();
        }
        
        // Préparer les données pour la mise à jour
        $data = [
            'heures_supplementaires_actives' => $request->boolean('heures_supplementaires_actives'),
            'nombre_pointages_par_jour' => $request->input('nombre_pointages_par_jour'),
            'pauses_actives' => $request->boolean('pauses_actives'),
            'annuler_presence_sans_sortie' => $request->boolean('annuler_presence_sans_sortie'),
            'delai_annulation_heures' => $request->input('delai_annulation_heures'),
            'notifications_actives' => $request->boolean('notifications_actives'),
            'notification_absence' => $request->boolean('notification_absence'),
            'notification_retard' => $request->boolean('notification_retard'),
            'notification_conge' => $request->boolean('notification_conge'),
        ];
        
        // Ajouter les messages personnalisés s'ils sont fournis
        if ($request->filled('message_absence')) {
            $data['message_absence'] = $request->input('message_absence');
        }
        
        if ($request->filled('message_retard')) {
            $data['message_retard'] = $request->input('message_retard');
        }
        
        if ($request->filled('message_conge')) {
            $data['message_conge'] = $request->input('message_conge');
        }
        
        // Mettre à jour la configuration
        $this->configurationPresenceService->updateConfiguration($entreprise, $data);
        
        return redirect()
            ->route('configurations.presence.edit', $entreprise)
            ->with('success', 'La configuration des présences a été mise à jour avec succès.');
    }

    /**
     * Réinitialise la configuration des présences aux valeurs par défaut.
     *
     * @param Entreprise $entreprise
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Entreprise $entreprise)
    {
        $this->authorize('updateConfigurationPresence', $entreprise);
        
        // Supprimer la configuration existante
        ConfigurationPresence::where('entreprise_id', $entreprise->id)->delete();
        
        // Créer une nouvelle configuration par défaut
        $this->configurationPresenceService->creerConfigurationParDefaut($entreprise->id);
        
        return redirect()
            ->route('configurations.presence.edit', $entreprise)
            ->with('success', 'La configuration des présences a été réinitialisée aux valeurs par défaut.');
    }

    /**
     * Affiche la configuration des présences pour l'entreprise de l'utilisateur connecté.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->entreprise_id) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Vous n\'êtes associé à aucune entreprise.');
        }
        
        $entreprise = Entreprise::find($user->entreprise_id);
        
        if (!$entreprise) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Entreprise introuvable.');
        }
        
        return redirect()->route('configurations.presence.edit', $entreprise);
    }
}
