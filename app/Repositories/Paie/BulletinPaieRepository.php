<?php

namespace App\Repositories\Paie;

use App\Interfaces\Paie\BulletinPaieRepositoryInterface;
use App\Models\Paie\BulletinPaie;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BulletinPaieRepository implements BulletinPaieRepositoryInterface
{
    /**
     * @var BulletinPaie
     */
    protected $model;

    /**
     * Constructeur
     *
     * @param BulletinPaie $model
     */
    public function __construct(BulletinPaie $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritDoc}
     */
    public function tous(array $filtres = [])
    {
        $query = $this->model->newQuery();

        if (isset($filtres['entreprise_id'])) {
            $query->where('entreprise_id', $filtres['entreprise_id']);
        }

        if (isset($filtres['employeur_id'])) {
            $query->where('employeur_id', $filtres['employeur_id']);
        }

        if (isset($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (isset($filtres['date_debut']) && isset($filtres['date_fin'])) {
            $query->whereBetween('periode_debut', [$filtres['date_debut'], $filtres['date_fin']])
                  ->orWhereBetween('periode_fin', [$filtres['date_debut'], $filtres['date_fin']]);
        }

        if (isset($filtres['tri_par'])) {
            $direction = $filtres['tri_direction'] ?? 'desc';
            $query->orderBy($filtres['tri_par'], $direction);
        } else {
            $query->orderBy('periode_fin', 'desc');
        }

        if (isset($filtres['limite'])) {
            return $query->paginate($filtres['limite']);
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function trouverParId(string $id)
    {
        return $this->model->with(['employeur', 'entreprise', 'elements', 'generePar', 'validePar'])->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function trouverParEmployeur(string $employeurId, array $filtres = [])
    {
        $query = $this->model->where('employeur_id', $employeurId);

        if (isset($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (isset($filtres['date_debut']) && isset($filtres['date_fin'])) {
            $query->whereBetween('periode_debut', [$filtres['date_debut'], $filtres['date_fin']])
                  ->orWhereBetween('periode_fin', [$filtres['date_debut'], $filtres['date_fin']]);
        }

        if (isset($filtres['tri_par'])) {
            $direction = $filtres['tri_direction'] ?? 'desc';
            $query->orderBy($filtres['tri_par'], $direction);
        } else {
            $query->orderBy('periode_fin', 'desc');
        }

        if (isset($filtres['limite'])) {
            return $query->paginate($filtres['limite']);
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function trouverParEntreprise(string $entrepriseId, array $filtres = [])
    {
        $query = $this->model->where('entreprise_id', $entrepriseId);

        if (isset($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (isset($filtres['date_debut']) && isset($filtres['date_fin'])) {
            $query->whereBetween('periode_debut', [$filtres['date_debut'], $filtres['date_fin']])
                  ->orWhereBetween('periode_fin', [$filtres['date_debut'], $filtres['date_fin']]);
        }

        if (isset($filtres['tri_par'])) {
            $direction = $filtres['tri_direction'] ?? 'desc';
            $query->orderBy($filtres['tri_par'], $direction);
        } else {
            $query->orderBy('periode_fin', 'desc');
        }

        if (isset($filtres['limite'])) {
            return $query->paginate($filtres['limite']);
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function trouverParPeriode(string $debut, string $fin, array $filtres = [])
    {
        $query = $this->model->whereBetween('periode_debut', [$debut, $fin])
                             ->orWhereBetween('periode_fin', [$debut, $fin]);

        if (isset($filtres['entreprise_id'])) {
            $query->where('entreprise_id', $filtres['entreprise_id']);
        }

        if (isset($filtres['employeur_id'])) {
            $query->where('employeur_id', $filtres['employeur_id']);
        }

        if (isset($filtres['statut'])) {
            $query->where('statut', $filtres['statut']);
        }

        if (isset($filtres['tri_par'])) {
            $direction = $filtres['tri_direction'] ?? 'desc';
            $query->orderBy($filtres['tri_par'], $direction);
        } else {
            $query->orderBy('periode_fin', 'desc');
        }

        if (isset($filtres['limite'])) {
            return $query->paginate($filtres['limite']);
        }

        return $query->get();
    }

    /**
     * {@inheritDoc}
     */
    public function creer(array $donnees)
    {
        $bulletin = $this->model->create($donnees);

        // Créer les éléments de paie si présents
        if (isset($donnees['elements']) && is_array($donnees['elements'])) {
            foreach ($donnees['elements'] as $element) {
                $bulletin->elements()->create($element);
            }
        }

        return $bulletin;
    }

    /**
     * {@inheritDoc}
     */
    public function mettreAJour(string $id, array $donnees)
    {
        $bulletin = $this->trouverParId($id);

        if (!$bulletin) {
            return null;
        }

        // Ne pas permettre la modification d'un bulletin validé ou annulé
        if ($bulletin->statut !== 'brouillon') {
            throw new \Exception("Impossible de modifier un bulletin qui n'est pas en brouillon");
        }

        $bulletin->update($donnees);

        // Mettre à jour les éléments de paie si présents
        if (isset($donnees['elements']) && is_array($donnees['elements'])) {
            // Supprimer les éléments existants
            $bulletin->elements()->delete();

            // Créer les nouveaux éléments
            foreach ($donnees['elements'] as $element) {
                $bulletin->elements()->create($element);
            }
        }

        return $bulletin;
    }

    /**
     * {@inheritDoc}
     */
    public function supprimer(string $id)
    {
        $bulletin = $this->trouverParId($id);

        if (!$bulletin) {
            return false;
        }

        // Ne pas permettre la suppression d'un bulletin validé
        if ($bulletin->statut === 'validé') {
            throw new \Exception("Impossible de supprimer un bulletin validé");
        }

        // Supprimer les éléments de paie associés
        $bulletin->elements()->delete();

        // Supprimer le fichier PDF s'il existe
        if ($bulletin->fichier_pdf) {
            Storage::disk('public')->delete($bulletin->fichier_pdf);
        }

        return $bulletin->delete();
    }

    /**
     * {@inheritDoc}
     */
    public function valider(string $id, string $validateurId)
    {
        $bulletin = $this->trouverParId($id);

        if (!$bulletin) {
            return null;
        }

        // Ne pas permettre la validation d'un bulletin déjà validé ou annulé
        if ($bulletin->statut !== 'brouillon') {
            throw new \Exception("Impossible de valider un bulletin qui n'est pas en brouillon");
        }

        $bulletin->update([
            'statut' => 'validé',
            'valide_par' => $validateurId,
            'date_validation' => now()
        ]);

        // Générer le PDF du bulletin
        $this->genererPDF($id);

        return $bulletin;
    }

    /**
     * {@inheritDoc}
     */
    public function annuler(string $id, string $commentaire = null)
    {
        $bulletin = $this->trouverParId($id);

        if (!$bulletin) {
            return null;
        }

        // Ne pas permettre l'annulation d'un bulletin déjà annulé
        if ($bulletin->statut === 'annulé') {
            throw new \Exception("Ce bulletin est déjà annulé");
        }

        $bulletin->update([
            'statut' => 'annulé',
            'commentaire' => $commentaire
        ]);

        return $bulletin;
    }

    /**
     * {@inheritDoc}
     */
    public function genererPDF(string $id)
    {
        $bulletin = $this->trouverParId($id);

        if (!$bulletin) {
            return null;
        }

        // Générer le nom du fichier PDF
        $nomFichier = 'bulletins-paie/' . Str::slug($bulletin->reference) . '-' . time() . '.pdf';

        // Générer le PDF (à implémenter avec un service PDF)
        // Pour l'instant, on simule la génération du PDF
        $bulletin->update([
            'fichier_pdf' => $nomFichier
        ]);

        return $nomFichier;
    }
}
