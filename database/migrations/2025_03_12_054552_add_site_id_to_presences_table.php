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
        Schema::table('presences', function (Blueprint $table) {
            // Ajouter user_id s'il n'existe pas
            if (!Schema::hasColumn('presences', 'employeur_id')) {
                $table->uuid('user_id')->after('employeur_id');
                $table->foreign('employeur_id')->references('id')->on('employeurs');
            }
            
            // Ajouter site_id
            if (!Schema::hasColumn('presences', 'site_id')) {
                $table->uuid('site_id')->nullable()->after('employeur_id');
                $table->foreign('site_id')->references('id')->on('sites');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            // Supprimer site_id si elle existe
            if (Schema::hasColumn('presences', 'site_id')) {
                $table->dropForeign(['site_id']);
                $table->dropColumn('site_id');
            }
            
            // Supprimer user_id si elle existe
            if (Schema::hasColumn('presences', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};
