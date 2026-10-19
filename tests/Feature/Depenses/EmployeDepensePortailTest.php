<?php

namespace Tests\Feature\Depenses;

use App\Models\DemandeDepense;
use App\Models\Departement;
use App\Models\Employeur;
use App\Models\User;
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

        // Création : la demande part directement à la comptabilité, avec sa pièce jointe.
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
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->statut);
        $this->assertCount(1, $demande->justificatifs);

        $this->get(route('employe.depenses.index'))->assertOk()->assertSee($demande->reference);
        $this->get(route('employe.depenses.show', $demande))->assertOk()->assertSee('La comptabilité examine votre demande');

        // Validation comptable.
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

    public function test_un_brouillon_renvoye_peut_etre_envoye_depuis_la_page_de_suivi(): void
    {
        $demande = $this->nouvelleDemande(1000);

        $this->actingAs($this->demandeur)
            ->get(route('employe.depenses.show', $demande))
            ->assertOk()
            ->assertSee('Envoyer la demande');

        $this->post(route('employe.depenses.soumettre', $demande))->assertRedirect(route('employe.depenses.show', $demande));
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->fresh()->statut);
    }

    public function test_le_suivi_n_affiche_que_les_demandes_en_cours(): void
    {
        $enCours = $this->nouvelleDemande(1000);
        $cloturee = $this->nouvelleDemande(2000);
        $cloturee->forceFill(['statut' => DemandeDepense::STATUT_CLOTUREE])->save();
        $annulee = $this->nouvelleDemande(3000);
        $annulee->forceFill(['statut' => DemandeDepense::STATUT_ANNULEE])->save();
        $rejeteeRecente = $this->nouvelleDemande(4000);
        $rejeteeRecente->forceFill(['statut' => DemandeDepense::STATUT_REJETEE])->save();
        $rejeteeAncienne = $this->nouvelleDemande(5000);
        $rejeteeAncienne->forceFill(['statut' => DemandeDepense::STATUT_REJETEE, 'updated_at' => now()->subWeeks(2)])->saveQuietly();

        $this->actingAs($this->demandeur)
            ->get(route('employe.depenses.index'))
            ->assertOk()
            ->assertSee($enCours->reference)
            ->assertSee($rejeteeRecente->reference)
            ->assertDontSee($cloturee->reference)
            ->assertDontSee($annulee->reference)
            ->assertDontSee($rejeteeAncienne->reference);
    }

    public function test_la_demande_est_rattachee_au_departement_et_a_la_personne_identifiee(): void
    {
        [$departement, $membres] = $this->departementAvecMembres();

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[1]->id]);

        // Le bandeau annonce le département et la personne ; le formulaire ne redemande pas son nom.
        $this->get(route('employe.depenses.create'))
            ->assertOk()
            ->assertSee($departement->nom)
            ->assertSee($membres[1]->prenom.' '.$membres[1]->nom)
            ->assertDontSee('Qui fait la demande')
            ->assertDontSee('logo2.png');

        $this->post(route('employe.depenses.store'), [
            'objet' => 'Achat clavier',
            'categorie_depense_id' => $this->categorie->id,
            'montant' => 45000,
            'beneficiaire' => 'Boutique X',
        ])->assertRedirect();

        $demande = DemandeDepense::where('objet', 'Achat clavier')->firstOrFail();
        $this->assertSame($departement->id, $demande->departement_id);
        $this->assertSame($membres[1]->id, $demande->demandeur_id);
        $this->assertTrue($demande->creePar->estCompteDepartement());
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->statut);

        $this->get(route('employe.depenses.show', $demande))->assertOk()->assertSee($membres[1]->nom);
    }

    public function test_il_faut_nommer_une_personne_du_departement(): void
    {
        [$departement, $membres] = $this->departementAvecMembres();
        $etranger = $this->creerEmploye($this->creerDepartement('Autre', 'AUT'), 'X9');

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id])
            ->assertSessionHasErrors('demandeur_id');
        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $etranger->id])
            ->assertSessionHasErrors('demandeur_id');
        $this->assertGuest();

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[0]->id])
            ->assertRedirect(route('employe.depenses.index'));
        $this->assertAuthenticated();
    }

    public function test_tout_membre_du_departement_suit_les_demandes_du_departement(): void
    {
        [$departement, $membres] = $this->departementAvecMembres();

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[0]->id]);
        $this->post(route('employe.depenses.store'), [
            'objet' => 'Achat clavier',
            'categorie_depense_id' => $this->categorie->id,
            'montant' => 45000,
            'beneficiaire' => 'Boutique X',
        ]);
        $demande = DemandeDepense::where('objet', 'Achat clavier')->firstOrFail();

        // Un collègue, sur un autre appareil, retrouve la demande avec le même code.
        $this->post(route('employe.depenses.quitter'));
        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[1]->id]);
        $this->get(route('employe.depenses.index'))->assertOk()->assertSee($demande->reference);
        $this->get(route('employe.depenses.show', $demande))->assertOk();
    }

    public function test_les_demandes_d_un_autre_departement_restent_invisibles(): void
    {
        [$logistique, $membres] = $this->departementAvecMembres();
        $compta = $this->creerDepartement('Compta', 'CPT');

        $demande = $this->nouvelleDemande(1000, $compta->compteDepenses());

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $logistique->id, 'demandeur_id' => $membres[0]->id]);
        $this->get(route('employe.depenses.show', $demande))->assertForbidden();
        $this->get(route('employe.depenses.index'))->assertDontSee($demande->reference);
    }

    public function test_l_acces_se_fait_sans_connexion_avec_le_departement_et_le_nom(): void
    {
        [$departement, $membres] = $this->departementAvecMembres();

        $this->get('/depense')->assertRedirect(route('employe.depenses.acces'));
        $this->get(route('employe.depenses.acces'))
            ->assertOk()
            ->assertSee($departement->nom)
            ->assertSee($membres[0]->nom);

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[0]->id])
            ->assertRedirect(route('employe.depenses.index'));
        $this->assertTrue(auth()->user()->estCompteDepartement());
        $this->get(route('employe.depenses.index'))->assertOk()->assertSee($departement->nom);
    }

    public function test_un_departement_sans_employe_actif_n_est_pas_propose(): void
    {
        $vide = $this->creerDepartement('Vide', 'VID');

        $this->get(route('employe.depenses.acces'))->assertOk()->assertDontSee('Vide');
    }

    public function test_une_session_du_portail_est_confinee_au_portail(): void
    {
        [$departement, $membres] = $this->departementAvecMembres();

        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[0]->id]);

        // Back-office Filament fermé, et le reste de l'application renvoie vers le portail.
        $this->get('/admin')->assertForbidden();
        $this->get(route('logout'))->assertRedirect(route('employe.depenses.index'));
        $this->get(route('employe.depenses.index'))->assertOk();

        $this->post(route('employe.depenses.quitter'))->assertRedirect(route('employe.depenses.acces'));
        $this->assertGuest();
    }

    public function test_se_connecter_au_back_office_apres_le_portail_n_est_pas_bloque(): void
    {
        [$departement, $membres] = $this->departementAvecMembres();

        // Même navigateur : accès au portail, puis connexion d'un comptable au back-office.
        $this->post(route('employe.depenses.acces.verifier'), ['departement_id' => $departement->id, 'demandeur_id' => $membres[0]->id]);
        $this->post(route('employe.depenses.quitter'));

        $this->actingAs($this->comptable)->get('/admin')->assertSuccessful();
        $this->get(route('employe.depenses.index'))->assertOk()->assertDontSee($departement->nom);
    }

    public function test_le_portail_est_installable_en_pwa(): void
    {
        $this->get(route('employe.depenses.manifeste'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('start_url', '/depense')
            ->assertJsonPath('display', 'standalone');

        $this->get(route('employe.depenses.sw'))
            ->assertOk()
            ->assertHeader('Service-Worker-Allowed', '/depense');

        $this->get(route('employe.depenses.hors-ligne'))->assertOk()->assertSee('Pas de connexion');
        $this->get(route('employe.depenses.acces'))->assertOk()->assertSee('rel="manifest"', false);
    }

    public function test_l_ancienne_adresse_redirige_vers_la_nouvelle(): void
    {
        $this->get('/employe/depenses')->assertRedirect('/depense');
        $this->get('/employe/depenses/nouvelle')->assertRedirect('/depense/nouvelle');
        $this->get('/depenses')->assertRedirect('/depense');
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

    private function creerDepartement(string $nom, string $code): Departement
    {
        return Departement::create(['entreprise_id' => $this->entreprise->id, 'nom' => $nom, 'code' => $code, 'statut' => 'actif']);
    }

    private function creerEmploye(Departement $departement, string $code): Employeur
    {
        return Employeur::withoutEvents(fn () => Employeur::create([
            'entreprise_id' => $this->entreprise->id,
            'departement_id' => $departement->id,
            'matricule' => $code,
            'code_employe' => $code,
            'qr_code_secret' => 'secret-'.$code,
            'date_embauche' => '2024-01-01',
            'type_contrat' => 'CDI',
            'salaire_base' => 100000,
            'poste' => 'Agent',
            'nom' => 'Nom'.$code,
            'prenom' => 'Prenom'.$code,
            'email' => strtolower($code).'@test.local',
            'statut' => 'actif',
        ]));
    }

    /**
     * Département actif avec deux membres.
     *
     * @return array{0: Departement, 1: array<int, Employeur>}
     */
    private function departementAvecMembres(): array
    {
        $departement = $this->creerDepartement('Logistique', 'LOG');

        return [$departement, [$this->creerEmploye($departement, 'M1'), $this->creerEmploye($departement, 'M2')]];
    }
}
