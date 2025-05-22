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
        Schema::table('entreprises', function (Blueprint $table) {
            $table->index(['statut', 'deleted_at']);
            $table->index('email');
            $table->index('telephone');
        });

        Schema::table('abonnements', function (Blueprint $table) {
            $table->index(['entreprise_id', 'statut']);
            $table->index(['date_debut', 'date_fin']);
        });

        Schema::table('plan_abonnements', function (Blueprint $table) {
            $table->index(['nom', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropIndex(['statut', 'deleted_at']);
            $table->dropIndex(['email']);
            $table->dropIndex(['telephone']);
        });

        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropIndex(['entreprise_id', 'statut']);
            $table->dropIndex(['date_debut', 'date_fin']);
        });

        Schema::table('plan_abonnements', function (Blueprint $table) {
            $table->dropIndex(['nom', 'statut']);
        });
    }
};
