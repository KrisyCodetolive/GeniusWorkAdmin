<?php

namespace Tests\Feature\Depenses;

use App\Models\CategorieDepense;
use App\Models\DemandeDepense;
use App\Models\Entreprise;
use App\Models\ParametreDepense;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

trait DepenseTestHelpers
{
    protected Entreprise $entreprise;

    protected User $demandeur;

    protected User $comptable;

    protected User $ceo;

    protected CategorieDepense $categorie;

    // PNG 1x1, au format envoyé par le champ signature
    protected string $signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    protected function preparerCircuit(): void
    {
        Storage::fake('local');
        Notification::fake();

        $this->entreprise = $this->creerEntreprise('Entreprise Test');
        $this->demandeur = $this->creerUtilisateur('manager', $this->entreprise);
        $this->comptable = $this->creerUtilisateur('comptable', $this->entreprise);
        $this->ceo = $this->creerUtilisateur('admin', $this->entreprise);

        ParametreDepense::pour($this->entreprise->id)->update([
            'seuil_validation_ceo' => 500000,
            'ceo_user_id' => $this->ceo->id,
        ]);

        $this->categorie = CategorieDepense::withoutGlobalScopes()
            ->where('entreprise_id', $this->entreprise->id)
            ->firstOrFail();
    }

    protected function creerEntreprise(string $nom): Entreprise
    {
        return Entreprise::create(['nom' => $nom, 'email' => str()->slug($nom).'@test.local']);
    }

    protected function creerUtilisateur(string $role, Entreprise $entreprise): User
    {
        return User::factory()->create([
            'role' => $role,
            'statut' => 'actif',
            'entreprise_id' => $entreprise->id,
        ]);
    }

    protected function nouvelleDemande(float $montant, ?User $auteur = null): DemandeDepense
    {
        return DemandeDepense::create([
            'entreprise_id' => $this->entreprise->id,
            'cree_par_user_id' => ($auteur ?? $this->demandeur)->id,
            'categorie_depense_id' => $this->categorie->id,
            'objet' => 'Achat de fournitures',
            'montant' => $montant,
            'beneficiaire' => 'Fournisseur SARL',
        ]);
    }
}
