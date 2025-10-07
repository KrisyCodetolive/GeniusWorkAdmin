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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->string('titre');
            $table->string('active');
            $table->text('description')->nullable();
            $table->dateTime('date_debut')->nullable();
            $table->dateTime('date_fin')->nullable();
            $table->string('timing')->nullable();
            $table->text('livrable')->nullable();
            $table->string('statut')->default('en_attente');
            $table->string('type')->default('standard');
            $table->string('priorite')->default('moyenne');
            $table->string('etiquette')->nullable();
            $table->boolean('est_routine')->default(false);
            $table->string('frequence_routine')->nullable();
            $table->json('jour_routine')->nullable();
            $table->uuid('createur_id')->nullable();
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entreprise_id')
                ->references('id')
                ->on('entreprises')
                ->onDelete('cascade');

            $table->foreign('createur_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
