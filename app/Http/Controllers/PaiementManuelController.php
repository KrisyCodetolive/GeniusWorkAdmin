<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Services\Paiement\ManuelService;
use App\Services\Paiement\PaiementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaiementManuelController extends Controller
{
    protected $manuelService;
    protected $paiementService;

    public function __construct(ManuelService $manuelService, PaiementService $paiementService)
    {
        $this->manuelService = $manuelService;
        $this->paiementService = $paiementService;
    }

    /**
     * Afficher les instructions pour le paiement manuel
     *
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function instructions($reference)
    {
        $paiement = Paiement::where('reference', $reference)
            ->where('passerelle', Paiement::PASSERELLE_MANUEL)
            ->firstOrFail();
            
            return view('paiements.manuel.instructions', [
            'paiement' => $paiement,
            'instructions' => $paiement->meta_donnees['instructions'] ?? []
        ]);
    }

    /**
     * Télécharger le justificatif de paiement
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function telechargerJustificatif(Request $request, $reference)
    {
        $paiement = Paiement::where('reference', $reference)
            ->where('passerelle', Paiement::PASSERELLE_MANUEL)
            ->firstOrFail();
            
        // Vérifier que l'utilisateur a le droit de voir ce paiement
        if (Auth::id() !== $paiement->initiateur_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ce paiement');
        }

        $request->validate([
            'justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'commentaire' => 'nullable|string|max:500'
        ]);

        try {
            // Enregistrer le fichier
            $path = $request->file('justificatif')->store('justificatifs/paiements/' . $paiement->id, 'public');
            
            // Mettre à jour le paiement
            $metaDonnees = $paiement->meta_donnees ?? [];
            $metaDonnees['preuve_paiement'] = [
                'path' => $path,
                'nom_original' => $request->file('justificatif')->getClientOriginalName(),
                'type' => $request->file('justificatif')->getMimeType(),
                'taille' => $request->file('justificatif')->getSize(),
                'date_upload' => now()->toIso8601String()
            ];
            
            $paiement->update([
                'meta_donnees' => $metaDonnees,
                'commentaire' => $request->commentaire ?? $paiement->commentaire
            ]);

            // Notifier les administrateurs
            // TODO: Implémenter la notification

            return redirect()->back()->with('success', 'Justificatif téléchargé avec succès. Notre équipe va vérifier votre paiement dans les plus brefs délais.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du justificatif', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors du téléchargement du justificatif.');
        }
    }

    /**
     * Valider un paiement manuel (réservé aux administrateurs)
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function valider(Request $request, $reference)
    {
        // Vérifier que l'utilisateur est administrateur
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasPermissionTo('validate-payments')) {
            abort(403, 'Vous n\'êtes pas autorisé à valider les paiements');
        }

        $paiement = Paiement::where('reference', $reference)
            ->where('passerelle', Paiement::PASSERELLE_MANUEL)
            ->firstOrFail();

        $request->validate([
            'commentaire' => 'nullable|string|max:500'
        ]);

        try {
            $paiement->valider(Auth::id(), $request->commentaire);
            
            // Notifier l'utilisateur
            // TODO: Implémenter la notification

            return redirect()->back()->with('success', 'Paiement validé avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la validation du paiement manuel', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors de la validation du paiement.');
        }
    }

    /**
     * Rejeter un paiement manuel (réservé aux administrateurs)
     *
     * @param Request $request
     * @param string $reference
     * @return \Illuminate\Http\Response
     */
    public function rejeter(Request $request, $reference)
    {
        // Vérifier que l'utilisateur est administrateur
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasPermissionTo('validate-payments')) {
            abort(403, 'Vous n\'êtes pas autorisé à rejeter les paiements');
        }

        $paiement = Paiement::where('reference', $reference)
            ->where('passerelle', Paiement::PASSERELLE_MANUEL)
            ->firstOrFail();

        $request->validate([
            'raison' => 'required|string|max:500'
        ]);

        try {
            $paiement->rejeter(Auth::id(), $request->raison);
            
            // Notifier l'utilisateur
            // TODO: Implémenter la notification

            return redirect()->back()->with('success', 'Paiement rejeté avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet du paiement manuel', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'paiement_id' => $paiement->id
            ]);

            return redirect()->back()->with('error', 'Une erreur est survenue lors du rejet du paiement.');
        }
    }
}
