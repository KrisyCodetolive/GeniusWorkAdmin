<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('politiques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->boolean('annuler_horaires_si_sortie_manquee')->default(false);
            $table->integer('nombre_pointages_par_jour')->default(2);
            $table->boolean('activer_pauses')->default(true);
            $table->boolean('activer_heures_supplementaires')->default(true);
            $table->boolean('notifier_utilisateurs')->default(true);
            $table->integer('tolerance_retard')->default(15); // en minutes
            $table->integer('tolerance_depart_anticipe')->default(0); // en minutes
            $table->boolean('autoriser_permutations')->default(true);
            $table->boolean('autoriser_recuperations')->default(true);
            $table->boolean('autoriser_travail_weekend')->default(false);
            $table->json('regles_presence')->nullable();
            $table->json('regles_conges')->nullable();
            $table->json('regles_supplementaires')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('entreprise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('politiques');
    }
};
