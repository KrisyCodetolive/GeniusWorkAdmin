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
        Schema::create('configurations_paie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('est_defaut')->default(false);
            $table->decimal('smig', 15, 2)->default(75000); // SMIG 2023 en FCFA
            $table->decimal('plafond_cnps', 15, 2)->default(225000); // Plafond mensuel CNPS
            $table->decimal('taux_cnps_employe', 8, 4)->default(6.3); // 6.3%
            $table->decimal('taux_cnps_employeur', 8, 4)->default(7.7); // 7.7%
            $table->decimal('taux_prestations_familiales', 8, 4)->default(5.75); // 5.75%
            $table->decimal('taux_accident_travail', 8, 4)->default(2.0); // 2.0% (peut varier selon le secteur)
            $table->decimal('taux_assurance_maladie', 8, 4)->default(0.75); // 0.75%
            $table->decimal('abattement_igr', 8, 4)->default(20.0); // 20% d'abattement forfaitaire
            $table->json('baremes_igr')->nullable();
            $table->json('parametres_indemnites')->nullable();
            $table->json('parametres_primes')->nullable();
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('entreprise_id')->references('id')->on('entreprises');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations_paie');
    }
};
