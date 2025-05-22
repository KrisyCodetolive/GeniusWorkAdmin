<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plan_abonnements', function (Blueprint $table) {
            // Ajout des prix pour les périodes manquantes
            if (!Schema::hasColumn('plan_abonnements', 'prix_trimestriel')) {
                $table->decimal('prix_trimestriel', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('plan_abonnements', 'prix_semestriel')) {
                $table->decimal('prix_semestriel', 10, 2)->nullable();
            }
            
            // Ajout des champs pour la gestion des utilisateurs
            if (!Schema::hasColumn('plan_abonnements', 'nombre_utilisateurs_min')) {
                $table->integer('nombre_utilisateurs_min')->default(1);
            }
            if (!Schema::hasColumn('plan_abonnements', 'nombre_utilisateurs_max')) {
                $table->integer('nombre_utilisateurs_max')->nullable();
            }
            
            // Ajout des champs pour les fonctionnalités et la configuration
            if (!Schema::hasColumn('plan_abonnements', 'fonctionnalites')) {
                $table->json('fonctionnalites')->nullable();
            }
            if (!Schema::hasColumn('plan_abonnements', 'est_actif')) {
                $table->boolean('est_actif')->default(true);
            }
            if (!Schema::hasColumn('plan_abonnements', 'devise')) {
                $table->string('devise')->default('EUR');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_abonnements', function (Blueprint $table) {
            $columns = [
                'prix_trimestriel',
                'prix_semestriel',
                'nombre_utilisateurs_min',
                'nombre_utilisateurs_max',
                'fonctionnalites',
                'est_actif',
                'devise'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('plan_abonnements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
