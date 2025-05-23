<?php

namespace Tests\Feature;

use App\Models\Abonnement;
use App\Models\Entreprise;
use App\Models\Facturation;
use App\Models\PlanAbonnement;
use App\Models\User;
use App\Services\ChangeAbonnementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChangeAbonnementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $entreprise;
    protected $abonnement;
    protected $planAbonnement;
    protected $nouveauPlan;

    public function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur
        $this->user = User::factory()->create();

        // Créer une entreprise
        $this->entreprise = Entreprise::factory()->create([
            'admin_id' => $this->user->id
        ]);

        // Associer l'utilisateur à l'entreprise
        $this->user->update(['entreprise_id' => $this->entreprise->id]);

        // Créer des plans d'abonnement
        $this->planAbonnement = PlanAbonnement::factory()->create([
            'nom' => 'Starter',
            'prix_mensuel' => 10000,
            'prix_annuel' => 100000,
            'nombre_employes_max' => 50
        ]);

        $this->nouveauPlan = PlanAbonnement::factory()->create([
            'nom' => 'Side Business',
            'prix_mensuel' => 15000,
            'prix_annuel' => 150000,
            'nombre_employes_max' => 100
        ]);

        // Créer un abonnement
        $this->abonnement = Abonnement::factory()->create([
            'entreprise_id' => $this->entreprise->id,
            'plan_abonnement_id' => $this->planAbonnement->id,
            'type_periode' => 'mensuel',
            'montant' => 10000,
            'statut' => 'actif'
        ]);
    }

    /** @test */
    public function user_can_access_change_abonnement_form()
    {
        $this->actingAs($this->user)
            ->get(route('abonnements.change.form', ['abonnementId' => $this->abonnement->id]))
            ->assertStatus(200)
            ->assertViewIs('abonnements.change-form')
            ->assertSee('Changer d\'abonnement');
    }

    /** @test */
    public function user_can_submit_change_abonnement_form()
    {
        $this->actingAs($this->user)
            ->post(route('abonnements.change.process', ['abonnementId' => $this->abonnement->id]), [
                'nombre_employes' => 75,
                'type_periode' => 'mensuel'
            ])
            ->assertRedirect(route('abonnements.change.confirm'));

        // Vérifier que les données sont stockées en session
        $this->assertTrue(session()->has('changement_abonnement'));
        $this->assertEquals(75, session('changement_abonnement.nombre_employes'));
        $this->assertEquals('mensuel', session('changement_abonnement.type_periode'));
    }

    /** @test */
    public function user_can_view_confirmation_page()
    {
        // Simuler les données de session
        session(['changement_abonnement' => [
            'abonnement_id' => $this->abonnement->id,
            'plan_abonnement_id' => $this->nouveauPlan->id,
            'facturation_id' => Facturation::factory()->create([
                'entreprise_id' => $this->entreprise->id,
                'abonnement_id' => $this->abonnement->id,
                'montant_ttc' => 15000
            ])->id,
            'nombre_employes' => 75,
            'type_periode' => 'mensuel',
            'montant' => 15000
        ]]);

        $this->actingAs($this->user)
            ->get(route('abonnements.change.confirm'))
            ->assertStatus(200)
            ->assertViewIs('abonnements.change-confirm')
            ->assertSee('Confirmation du changement d\'abonnement');
    }

    /** @test */
    public function service_can_calculate_forfait_et_cout()
    {
        $service = app(ChangeAbonnementService::class);
        
        // Test pour le forfait Starter
        $result = $service->calculerForfaitEtCout(25);
        $this->assertEquals('Starter', $result['forfait']);
        $this->assertEquals(10000, $result['cout_fixe']);
        $this->assertEquals(2500, $result['cout_utilisateurs']);
        $this->assertEquals(12500, $result['cout_total']);
        
        // Test pour le forfait Side Business
        $result = $service->calculerForfaitEtCout(75);
        $this->assertEquals('Side Business', $result['forfait']);
        $this->assertEquals(15000, $result['cout_fixe']);
        $this->assertEquals(7500, $result['cout_utilisateurs']);
        $this->assertEquals(22500, $result['cout_total']);
        
        // Test pour le forfait Enterprise
        $result = $service->calculerForfaitEtCout(150);
        $this->assertEquals('Enterprise', $result['forfait']);
        $this->assertEquals(30000, $result['cout_fixe']);
        $this->assertEquals(15000, $result['cout_utilisateurs']);
        $this->assertEquals(45000, $result['cout_total']);
    }

    /** @test */
    public function service_can_prepare_changement_abonnement()
    {
        $service = app(ChangeAbonnementService::class);
        
        // Créer un plan Enterprise
        PlanAbonnement::factory()->create([
            'nom' => 'Enterprise',
            'prix_mensuel' => 30000,
            'prix_annuel' => 300000,
            'nombre_employes_max' => 500
        ]);
        
        $result = $service->preparerChangementAbonnement($this->abonnement, 150, 'mensuel');
        
        $this->assertEquals($this->abonnement->id, $result['abonnement']->id);
        $this->assertEquals('Enterprise', $result['plan_abonnement']->nom);
        $this->assertInstanceOf(Facturation::class, $result['facturation']);
        $this->assertEquals(30000, $result['montant']);
        $this->assertEquals('mensuel', $result['type_periode']);
        $this->assertEquals(150, $result['nombre_employes']);
        $this->assertEquals('Enterprise', $result['calcul_cout']['forfait']);
    }
}
