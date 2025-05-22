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
        Schema::create('frais_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('abonnement_id')->constrained()->restrictOnDelete();
            $table->string('type_frais');
            $table->date('date_debut_periode');
            $table->date('date_fin_periode');
            $table->integer('quantite');
            $table->decimal('cout_unitaire', 10, 2);
            $table->decimal('montant_total', 10, 2);
            $table->boolean('facture')->default(false);
            $table->foreignUuid('facturation_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('statut', ['en_attente', 'facture', 'annule'])->default('en_attente');
            $table->text('description')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frais_usages');
    }
};
