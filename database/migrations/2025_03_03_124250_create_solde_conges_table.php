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
        if (!Schema::hasTable('solde_conges')) {
            Schema::create('solde_conges', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignUuid('type_conge_id')->constrained('type_conges')->onDelete('cascade');
                $table->integer('annee');
                $table->decimal('solde_initial', 8, 2)->default(0);
                $table->decimal('solde_acquis', 8, 2)->default(0);
                $table->decimal('solde_pris', 8, 2)->default(0);
                $table->decimal('solde_restant', 8, 2)->default(0);
                $table->datetime('date_derniere_maj')->nullable();
                $table->text('commentaire')->nullable();
                $table->json('meta_donnees')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Index pour améliorer les performances des requêtes courantes
                $table->index(['user_id', 'type_conge_id', 'annee']);
                $table->index('annee');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solde_conges');
    }
};
