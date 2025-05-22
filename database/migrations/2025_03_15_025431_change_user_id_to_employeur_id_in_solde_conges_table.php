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
        Schema::table('solde_conges', function (Blueprint $table) {
            // Supprimer la clé étrangère existante si elle existe
            if (Schema::hasColumn('solde_conges', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            // Ajouter la nouvelle colonne employeur_id
            $table->uuid('employeur_id')->after('id');
            
            // Ajouter la contrainte de clé étrangère
            $table->foreign('employeur_id')
                ->references('id')
                ->on('employeurs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solde_conges', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne employeur_id
            if (Schema::hasColumn('solde_conges', 'employeur_id')) {
                $table->dropForeign(['employeur_id']);
                $table->dropColumn('employeur_id');
            }
            
            // Vérifier si la colonne user_id existe déjà avant de l'ajouter
            if (!Schema::hasColumn('solde_conges', 'user_id')) {
                // Recréer la colonne user_id sans contrainte de clé étrangère
                // pour éviter les problèmes d'intégrité référentielle lors du rollback
                $table->uuid('user_id')->nullable()->after('id');
            }
            
            // Note: nous ne rétablissons pas la contrainte de clé étrangère
            // car cela pourrait causer des problèmes lors du rollback
            // Les données devront être rétablies manuellement si nécessaire
        });
    }
};
