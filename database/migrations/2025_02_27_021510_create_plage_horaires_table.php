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
        Schema::create('plage_horaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->integer('duree_pause')->nullable(); // en minutes
            $table->boolean('est_standard')->default(false);
            $table->boolean('est_flexible')->default(false);
            $table->integer('marge_retard')->default(0); // en minutes
            $table->integer('marge_depart')->default(0); // en minutes
            $table->json('pauses')->nullable();
            $table->json('configuration')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plage_horaires');
    }
};
