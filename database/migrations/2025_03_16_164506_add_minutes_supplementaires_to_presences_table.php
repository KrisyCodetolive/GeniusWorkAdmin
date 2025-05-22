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
            if (!Schema::hasColumn('presences', 'minutes_travaillees')) {
                $table->integer('minutes_travaillees')->nullable()->after('statut');
            }
            
            if (!Schema::hasColumn('presences', 'minutes_retard')) {
                $table->integer('minutes_retard')->nullable()->after('minutes_travaillees');
            }
            
            if (!Schema::hasColumn('presences', 'minutes_supplementaires')) {
                $table->integer('minutes_supplementaires')->nullable()->after('minutes_retard');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'minutes_travaillees')) {
                $table->dropColumn('minutes_travaillees');
            }
            
            if (Schema::hasColumn('presences', 'minutes_retard')) {
                $table->dropColumn('minutes_retard');
            }
            
            if (Schema::hasColumn('presences', 'minutes_supplementaires')) {
                $table->dropColumn('minutes_supplementaires');
            }
        });
    }
};
