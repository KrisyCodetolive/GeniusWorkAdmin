<?php

namespace Tests\Feature\Depenses;

use App\Filament\Resources\CategorieDepenseResource\Pages\EditCategorieDepense;
use App\Filament\Resources\CategorieDepenseResource\RelationManagers\DemandesRelationManager;
use App\Filament\Resources\DemandeDepenseResource\Pages\CreateDemandeDepense;
use App\Filament\Resources\DemandeDepenseResource\Pages\ViewDemandeDepense;
use App\Filament\Resources\DemandeDepenseResource\RelationManagers\JustificatifsRelationManager;
use App\Filament\Resources\DemandeDepenseResource\RelationManagers\ValidationsRelationManager;
use App\Models\CategorieDepense;
use App\Models\DemandeDepense;
use App\Models\Departement;
use App\Services\DepenseWorkflowService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DepenseRelationManagersTest extends TestCase
{
    use DepenseTestHelpers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preparerCircuit();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_l_assistant_bloque_l_etape_suivante_si_l_etape_en_cours_est_incomplete(): void
    {
        $this->actingAs($this->demandeur);

        Livewire::test(CreateDemandeDepense::class)
            ->assertWizardCurrentStep(1)
            ->goToNextWizardStep()
            ->assertHasFormErrors(['objet' => 'required', 'categorie_depense_id' => 'required'])
            ->assertWizardCurrentStep(1)
            ->fillForm(['objet' => 'Billets d\'avion', 'categorie_depense_id' => $this->categorie->id])
            ->goToNextWizardStep()
            ->assertHasNoFormErrors()
            ->assertWizardCurrentStep(2);
    }

    public function test_le_recapitulatif_annonce_le_circuit_de_validation(): void
    {
        $this->actingAs($this->demandeur);

        Livewire::test(CreateDemandeDepense::class)
            ->fillForm(['objet' => 'Serveur', 'montant' => 900000, 'beneficiaire' => 'Dell', 'categorie_depense_id' => $this->categorie->id])
            ->assertSee('Comptabilité puis CEO')
            ->fillForm(['montant' => 20000])
            ->assertSee('Comptabilité uniquement');
    }

    public function test_justificatifs_ajoutables_en_brouillon_puis_verrouilles_apres_soumission(): void
    {
        $demande = $this->nouvelleDemande(100000);
        $this->actingAs($this->demandeur);

        Livewire::test(JustificatifsRelationManager::class, ['ownerRecord' => $demande, 'pageClass' => ViewDemandeDepense::class])
            ->assertTableActionVisible('create')
            ->callTableAction('create', data: [
                'type' => 'facture',
                'fichier' => UploadedFile::fake()->create('facture.pdf', 120, 'application/pdf'),
            ])
            ->assertHasNoTableActionErrors();

        $justificatif = $demande->justificatifs()->firstOrFail();
        $this->assertSame('facture.pdf', $justificatif->nom_original);
        Storage::disk('local')->assertExists($justificatif->fichier);

        $demande = app(DepenseWorkflowService::class)->soumettre($demande, $this->demandeur);

        Livewire::test(JustificatifsRelationManager::class, ['ownerRecord' => $demande, 'pageClass' => ViewDemandeDepense::class])
            ->assertCanSeeTableRecords([$justificatif])
            ->assertTableActionHidden('create')
            ->assertTableActionHidden('delete', $justificatif);
    }

    public function test_supprimer_un_justificatif_supprime_aussi_le_fichier(): void
    {
        $demande = $this->nouvelleDemande(100000);
        Storage::disk('local')->put('depenses/justificatifs/devis.pdf', '%PDF-test');
        $justificatif = $demande->justificatifs()->create(['fichier' => 'depenses/justificatifs/devis.pdf', 'nom_original' => 'devis.pdf', 'type' => 'devis']);
        $this->actingAs($this->demandeur);

        Livewire::test(JustificatifsRelationManager::class, ['ownerRecord' => $demande, 'pageClass' => ViewDemandeDepense::class])
            ->callTableAction('delete', $justificatif);

        $this->assertModelMissing($justificatif);
        Storage::disk('local')->assertMissing('depenses/justificatifs/devis.pdf');
    }

    public function test_un_valideur_ne_peut_pas_toucher_aux_justificatifs(): void
    {
        $demande = $this->nouvelleDemande(100000);
        $this->actingAs($this->comptable);

        Livewire::test(JustificatifsRelationManager::class, ['ownerRecord' => $demande, 'pageClass' => ViewDemandeDepense::class])
            ->assertTableActionHidden('create');
    }

    public function test_l_historique_liste_les_etapes_en_lecture_seule(): void
    {
        $service = app(DepenseWorkflowService::class);
        $demande = $service->validerComptabilite(
            $service->soumettre($this->nouvelleDemande(100000), $this->demandeur),
            $this->comptable,
            $this->signature
        );
        $this->actingAs($this->comptable);

        Livewire::test(ValidationsRelationManager::class, ['ownerRecord' => $demande, 'pageClass' => ViewDemandeDepense::class])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($demande->validations)
            ->assertSee('Comptabilité')
            ->assertTableActionDoesNotExist('create')
            ->assertTableActionDoesNotExist('edit')
            ->assertTableActionDoesNotExist('delete');
    }

    public function test_une_categorie_liste_ses_demandes(): void
    {
        $demande = $this->nouvelleDemande(42000);
        $autreCategorie = $this->categorie->replicate()->fill(['nom' => 'Autre']);
        $autreCategorie->save();
        $horsCategorie = DemandeDepense::create([
            'entreprise_id' => $this->entreprise->id,
            'cree_par_user_id' => $this->demandeur->id,
            'categorie_depense_id' => $autreCategorie->id,
            'objet' => 'Autre',
            'montant' => 1000,
            'beneficiaire' => 'X',
        ]);
        $this->actingAs($this->comptable);

        Livewire::test(DemandesRelationManager::class, ['ownerRecord' => $this->categorie, 'pageClass' => EditCategorieDepense::class])
            ->assertCanSeeTableRecords([$demande])
            ->assertCanNotSeeTableRecords([$horsCategorie]);
    }

    public function test_les_pages_affichent_les_onglets_de_relations(): void
    {
        $demande = $this->nouvelleDemande(100000);
        $this->actingAs($this->demandeur);

        $this->get(ViewDemandeDepense::getUrl(['record' => $demande]))
            ->assertOk()
            ->assertSee('Justificatifs')
            ->assertSee('Historique et signatures');

        $this->actingAs($this->comptable)
            ->get(EditCategorieDepense::getUrl(['record' => $this->categorie]))
            ->assertOk()
            ->assertSee('Demandes de dépense');
    }

    public function test_le_comptable_cree_une_categorie_depuis_la_liste_deroulante(): void
    {
        $this->actingAs($this->comptable);

        $page = Livewire::test(CreateDemandeDepense::class)
            ->assertFormComponentActionVisible('categorie_depense_id', 'createOption')
            ->callFormComponentAction('categorie_depense_id', 'createOption', data: [
                'nom' => 'Loyer',
                'code_comptable' => '622',
            ])
            ->assertHasNoFormComponentActionErrors();

        $categorie = CategorieDepense::where('nom', 'Loyer')->firstOrFail();
        $this->assertSame($this->entreprise->id, $categorie->entreprise_id);
        $this->assertSame('622', $categorie->code_comptable);
        $page->assertFormSet(['categorie_depense_id' => $categorie->id]);
    }

    public function test_l_admin_cree_un_departement_depuis_la_liste_deroulante(): void
    {
        $this->actingAs($this->ceo);

        $page = Livewire::test(CreateDemandeDepense::class)
            ->assertFormComponentActionVisible('departement_id', 'createOption')
            ->callFormComponentAction('departement_id', 'createOption', data: ['nom' => 'Logistique'])
            ->assertHasNoFormComponentActionErrors();

        $departement = Departement::where('nom', 'Logistique')->firstOrFail();
        $this->assertSame($this->entreprise->id, $departement->entreprise_id);
        $this->assertSame('LOG001', $departement->code);
        $page->assertFormSet(['departement_id' => $departement->id]);
    }

    public function test_un_simple_demandeur_ne_peut_pas_creer_de_categorie_ni_de_departement(): void
    {
        $this->actingAs($this->demandeur);

        Livewire::test(CreateDemandeDepense::class)
            ->assertFormComponentActionHidden('categorie_depense_id', 'createOption')
            ->assertFormComponentActionHidden('departement_id', 'createOption');
    }
}
