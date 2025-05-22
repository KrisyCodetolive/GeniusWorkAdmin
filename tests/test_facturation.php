<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use App\Models\Facturation;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Initialiser l'application Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Test de création de facturation...\n";

try {
    // Créer un utilisateur de test
    $user = User::firstOrCreate(
        ['email' => 'test@example.com'],
        [
            'name' => 'Utilisateur Test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );
    
    echo "Utilisateur créé ou trouvé: {$user->id}\n";
    
    // Créer une entreprise de test
    $entreprise = Entreprise::firstOrCreate(
        ['nom' => 'Entreprise Test'],
        [
            'code' => 'TST',
            'adresse' => '123 Rue de Test',
            'telephone' => '123456789',
            'email' => 'contact@test.com',
            'statut' => 'actif',
            'user_id' => $user->id,
        ]
    );
    
    echo "Entreprise créée ou trouvée: {$entreprise->id}\n";
    
    // Créer un plan d'abonnement de test
    $planAbonnement = PlanAbonnement::firstOrCreate(
        ['nom' => 'Plan Test'],
        [
            'description' => 'Plan de test',
            'prix_mensuel' => 10000,
            'prix_annuel' => 100000,
            'nombre_utilisateurs' => 10,
            'fonctionnalites' => json_encode(['test1', 'test2']),
        ]
    );
    
    echo "Plan d'abonnement créé ou trouvé: {$planAbonnement->id}\n";
    
    // Créer un abonnement de test
    $abonnement = Abonnement::firstOrCreate(
        [
            'entreprise_id' => $entreprise->id,
            'plan_abonnement_id' => $planAbonnement->id,
        ],
        [
            'date_debut' => Carbon::now(),
            'date_fin' => Carbon::now()->addYear(),
            'montant' => 10000,
            'frequence' => 'mensuel',
            'statut' => 'en_attente',
            'mode_paiement' => 'carte',
        ]
    );
    
    echo "Abonnement créé ou trouvé: {$abonnement->id}\n";
    
    // Créer une facturation de test
    $facturation = Facturation::create([
        'entreprise_id' => $entreprise->id,
        'abonnement_id' => $abonnement->id,
        'numero_facture' => 'TEST-' . now()->format('YmdHis'),
        'date_facturation' => Carbon::now(),
        'date_echeance' => Carbon::now()->addDays(7),
        'montant_ht' => 10000,
        'taux_tva' => 0,
        'montant_tva' => 0,
        'montant_ttc' => 10000,
        'statut_paiement' => 'en_attente',
        'mode_paiement' => 'carte',
        'reference_paiement' => 'TEST-REF-' . now()->format('YmdHis'),
        'devise' => 'FCFA',
    ]);
    
    echo "Facturation créée avec succès: {$facturation->id}\n";
    echo "Statut de paiement: {$facturation->statut_paiement}\n";
    
    echo "Test terminé avec succès!\n";
    
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
