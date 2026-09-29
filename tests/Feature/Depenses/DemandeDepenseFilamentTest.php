<?php

namespace Tests\Feature\Depenses;

use App\Filament\Resources\CategorieDepenseResource\Pages\ListCategorieDepenses;
use App\Filament\Resources\DemandeDepenseResource\Pages\CreateDemandeDepense;
use App\Filament\Resources\DemandeDepenseResource\Pages\EditDemandeDepense;
use App\Filament\Resources\DemandeDepenseResource\Pages\ListDemandeDepenses;
use App\Filament\Resources\DemandeDepenseResource\Pages\ViewDemandeDepense;
use App\Filament\Resources\ParametreDepenseResource\Pages\ListParametreDepenses;
use App\Models\DemandeDepense;
use App\Models\User;
use App\Services\DepenseWorkflowService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DemandeDepenseFilamentTest extends TestCase
{
    use DepenseTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preparerCircuit();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_circuit_complet_depuis_les_ecrans(): void
    {
        // Le demandeur crée la demande : entreprise et auteur sont renseignés automatiquement.
        $this->actingAs($this->demandeur);
        Livewire::test(CreateDemandeDepense::class)
            ->fillForm([
                'objet' => 'Achat imprimante',
                'montant' => 750000,
                'beneficiaire' => 'Bureau Plus',
                'categorie_depense_id' => $this->categorie->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $demande = DemandeDepense::where('objet', 'Achat imprimante')->firstOrFail();
        $this->assertSame($this->entreprise->id, $demande->entreprise_id);
        $this->assertSame($this->demandeur->id, $demande->cree_par_user_id);

        Livewire::test(ViewDemandeDepense::class, ['record' => $demande->getRouteKey()])
            ->assertActionVisible('soumettre')
            ->assertActionHidden('valider')
            ->callAction('soumettre');
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->fresh()->statut);

        // Le comptable voit la demande dans son onglet et la signe.
        $this->actingAs($this->comptable);
        Livewire::test(ListDemandeDepenses::class)
            ->set('activeTab', 'a_valider')
            ->assertCanSeeTableRecords([$demande]);
        Livewire::test(ViewDemandeDepense::class, ['record' => $demande->getRouteKey()])
            ->assertActionVisible('valider')
            ->assertActionVisible('rejeter')
            ->assertActionHidden('soumettre')
            ->callAction('valider', ['signature' => $this->signature, 'commentaire' => 'Conforme']);
        $demande->refresh();
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_CEO, $demande->statut);

        // Montant au-dessus du seuil : le CEO signe à son tour.
        $this->actingAs($this->ceo);
        Livewire::test(ViewDemandeDepense::class, ['record' => $demande->getRouteKey()])
            ->assertSee('Historique et signatures')
            ->callAction('valider', ['signature' => $this->signature]);
        $this->assertSame(DemandeDepense::STATUT_APPROUVEE, $demande->fresh()->statut);

        // Le comptable enregistre le paiement.
        $this->actingAs($this->comptable);
        Livewire::test(ViewDemandeDepense::class, ['record' => $demande->getRouteKey()])
            ->assertActionVisible('bonSortie')
            ->callAction('decaisser', [
                'mode_paiement' => 'cheque',
                'reference_paiement' => 'CHQ-42',
                'date_paiement' => now()->toDateString(),
            ]);
        $this->assertSame(DemandeDepense::STATUT_PAYEE, $demande->fresh()->statut);

        $this->get(route('depenses.bon-sortie', $demande))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_un_simple_employe_ne_voit_que_ses_demandes(): void
    {
        $autre = $this->creerUtilisateur('manager', $this->entreprise);
        $sienne = $this->nouvelleDemande(1000);
        $pasLaSienne = $this->nouvelleDemande(2000, $autre);

        $this->actingAs($this->demandeur);

        Livewire::test(ListDemandeDepenses::class)
            ->assertCanSeeTableRecords([$sienne])
            ->assertCanNotSeeTableRecords([$pasLaSienne]);
    }

    public function test_les_fichiers_prives_sont_refuses_aux_autres_entreprises(): void
    {
        $demande = app(DepenseWorkflowService::class)
            ->validerComptabilite(
                app(DepenseWorkflowService::class)->soumettre($this->nouvelleDemande(1000), $this->demandeur),
                $this->comptable,
                $this->signature
            );
        $validation = $demande->approbation('comptabilite');

        $this->actingAs($this->comptable)->get(route('depenses.signature', $validation))->assertOk();

        $etranger = $this->creerUtilisateur('comptable', $this->creerEntreprise('Autre Entreprise'));
        $this->actingAs($etranger)->get(route('depenses.signature', $validation))->assertForbidden();
    }

    public function test_les_ecrans_s_affichent_pour_le_super_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'statut' => 'actif']));
        $demande = $this->nouvelleDemande(1000);

        Livewire::test(ListDemandeDepenses::class)->assertSuccessful()->assertCanSeeTableRecords([$demande]);
        Livewire::test(CreateDemandeDepense::class)->assertSuccessful();
        Livewire::test(ViewDemandeDepense::class, ['record' => $demande->getRouteKey()])->assertSuccessful();
        Livewire::test(EditDemandeDepense::class, ['record' => $demande->getRouteKey()])
            ->assertSuccessful()
            ->assertFormSet(['objet' => $demande->objet, 'montant' => $demande->montant])
            ->fillForm(['objet' => 'Objet corrigé'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('Objet corrigé', $demande->fresh()->objet);
        Livewire::test(ListCategorieDepenses::class)->assertSuccessful();
        Livewire::test(ListParametreDepenses::class)->assertSuccessful();
    }

    public function test_circuit_complet_depuis_les_lignes_de_la_liste(): void
    {
        $demande = $this->nouvelleDemande(800000);

        // Le demandeur soumet depuis la ligne ; il ne voit pas les actions des validateurs.
        $this->actingAs($this->demandeur);
        Livewire::test(ListDemandeDepenses::class)
            ->assertTableActionVisible('soumettre', $demande)
            ->assertTableActionHidden('valider', $demande)
            ->assertTableActionHidden('decaisser', $demande)
            ->callTableAction('soumettre', $demande);
        $demande->refresh();
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->statut);

        // Le comptable signe depuis la ligne.
        $this->actingAs($this->comptable);
        Livewire::test(ListDemandeDepenses::class)
            ->assertTableActionHidden('soumettre', $demande)
            ->assertTableActionVisible('valider', $demande)
            ->assertTableActionVisible('renvoyer', $demande)
            ->assertTableActionVisible('rejeter', $demande)
            ->callTableAction('valider', $demande, data: ['signature' => $this->signature]);
        $demande->refresh();
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_CEO, $demande->statut);

        // Le CEO signe à son tour.
        $this->actingAs($this->ceo);
        Livewire::test(ListDemandeDepenses::class)
            ->callTableAction('valider', $demande, data: ['signature' => $this->signature]);
        $demande->refresh();
        $this->assertSame(DemandeDepense::STATUT_APPROUVEE, $demande->statut);

        // Le comptable paie ; le bon de sortie devient disponible dans le menu de la ligne.
        $this->actingAs($this->comptable);
        Livewire::test(ListDemandeDepenses::class)
            ->assertTableActionHidden('valider', $demande)
            ->assertTableActionVisible('bonSortie', $demande)
            ->callTableAction('decaisser', $demande, data: [
                'mode_paiement' => 'mobile_money',
                'reference_paiement' => 'MM-123',
                'date_paiement' => now()->toDateString(),
            ])
            ->assertHasNoTableActionErrors();
        $this->assertSame(DemandeDepense::STATUT_PAYEE, $demande->fresh()->statut);
    }

    public function test_renvoyer_et_rejeter_depuis_le_menu_de_la_ligne(): void
    {
        $service = app(DepenseWorkflowService::class);
        $aCorriger = $service->soumettre($this->nouvelleDemande(1000), $this->demandeur);
        $aRejeter = $service->soumettre($this->nouvelleDemande(2000), $this->demandeur);
        $this->actingAs($this->comptable);

        Livewire::test(ListDemandeDepenses::class)
            ->callTableAction('renvoyer', $aCorriger, data: ['motif' => 'Facture manquante'])
            ->callTableAction('rejeter', $aRejeter, data: ['motif' => 'Hors budget'])
            ->callTableAction('rejeter', $aCorriger, data: ['motif' => 'x']);

        $this->assertSame(DemandeDepense::STATUT_BROUILLON, $aCorriger->fresh()->statut);
        $this->assertSame(DemandeDepense::STATUT_REJETEE, $aRejeter->fresh()->statut);
    }

    public function test_boutons_de_la_ligne_selon_le_role_et_le_statut(): void
    {
        $service = app(DepenseWorkflowService::class);
        $brouillon = $this->nouvelleDemande(1000);
        $enAttente = $service->soumettre($this->nouvelleDemande(2000), $this->demandeur);
        $approuvee = $service->validerComptabilite($service->soumettre($this->nouvelleDemande(3000), $this->demandeur), $this->comptable, $this->signature);

        $this->actingAs($this->demandeur);
        Livewire::test(ListDemandeDepenses::class)
            ->assertTableActionVisible('soumettre', $brouillon)
            ->assertTableActionVisible('edit', $brouillon)
            ->assertTableActionVisible('delete', $brouillon)
            ->assertTableActionVisible('annuler', $brouillon)
            ->assertTableActionVisible('annuler', $enAttente)
            ->assertTableActionHidden('edit', $enAttente)
            ->assertTableActionHidden('delete', $enAttente)
            ->assertTableActionHidden('rejeter', $enAttente)
            ->assertTableActionVisible('bonSortie', $approuvee)
            ->assertTableActionHidden('decaisser', $approuvee);

        $this->actingAs($this->comptable);
        Livewire::test(ListDemandeDepenses::class)
            ->assertTableActionVisible('valider', $enAttente)
            ->assertTableActionVisible('renvoyer', $enAttente)
            ->assertTableActionVisible('rejeter', $enAttente)
            ->assertTableActionHidden('annuler', $enAttente)
            ->assertTableActionHidden('delete', $brouillon)
            ->assertTableActionVisible('decaisser', $approuvee)
            ->assertTableActionVisible('bonSortie', $approuvee)
            ->assertTableActionVisible('view', $approuvee);
    }

    public function test_l_auteur_supprime_son_brouillon_depuis_la_ligne(): void
    {
        $brouillon = $this->nouvelleDemande(1000);
        $this->actingAs($this->demandeur);

        Livewire::test(ListDemandeDepenses::class)
            ->callTableAction('delete', $brouillon);

        $this->assertSoftDeleted($brouillon);
    }

    public function test_la_signature_est_obligatoire_depuis_la_ligne(): void
    {
        $demande = app(DepenseWorkflowService::class)->soumettre($this->nouvelleDemande(1000), $this->demandeur);
        $this->actingAs($this->comptable);

        Livewire::test(ListDemandeDepenses::class)
            ->callTableAction('valider', $demande, data: ['signature' => null])
            ->assertHasTableActionErrors(['signature' => 'required']);

        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->fresh()->statut);
    }
}
