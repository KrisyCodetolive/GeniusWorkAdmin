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
        Schema::create('bulletins_paie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference')->unique();
            $table->uuid('employeur_id');
            $table->uuid('entreprise_id');
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->date('date_paiement')->nullable();
            $table->decimal('salaire_base', 15, 2);
            $table->decimal('total_indemnites', 15, 2)->default(0);
            $table->decimal('total_primes', 15, 2)->default(0);
            $table->decimal('salaire_brut', 15, 2);
            $table->decimal('cnps_employe', 15, 2)->default(0);
            $table->decimal('igr', 15, 2)->default(0);
            $table->decimal('total_retenues', 15, 2)->default(0);
            $table->decimal('salaire_net', 15, 2);
            $table->decimal('cnps_employeur', 15, 2)->default(0);
            $table->decimal('charges_patronales', 15, 2)->default(0);
            $table->string('statut')->default('brouillon'); // brouillon, validé, annulé
            $table->text('commentaire')->nullable();
            $table->uuid('genere_par')->nullable();
            $table->uuid('valide_par')->nullable();
            $table->timestamp('date_validation')->nullable();
            $table->string('fichier_pdf')->nullable();
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('employeur_id')->references('id')->on('employeurs');
            $table->foreign('entreprise_id')->references('id')->on('entreprises');
            $table->foreign('genere_par')->references('id')->on('users');
            $table->foreign('valide_par')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins_paie');
    }
};
