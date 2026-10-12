<?php

namespace Tests\Feature\Depenses;

use App\Models\DemandeDepense;
use App\Services\DepenseWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EmployeDepensePortailTest extends TestCase
{
    use DepenseTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preparerCircuit();
    }

    public function test_parcours_complet_de_l_employe(): void
    {
        $service = app(DepenseWorkflowService::class);

        // Création du brouillon avec une pièce jointe.
        $this->actingAs($this->demandeur)
            ->get(route('employe.depenses.create'))
            ->assertOk()
            ->assertSee('Nouvelle demande');

        $this->post(route('employe.depenses.store'), [
            'objet' => 'Achat clavier',
            'categorie_depense_id' => $this->categorie->id,
            'montant' => 45000,
            'beneficiaire' => 'Boutique X',
            'justificatifs' => [['type' => 'devis', 'fichier' => UploadedFile::fake()->create('devis.pdf', 50, 'application/pdf')]],
        ])->assertRedirect();

        $demande = DemandeDepense::where('objet', 'Achat clavier')->firstOrFail();
        $this->assertSame(DemandeDepense::STATUT_BROUILLON, $demande->statut);
        $this->assertCount(1, $demande->justificatifs);

        $this->get(route('employe.depenses.index'))->assertOk()->assertSee($demande->reference);
        $this->get(route('employe.depenses.show', $demande))->assertOk()->assertSee('Soumettre');

        // Soumission, puis validation comptable.
        $this->post(route('employe.depenses.soumettre', $demande))->assertRedirect(route('employe.depenses.show', $demande));
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->fresh()->statut);
        $service->validerComptabilite($demande->fresh(), $this->comptable, $this->signature);

        // Signature de la décharge.
        $this->actingAs($this->demandeur)
            ->get(route('employe.depenses.show', $demande))
            ->assertOk()
            ->assertSee('Votre décharge');
        $this->post(route('employe.depenses.signer', $demande), ['signature' => $this->signature])
            ->assertRedirect(route('employe.depenses.show', $demande));

        // Paiement par la comptabilité, puis justification par l'employé.
        $service->decaisser($demande->fresh(), $this->comptable, ['mode_paiement' => 'especes']);

        $this->actingAs($this->demandeur)
            ->post(route('employe.depenses.justification', $demande))
            ->assertSessionHas('error');

        $this->post(route('employe.depenses.justificatifs.store', $demande), [
            'type' => 'recu',
            'fichier' => UploadedFile::fake()->image('recu.jpg'),
        ])->assertSessionHas('success');

        $this->post(route('employe.depenses.justification', $demande))->assertSessionHas('success');
        $this->assertSame(DemandeDepense::STATUT_JUSTIFICATION_SOUMISE, $demande->fresh()->statut);

        $service->validerJustification($demande->fresh(), $this->comptable);
        $this->get(route('employe.depenses.show', $demande))->assertOk()->assertSee('Clôturée');
    }

    public function test_un_employe_ne_voit_pas_la_demande_d_un_autre(): void
    {
        $autre = $this->creerUtilisateur('manager', $this->entreprise);
        $demande = $this->nouvelleDemande(1000, $autre);

        $this->actingAs($this->demandeur)
            ->get(route('employe.depenses.show', $demande))
            ->assertForbidden();

        $this->get(route('employe.depenses.index'))->assertDontSee($demande->reference);
    }

    public function test_annulation_et_validation_des_champs(): void
    {
        $this->actingAs($this->demandeur)
            ->post(route('employe.depenses.store'), ['objet' => ''])
            ->assertSessionHasErrors(['objet', 'categorie_depense_id', 'montant', 'beneficiaire']);

        $demande = $this->nouvelleDemande(1000);
        $this->post(route('employe.depenses.annuler', $demande))->assertSessionHas('success');
        $this->assertSame(DemandeDepense::STATUT_ANNULEE, $demande->fresh()->statut);
    }
}
