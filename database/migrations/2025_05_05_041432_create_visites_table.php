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
        Schema::create('visites', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('visiteur_id')->index();
            $table->uuid('entreprise_id')->index();
            $table->uuid('site_id')->index();
            $table->dateTime('date_arrivee');
            $table->dateTime('date_depart')->nullable();
            $table->string('motif_visite');
            $table->string('personne_a_rencontrer')->nullable();
            $table->string('departement_a_visiter')->nullable();
            $table->text('commentaires')->nullable();
            $table->string('statut')->default('en_cours'); // en_cours, terminee, annulee
            $table->string('badge_visiteur')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visites');
    }
};
