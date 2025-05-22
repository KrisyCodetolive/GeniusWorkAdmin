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
        Schema::table('plan_abonnements', function (Blueprint $table) {
            $table->enum('periode_facturation', ['mensuel', 'trimestriel', 'semestriel', 'annuel'])
                ->default('mensuel')
                ->after('prix_annuel');
        });

        // Mettre à jour les données existantes
        DB::statement("UPDATE plan_abonnements SET periode_facturation = 'mensuel'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_abonnements', function (Blueprint $table) {
            $table->dropColumn('periode_facturation');
        });
    }
};
