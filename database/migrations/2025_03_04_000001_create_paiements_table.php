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
        Schema::create('paiements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('facturation_id')->nullable()->constrained('facturations')->onDelete('set null');
            $table->foreignUuid('abonnement_id')->nullable()->constrained('abonnements')->onDelete('set null');
            $table->foreignUuid('entreprise_id')->nullable()->constrained('entreprises')->onDelete('set null');
            $table->foreignUuid('initiateur_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignUuid('validateur_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('reference')->unique();
            $table->string('reference_externe')->nullable();
            $table->decimal('montant', 15, 2);
            $table->string('devise', 10)->default('FCFA');
            
            $table->string('methode');
            $table->string('passerelle');
            $table->string('statut')->default('en_attente');
            
            $table->timestamp('date_paiement')->nullable();
            $table->timestamp('date_validation')->nullable();
            
            $table->json('meta_donnees')->nullable();
            $table->text('commentaire')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('reference');
            $table->index('reference_externe');
            $table->index('statut');
            $table->index('methode');
            $table->index('passerelle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
