<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si les colonnes existent déjà
        $hasAbonnementId = DB::getSchemaBuilder()->hasColumn('paiements', 'abonnement_id');
        $hasEntrepriseId = DB::getSchemaBuilder()->hasColumn('paiements', 'entreprise_id');
        
        Schema::table('paiements', function (Blueprint $table) use ($hasAbonnementId, $hasEntrepriseId) {
            if (!$hasAbonnementId) {
                $table->uuid('abonnement_id')->nullable()->after('facturation_id');
                
                // Vérifier si la clé étrangère existe déjà
                if (!$this->hasForeignKey('paiements', 'paiements_abonnement_id_foreign')) {
                    $table->foreign('abonnement_id')
                        ->references('id')
                        ->on('abonnements')
                        ->onDelete('set null');
                }
            }
            
            if (!$hasEntrepriseId) {
                $table->uuid('entreprise_id')->nullable()->after('abonnement_id');
                
                // Vérifier si la clé étrangère existe déjà
                if (!$this->hasForeignKey('paiements', 'paiements_entreprise_id_foreign')) {
                    $table->foreign('entreprise_id')
                        ->references('id')
                        ->on('entreprises')
                        ->onDelete('set null');
                }
            }
        });
    }

    /**
     * Vérifie si une clé étrangère existe
     */
    private function hasForeignKey(string $table, string $key): bool
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        
        $foreignKeys = array_map(
            fn($key) => $key->getName(),
            $dbSchemaManager->listTableForeignKeys($table)
        );
        
        return in_array($key, $foreignKeys);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne rien faire car les colonnes sont déjà définies dans la migration de création de la table
    }
};
