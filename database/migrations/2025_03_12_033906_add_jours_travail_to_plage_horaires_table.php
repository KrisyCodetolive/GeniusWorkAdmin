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
        Schema::table('plage_horaires', function (Blueprint $table) {
            $table->json('jours_travail')->nullable()->after('configuration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plage_horaires', function (Blueprint $table) {
            $table->dropColumn('jours_travail');
        });
    }
};
