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
        if (!Schema::hasTable('conge_validations')) {
            Schema::create('conge_validations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('conge_id')->constrained('conges')->onDelete('cascade');
                $table->foreignUuid('validateur_id')->constrained('users')->onDelete('cascade');
                $table->integer('niveau')->default(1);
                $table->enum('decision', ['en_attente', 'approuve', 'refuse', 'delegue'])->default('en_attente');
                $table->text('commentaire')->nullable();
                $table->datetime('date_decision')->nullable();
                $table->foreignUuid('delegue_a')->nullable()->constrained('users')->onDelete('set null');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Index pour améliorer les performances des requêtes courantes
                $table->index(['conge_id', 'niveau']);
                $table->index(['validateur_id', 'decision']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conge_validations');
    }
};
