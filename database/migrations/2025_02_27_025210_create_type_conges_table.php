<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('type_conges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->integer('duree_max_annuelle')->nullable();
            $table->boolean('necessite_justificatif')->default(false);
            $table->boolean('est_paye')->default(true);
            $table->boolean('deductible_solde')->default(true);
            $table->integer('delai_demande_prealable')->default(0); // en jours
            $table->json('conditions_eligibilite')->nullable();
            $table->json('configuration')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('type_conges');
    }
};
