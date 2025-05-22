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
        Schema::table('rapports', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà avant de les ajouter
            if (!Schema::hasColumn('rapports', 'date_generation')) {
                $table->date('date_generation')->after('date_fin')->nullable();
            }
            
            if (!Schema::hasColumn('rapports', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('departement_id')->nullable();
            }
            
            if (!Schema::hasColumn('rapports', 'generateur_id')) {
                $table->unsignedBigInteger('generateur_id')->after('user_id')->nullable();
            }
            
            if (!Schema::hasColumn('rapports', 'donnees')) {
                $table->json('donnees')->after('parametres')->nullable();
            }
            
            if (!Schema::hasColumn('rapports', 'fichier_path')) {
                $table->string('fichier_path')->after('donnees')->nullable();
            }
            
            if (!Schema::hasColumn('rapports', 'statut')) {
                $table->string('statut')->after('fichier_path')->default('généré');
            }
            
            if (!Schema::hasColumn('rapports', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            // Supprimer les colonnes
            $columns = ['date_generation', 'user_id', 'generateur_id', 'donnees', 'fichier_path', 'statut', 'deleted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('rapports', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
