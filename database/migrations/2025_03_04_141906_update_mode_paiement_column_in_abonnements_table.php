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
        // Vérifier si la table existe
        if (Schema::hasTable('abonnements')) {
            // Vérifier si la colonne mode_paiement existe
            if (Schema::hasColumn('abonnements', 'mode_paiement')) {
                // Modifier la colonne mode_paiement pour accepter 'non_specifie'
                DB::statement("ALTER TABLE `abonnements` MODIFY `mode_paiement` ENUM('carte', 'virement', 'especes', 'non_specifie') DEFAULT 'carte'");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vérifier si la table existe
        if (Schema::hasTable('abonnements')) {
            // Vérifier si la colonne mode_paiement existe
            if (Schema::hasColumn('abonnements', 'mode_paiement')) {
                // Remettre la colonne mode_paiement à son état d'origine
                DB::statement("ALTER TABLE `abonnements` MODIFY `mode_paiement` ENUM('carte', 'virement', 'especes') DEFAULT 'carte'");
            }
        }
    }
};
