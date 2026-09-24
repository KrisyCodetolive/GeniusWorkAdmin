<?php

namespace Tests\Feature\Depenses;

use App\Models\DemandeDepense;
use App\Models\ValidationDepense;
use App\Notifications\DepenseNotification;
use App\Services\DepenseWorkflowService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class DepenseWorkflowTest extends TestCase
{
    use DepenseTestHelpers, RefreshDatabase;

    private DepenseWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->preparerCircuit();
        $this->service = app(DepenseWorkflowService::class);
    }

    public function test_circuit_complet_sous_le_seuil_sans_ceo(): void
    {
        $demande = $this->nouvelleDemande(100000);
        $this->assertSame(DemandeDepense::STATUT_BROUILLON, $demande->statut);

        $demande = $this->service->soumettre($demande, $this->demandeur);
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->statut);
        Notification::assertSentTo($this->comptable, DepenseNotification::class);

        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature, 'Conforme');
        $this->assertSame(DemandeDepense::STATUT_APPROUVEE, $demande->statut);

        $demande = $this->service->decaisser($demande, $this->comptable, [
            'mode_paiement' => 'virement',
            'reference_paiement' => 'VIR-001',
        ]);

        $this->assertSame(DemandeDepense::STATUT_PAYEE, $demande->statut);
        $this->assertSame($this->comptable->id, $demande->payee_par_user_id);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($demande->pdf_bon_sortie));
        $this->assertSame(
            [ValidationDepense::ETAPE_SOUMISSION, ValidationDepense::ETAPE_COMPTABILITE, ValidationDepense::ETAPE_DECAISSEMENT],
            $demande->validations()->pluck('etape')->all()
        );
    }

    public function test_circuit_au_dessus_du_seuil_passe_par_le_ceo(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(800000), $this->demandeur);

        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature);
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_CEO, $demande->statut);
        Notification::assertSentTo($this->ceo, DepenseNotification::class);

        $demande = $this->service->validerCeo($demande, $this->ceo, $this->signature);
        $this->assertSame(DemandeDepense::STATUT_APPROUVEE, $demande->statut);

        foreach ([ValidationDepense::ETAPE_COMPTABILITE, ValidationDepense::ETAPE_CEO] as $etape) {
            $validation = $demande->approbation($etape);
            Storage::disk('local')->assertExists($validation->signature_path);
            $this->assertTrue($validation->hashValide());
        }
    }

    public function test_montant_egal_au_seuil_ne_passe_pas_par_le_ceo(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(500000), $this->demandeur);

        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature);

        $this->assertSame(DemandeDepense::STATUT_APPROUVEE, $demande->statut);
    }

    public function test_le_ceo_par_defaut_est_l_administrateur_de_l_entreprise(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(800000), $this->demandeur);
        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature);
        $demande->parametres()->update(['ceo_user_id' => null]);

        $demande = $this->service->validerCeo($demande, $this->ceo, $this->signature);

        $this->assertSame(DemandeDepense::STATUT_APPROUVEE, $demande->statut);
    }

    public function test_personne_ne_valide_sa_propre_demande(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000, $this->comptable), $this->comptable);

        $this->expectException(AuthorizationException::class);
        $this->service->validerComptabilite($demande, $this->comptable, $this->signature);
    }

    public function test_le_ceo_ne_peut_pas_sauter_l_etape_comptable(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(800000), $this->demandeur);

        $this->expectException(AuthorizationException::class);
        $this->service->validerCeo($demande, $this->ceo, $this->signature);
    }

    public function test_la_meme_personne_ne_peut_pas_signer_comptable_et_ceo(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(800000), $this->demandeur);
        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature);
        $demande->parametres()->update(['ceo_user_id' => $this->comptable->id]);

        $this->expectException(AuthorizationException::class);
        $this->service->validerCeo($demande, $this->comptable, $this->signature);
    }

    public function test_seul_le_comptable_enregistre_le_paiement(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);
        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature);

        $this->expectException(AuthorizationException::class);
        $this->service->decaisser($demande, $this->demandeur, ['mode_paiement' => 'especes']);
    }

    public function test_une_demande_soumise_n_est_plus_modifiable(): void
    {
        $demande = $this->nouvelleDemande(100000);
        $this->assertTrue($this->demandeur->can('update', $demande));

        $demande = $this->service->soumettre($demande, $this->demandeur);

        $this->assertFalse($this->demandeur->can('update', $demande));
    }

    public function test_une_demande_renvoyee_redevient_modifiable_puis_peut_etre_resoumise(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);

        $demande = $this->service->renvoyer($demande, $this->comptable, 'Joindre la facture');

        $this->assertSame(DemandeDepense::STATUT_BROUILLON, $demande->statut);
        $this->assertTrue($this->demandeur->can('update', $demande));
        Notification::assertSentTo($demande->creePar, DepenseNotification::class);

        $demande = $this->service->soumettre($demande, $this->demandeur);
        $this->assertSame(DemandeDepense::STATUT_EN_ATTENTE_COMPTABLE, $demande->statut);
    }

    public function test_rejet_avec_motif_obligatoire(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);

        try {
            $this->service->rejeter($demande, $this->comptable, '  ');
            $this->fail('Un rejet sans motif doit être refusé.');
        } catch (InvalidArgumentException) {
        }

        $demande = $this->service->rejeter($demande, $this->comptable, 'Hors budget');

        $this->assertSame(DemandeDepense::STATUT_REJETEE, $demande->statut);
        $this->assertSame('Hors budget', $demande->validations()->where('decision', ValidationDepense::DECISION_REJETE)->first()->commentaire);
    }

    public function test_la_signature_est_obligatoire(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);

        $this->expectException(InvalidArgumentException::class);
        $this->service->validerComptabilite($demande, $this->comptable, 'pas une image');
    }

    public function test_le_demandeur_peut_annuler_tant_que_la_demande_n_est_pas_approuvee(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);

        $demande = $this->service->annuler($demande, $this->demandeur);

        $this->assertSame(DemandeDepense::STATUT_ANNULEE, $demande->statut);
    }

    public function test_un_comptable_d_une_autre_entreprise_ne_peut_rien_faire(): void
    {
        $autreComptable = $this->creerUtilisateur('comptable', $this->creerEntreprise('Autre Entreprise'));
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);

        $this->assertFalse($autreComptable->can('view', $demande));

        $this->expectException(AuthorizationException::class);
        $this->service->validerComptabilite($demande, $autreComptable, $this->signature);
    }

    public function test_les_references_sont_sequentielles_par_entreprise(): void
    {
        $annee = now()->year;

        $this->assertSame("DEP-{$annee}-0001", $this->nouvelleDemande(1000)->reference);
        $this->assertSame("DEP-{$annee}-0002", $this->nouvelleDemande(1000)->reference);

        $autre = $this->creerEntreprise('Autre Entreprise');
        $demandeAutre = DemandeDepense::create([
            'entreprise_id' => $autre->id,
            'cree_par_user_id' => $this->demandeur->id,
            'objet' => 'Test',
            'montant' => 1000,
            'beneficiaire' => 'X',
        ]);
        $this->assertSame("DEP-{$annee}-0001", $demandeAutre->reference);
    }

    public function test_l_empreinte_detecte_une_modification_apres_signature(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);
        $demande = $this->service->validerComptabilite($demande, $this->comptable, $this->signature);
        $this->assertTrue($demande->approbation(ValidationDepense::ETAPE_COMPTABILITE)->hashValide());

        DB::table('demandes_depense')->where('id', $demande->id)->update(['montant' => 999999]);

        $this->assertFalse($demande->fresh()->approbation(ValidationDepense::ETAPE_COMPTABILITE)->hashValide());
    }

    public function test_le_journal_des_validations_ne_peut_etre_ni_modifie_ni_supprime(): void
    {
        $demande = $this->service->soumettre($this->nouvelleDemande(100000), $this->demandeur);
        $validation = $demande->validations()->first();

        try {
            $validation->update(['commentaire' => 'falsifié']);
            $this->fail('La modification du journal doit être refusée.');
        } catch (LogicException) {
        }

        $this->expectException(LogicException::class);
        $validation->delete();
    }

    public function test_montant_en_lettres(): void
    {
        $demande = new DemandeDepense(['montant' => 1250500, 'devise' => 'FCFA']);

        $this->assertSame('Un million deux cent cinquante mille cinq cents FCFA', $demande->montantEnLettres());
    }
}
