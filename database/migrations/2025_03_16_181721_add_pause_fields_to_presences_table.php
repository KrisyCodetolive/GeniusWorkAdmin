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
            if (!Schema::hasColumn('presences', 'date_heure_pause_debut')) {
                $table->timestamp('date_heure_pause_debut')->nullable()->after('date_heure_sortie');
            }
            
            if (!Schema::hasColumn('presences', 'date_heure_pause_fin')) {
                $table->timestamp('date_heure_pause_fin')->nullable()->after('date_heure_pause_debut');
            }
            
            if (!Schema::hasColumn('presences', 'minutes_pause')) {
                $table->integer('minutes_pause')->nullable()->after('minutes_supplementaires');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'date_heure_pause_debut')) {
                $table->dropColumn('date_heure_pause_debut');
            }
            
            if (Schema::hasColumn('presences', 'date_heure_pause_fin')) {
                $table->dropColumn('date_heure_pause_fin');
            }
            
            if (Schema::hasColumn('presences', 'minutes_pause')) {
                $table->dropColumn('minutes_pause');
            }
        });
    }
};
