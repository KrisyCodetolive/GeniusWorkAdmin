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
        // Approche alternative pour modifier l'enum sans utiliser doctrine/dbal
        DB::statement("ALTER TABLE abonnements MODIFY COLUMN statut ENUM('actif', 'inactif', 'essai', 'expire', 'resilie', 'en_attente') DEFAULT 'en_attente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancienne définition
        DB::statement("ALTER TABLE abonnements MODIFY COLUMN statut ENUM('actif', 'inactif', 'essai', 'expire', 'resilie') DEFAULT 'actif'");
    }
};
