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
        Schema::table('jours', function (Blueprint $table) {
            $table->uuid('jour_travail_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dans le cas du rollback, nous laissons la colonne nullable
        // car il peut y avoir des données NULL dans la base de données
        // et nous ne voulons pas causer d'erreurs
        
        // Note: Normalement, nous devrions remettre la colonne comme non nullable,
        // mais cela causerait des erreurs avec les données existantes.
        // Pour une migration de production, il faudrait d'abord nettoyer les données.
    }
};
