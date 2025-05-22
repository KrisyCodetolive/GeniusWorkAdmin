<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Employeur;
use App\Models\Entreprise;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class EmployeAuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test d'authentification avec un QR code valide
     *
     * @return void
     */
    public function test_login_with_valid_qr_code()
    {
        // Créer une entreprise
        $entreprise = Entreprise::factory()->create();

        // Créer un employé avec un QR code actif
        $employe = Employeur::factory()->create([
            'entreprise_id' => $entreprise->id,
            'qr_code_secret' => 'test_qr_code_123',
            'qr_code_active' => true,
            'qr_code_expires_at' => now()->addDays(30),
            'statut' => 'actif'
        ]);

        // Tester l'authentification
        $response = $this->postJson('/api/auth/login', [
            'qr_code' => 'test_qr_code_123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'token',
                     'employe' => [
                         'id',
                         'nom',
                         'prenom',
                         'email',
                         'code_employe',
                         'matricule',
                         'entreprise'
                     ]
                 ])
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Authentification réussie',
                     'employe' => [
                         'id' => $employe->id,
                         'nom' => $employe->nom,
                         'prenom' => $employe->prenom
                     ]
                 ]);

        // Vérifier que l'utilisateur a été créé
        $this->assertDatabaseHas('users', [
            'employeur_id' => $employe->id,
            'entreprise_id' => $entreprise->id,
            'role' => 'employeur'
        ]);
    }

    /**
     * Test d'authentification avec un QR code invalide
     *
     * @return void
     */
    public function test_login_with_invalid_qr_code()
    {
        $response = $this->postJson('/api/auth/login', [
            'qr_code' => 'invalid_qr_code'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'QR code invalide ou expiré'
                 ]);
    }

    /**
     * Test d'authentification avec un QR code expiré
     *
     * @return void
     */
    public function test_login_with_expired_qr_code()
    {
        // Créer une entreprise
        $entreprise = Entreprise::factory()->create();

        // Créer un employé avec un QR code expiré
        $employe = Employeur::factory()->create([
            'entreprise_id' => $entreprise->id,
            'qr_code_secret' => 'expired_qr_code',
            'qr_code_active' => true,
            'qr_code_expires_at' => now()->subDays(1),
            'statut' => 'actif'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'qr_code' => 'expired_qr_code'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'QR code invalide ou expiré'
                 ]);
    }

    /**
     * Test d'authentification avec un QR code inactif
     *
     * @return void
     */
    public function test_login_with_inactive_qr_code()
    {
        // Créer une entreprise
        $entreprise = Entreprise::factory()->create();

        // Créer un employé avec un QR code inactif
        $employe = Employeur::factory()->create([
            'entreprise_id' => $entreprise->id,
            'qr_code_secret' => 'inactive_qr_code',
            'qr_code_active' => false,
            'qr_code_expires_at' => now()->addDays(30),
            'statut' => 'actif'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'qr_code' => 'inactive_qr_code'
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'QR code invalide ou expiré'
                 ]);
    }

    /**
     * Test d'authentification avec un employé inactif
     *
     * @return void
     */
    public function test_login_with_inactive_employe()
    {
        // Créer une entreprise
        $entreprise = Entreprise::factory()->create();

        // Créer un employé inactif
        $employe = Employeur::factory()->create([
            'entreprise_id' => $entreprise->id,
            'qr_code_secret' => 'inactive_employe_qr',
            'qr_code_active' => true,
            'qr_code_expires_at' => now()->addDays(30),
            'statut' => 'inactif'
        ]);

        $response = $this->postJson('/api/auth/login', [
            'qr_code' => 'inactive_employe_qr'
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'status' => 'error',
                     'message' => 'Compte employé inactif'
                 ]);
    }

    /**
     * Test de vérification d'un token valide
     *
     * @return void
     */
    public function test_verify_valid_token()
    {
        // Créer une entreprise
        $entreprise = Entreprise::factory()->create();

        // Créer un employé
        $employe = Employeur::factory()->create([
            'entreprise_id' => $entreprise->id,
            'statut' => 'actif'
        ]);

        // Créer un utilisateur associé à l'employé
        $user = User::factory()->create([
            'employeur_id' => $employe->id,
            'entreprise_id' => $entreprise->id,
            'role' => 'employeur'
        ]);

        // Authentifier l'utilisateur
        Sanctum::actingAs($user);

        // Tester la vérification du token
        $response = $this->getJson('/api/auth/verify');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Token valide',
                     'employe' => [
                         'id' => $employe->id,
                         'nom' => $employe->nom,
                         'prenom' => $employe->prenom
                     ]
                 ]);
    }

    /**
     * Test de déconnexion
     *
     * @return void
     */
    public function test_logout()
    {
        // Créer une entreprise
        $entreprise = Entreprise::factory()->create();

        // Créer un employé
        $employe = Employeur::factory()->create([
            'entreprise_id' => $entreprise->id,
            'statut' => 'actif'
        ]);

        // Créer un utilisateur associé à l'employé
        $user = User::factory()->create([
            'employeur_id' => $employe->id,
            'entreprise_id' => $entreprise->id,
            'role' => 'employeur'
        ]);

        // Créer un token pour l'utilisateur
        $token = $user->createToken('test-token')->plainTextToken;

        // Tester la déconnexion
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Déconnexion réussie'
                 ]);

        // Vérifier que le token a été supprimé
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test-token'
        ]);
    }
}
