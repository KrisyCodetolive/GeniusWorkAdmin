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
        Schema::create('visiteurs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id')->index();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone');
            $table->string('email')->nullable();
            $table->string('code_visiteur')->unique();
            $table->string('organisation')->nullable();
            $table->string('fonction')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->string('piece_identite')->nullable();
            $table->string('statut')->default('actif'); // actif, inactif
            $table->timestamps();
            $table->softDeletes();
            
            // Contrainte d'unicité composée pour le téléphone par entreprise
            $table->unique(['telephone', 'entreprise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visiteurs');
    }
};
