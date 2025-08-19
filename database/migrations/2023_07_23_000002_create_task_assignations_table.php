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
        Schema::create('task_assignations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->uuid('task_id');
            $table->string('assignable_type')->nullable();
            $table->uuid('assignable_id')->nullable();
            $table->uuid('employeur_id');
            $table->string('statut')->default('en_attente');
            $table->integer('progression')->default(0);
            $table->dateTime('date_debut_reelle')->nullable();
            $table->dateTime('date_fin_reelle')->nullable();
            $table->text('commentaire')->nullable();
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entreprise_id')
                ->references('id')
                ->on('entreprises')
                ->onDelete('cascade');

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->foreign('employeur_id')
                ->references('id')
                ->on('employeurs')
                ->onDelete('cascade');
                
            // Index pour les recherches par type d'assignation
            $table->index(['assignable_type', 'assignable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignations');
    }
};
