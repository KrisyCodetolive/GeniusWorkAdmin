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
        // Vérifier si la contrainte existe avant de la supprimer
        $indexExists = DB::select("SHOW INDEXES FROM entreprises WHERE Key_name = 'entreprises_email_unique'");
        
        if (!empty($indexExists)) {
            // Supprimer la contrainte d'unicité sur l'email
            Schema::table('entreprises', function (Blueprint $table) {
                $table->dropUnique('entreprises_email_unique');
            });
        }
        
        // Ajouter une nouvelle contrainte d'unicité qui ignore les enregistrements supprimés (soft delete)
        // Utiliser une requête SQL directe pour éviter les problèmes avec les colonnes NULL
        DB::statement('CREATE UNIQUE INDEX entreprises_email_deleted_at_unique ON entreprises (email, deleted_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la contrainte d'unicité complexe
        DB::statement('DROP INDEX IF EXISTS entreprises_email_deleted_at_unique ON entreprises');
        
        // Rétablir la contrainte d'unicité simple
        Schema::table('entreprises', function (Blueprint $table) {
            $table->unique(['email'], 'entreprises_email_unique');
        });
    }
};
