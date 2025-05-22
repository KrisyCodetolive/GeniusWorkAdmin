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
        // Modifier la colonne mode_paiement pour ajouter 'non_specifie' comme valeur valide
        DB::statement("ALTER TABLE facturations MODIFY COLUMN mode_paiement ENUM('carte', 'virement', 'especes', 'non_specifie', 'wave', 'mobile_money') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'état précédent
        DB::statement("ALTER TABLE facturations MODIFY COLUMN mode_paiement ENUM('carte', 'virement', 'especes', 'non_specifie', 'wave', 'mobile_money') NULL");
    }
};
