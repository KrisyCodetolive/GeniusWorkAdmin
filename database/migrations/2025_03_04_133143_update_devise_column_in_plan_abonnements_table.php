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
        Schema::table('plan_abonnements', function (Blueprint $table) {
            // Modifier la colonne devise pour augmenter sa taille
            $table->string('devise', 10)->default('XOF')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_abonnements', function (Blueprint $table) {
            // Remettre la colonne devise à sa taille d'origine
            $table->string('devise')->default('XOF')->change();
        });
    }
};
