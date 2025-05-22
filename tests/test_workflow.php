<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Services\WorkflowService;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Abonnement;
use App\Models\PlanAbonnement;
use App\Models\Facturation;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Initialiser l'application Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Test du workflow complet...\n";

try {
    // Récupérer le service de workflow
    $workflowService = app(WorkflowService::class);
    
    // Données de test
    $userData = [
        'name' => 'Utilisateur Test Workflow',
        'email' => 'workflow@example.com',
        'password' => bcrypt('password'),
    ];
    
    $companyData = [
        'company_name' => 'Entreprise Test Workflow',
        'industry' => 'Technologie',
        'contact_email' => 'contact@workflow.com',
        'contact_phone' => '987654321',
        'address' => '123 Rue du Workflow',
        'company_size' => 10,
    ];
    
    $subscriptionData = [
        'subscription_plan' => 'Starter',
        'nombre_utilisateurs' => 10,
        'frequence' => 'mensuel',
        'total_cost' => 11000,
    ];
    
    $paymentData = [
        'payment_method' => 'carte',
        'payment_date' => now(),
        'invoice_number' => 'TEST-WF-' . now()->format('YmdHis'),
    ];
    
    echo "Exécution du workflow complet...\n";
    
    // Exécuter le workflow complet
    $result = $workflowService->executeCompleteWorkflow(
        $userData,
        $companyData,
        $subscriptionData,
        $paymentData
    );
    
    echo "Workflow exécuté avec succès!\n";
    echo "Utilisateur créé: " . $result['user']->id . "\n";
    echo "Entreprise créée: " . $result['entreprise']->id . "\n";
    echo "Abonnement créé: " . $result['abonnement']->id . "\n";
    echo "Facturation créée: " . $result['facturation']->id . "\n";
    echo "Statut de paiement: " . $result['facturation']->statut_paiement . "\n";
    
    echo "Test terminé avec succès!\n";
    
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
