<?php

namespace App\Interfaces\Paie;

use App\Models\Paie\BulletinPaie;
use Illuminate\Database\Eloquent\Collection;

interface BulletinPaieRepositoryInterface
{
    /**
     * Obtenir tous les bulletins de paie
     *
     * @param array $filtres
     * @return Collection
     */
    public function tous(array $filtres = []);

    /**
     * Obtenir un bulletin de paie par son ID
     *
     * @param string $id
     * @return BulletinPaie|null
     */
    public function trouverParId(string $id);

    /**
     * Obtenir les bulletins de paie par employeur
     *
     * @param string $employeurId
     * @param array $filtres
     * @return Collection
     */
    public function trouverParEmployeur(string $employeurId, array $filtres = []);

    /**
     * Obtenir les bulletins de paie par entreprise
     *
     * @param string $entrepriseId
     * @param array $filtres
     * @return Collection
     */
    public function trouverParEntreprise(string $entrepriseId, array $filtres = []);

    /**
     * Obtenir les bulletins de paie par période
     *
     * @param string $debut
     * @param string $fin
     * @param array $filtres
     * @return Collection
     */
    public function trouverParPeriode(string $debut, string $fin, array $filtres = []);

    /**
     * Créer un nouveau bulletin de paie
     *
     * @param array $donnees
     * @return BulletinPaie
     */
    public function creer(array $donnees);

    /**
     * Mettre à jour un bulletin de paie
     *
     * @param string $id
     * @param array $donnees
     * @return BulletinPaie
     */
    public function mettreAJour(string $id, array $donnees);

    /**
     * Supprimer un bulletin de paie
     *
     * @param string $id
     * @return bool
     */
    public function supprimer(string $id);

    /**
     * Valider un bulletin de paie
     *
     * @param string $id
     * @param string $validateurId
     * @return BulletinPaie
     */
    public function valider(string $id, string $validateurId);

    /**
     * Annuler un bulletin de paie
     *
     * @param string $id
     * @param string $commentaire
     * @return BulletinPaie
     */
    public function annuler(string $id, string $commentaire = null);

    /**
     * Générer le PDF d'un bulletin de paie
     *
     * @param string $id
     * @return string Chemin du fichier PDF généré
     */
    public function genererPDF(string $id);
}
