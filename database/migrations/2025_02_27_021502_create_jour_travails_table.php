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
        Schema::create('jour_travails', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->string('jour_semaine');
            $table->boolean('est_travaille')->default(true);
            $table->boolean('est_ferie')->default(false);
            $table->time('heure_debut_standard')->nullable();
            $table->time('heure_fin_standard')->nullable();
            $table->integer('duree_pause_standard')->nullable(); // en minutes
            $table->json('plages_horaires')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entreprise_id', 'jour_semaine']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jour_travails');
    }
};
