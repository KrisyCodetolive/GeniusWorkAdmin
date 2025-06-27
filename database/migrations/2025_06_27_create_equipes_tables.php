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
        Schema::create('equipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->uuid('responsable_id')->nullable();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('statut')->default('actif');
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('entreprise_id')->references('id')->on('entreprises');
            $table->foreign('responsable_id')->references('id')->on('employeurs');
        });

        Schema::create('equipe_employeur', function (Blueprint $table) {
            $table->uuid('equipe_id');
            $table->uuid('employeur_id');
            $table->boolean('est_actif')->default(true);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();
            
            $table->primary(['equipe_id', 'employeur_id']);
            $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('cascade');
            $table->foreign('employeur_id')->references('id')->on('employeurs')->onDelete('cascade');
        });

        Schema::create('equipe_plage_horaire', function (Blueprint $table) {
            $table->uuid('equipe_id');
            $table->uuid('plage_horaire_id');
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
            
            $table->primary(['equipe_id', 'plage_horaire_id']);
            $table->foreign('equipe_id')->references('id')->on('equipes')->onDelete('cascade');
            $table->foreign('plage_horaire_id')->references('id')->on('plage_horaires')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipe_plage_horaire');
        Schema::dropIfExists('equipe_employeur');
        Schema::dropIfExists('equipes');
    }
};
