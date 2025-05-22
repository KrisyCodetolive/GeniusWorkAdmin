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
        if (!Schema::hasTable('conges')) {
            Schema::create('conges', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employeur_id')->constrained('employeurs')->onDelete('cascade');
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignUuid('type_conge_id')->constrained('type_conges')->onDelete('cascade');
                $table->datetime('date_debut');
                $table->datetime('date_fin');
                $table->decimal('duree', 8, 2);
                $table->text('motif')->nullable();
                $table->text('commentaire')->nullable();
                $table->enum('statut', ['brouillon', 'en_attente', 'valide', 'refuse', 'annule'])->default('brouillon');
                $table->string('justificatif_url')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Index pour améliorer les performances des requêtes courantes
                $table->index(['user_id', 'statut']);
                $table->index(['date_debut', 'date_fin']);
                $table->index('type_conge_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
