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
        Schema::table('jour_travails', function (Blueprint $table) {
            // Vérifier si les colonnes existent déjà
            if (!Schema::hasColumn('jour_travails', 'heure_debut_standard')) {
                $table->time('heure_debut_standard')->nullable();
            }
            
            if (!Schema::hasColumn('jour_travails', 'heure_fin_standard')) {
                $table->time('heure_fin_standard')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jour_travails', function (Blueprint $table) {
            $table->dropColumn(['heure_debut_standard', 'heure_fin_standard']);
        });
    }
};
