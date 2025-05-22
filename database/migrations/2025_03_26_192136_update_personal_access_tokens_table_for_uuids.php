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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Supprimer les anciennes colonnes
            $table->dropColumn('tokenable_id');
            $table->dropColumn('tokenable_type');
            
            // Ajouter les nouvelles colonnes avec le support UUID
            $table->uuidMorphs('tokenable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Supprimer les colonnes UUID
            $table->dropColumn('tokenable_id');
            $table->dropColumn('tokenable_type');
            
            // Restaurer les colonnes originales
            $table->morphs('tokenable');
        });
    }
};
