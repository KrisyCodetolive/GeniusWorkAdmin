<?php

namespace App\Http\Controllers;

use App\Models\DemandeDepense;
use App\Models\JustificatifDepense;
use App\Models\ValidationDepense;
use App\Services\DepenseWorkflowService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/**
 * Fichiers privés des dépenses (disque local, jamais exposés publiquement) :
 * chaque téléchargement repasse par DemandeDepensePolicy::view.
 */
class DepenseFichierController extends Controller
{
    public function justificatif(JustificatifDepense $justificatif)
    {
        Gate::authorize('view', $justificatif->demande);

        return $this->servir($justificatif->fichier, $justificatif->nom_original);
    }

    public function signature(ValidationDepense $validation)
    {
        Gate::authorize('view', $validation->demande);

        return $this->servir($validation->signature_path);
    }

    public function preuvePaiement(DemandeDepense $demande)
    {
        Gate::authorize('view', $demande);

        return $this->servir($demande->preuve_paiement);
    }

    public function bonSortie(DemandeDepense $demande, DepenseWorkflowService $service)
    {
        Gate::authorize('telechargerBon', $demande);

        $nom = "bon-sortie-{$demande->reference}.pdf";

        // Une demande payée a un bon archivé au moment du paiement ; sinon on le génère.
        if ($demande->pdf_bon_sortie && Storage::disk(DepenseWorkflowService::DISQUE)->exists($demande->pdf_bon_sortie)) {
            return $this->servir($demande->pdf_bon_sortie, $nom);
        }

        return $service->bonDeSortie($demande)->download($nom);
    }

    private function servir(?string $chemin, ?string $nom = null)
    {
        abort_unless($chemin && Storage::disk(DepenseWorkflowService::DISQUE)->exists($chemin), 404);

        return Storage::disk(DepenseWorkflowService::DISQUE)->response($chemin, $nom);
    }
}
