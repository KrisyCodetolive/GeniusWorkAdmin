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
            if (!Schema::hasColumn('presences', 'date_heure')) {
                $table->dateTime('date_heure')->nullable()->after('date_heure_entree');
            }
            
            if (!Schema::hasColumn('presences', 'date_heure_sortie')) {
                $table->dateTime('date_heure_sortie')->nullable()->after('date_heure_entree');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'date_heure')) {
                $table->dropColumn('date_heure');
            }
            
            if (Schema::hasColumn('presences', 'date_heure_sortie')) {
                $table->dropColumn('date_heure_sortie');
            }
        });
    }
};
