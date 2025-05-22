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
        Schema::create('facturations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('abonnement_id')->constrained()->restrictOnDelete();
            $table->string('numero_facture')->unique();
            $table->date('date_facturation');
            $table->date('date_echeance');
            $table->decimal('montant_ht', 10, 2);
            $table->decimal('taux_tva', 5, 2)->default(18.00);
            $table->decimal('montant_tva', 10, 2);
            $table->decimal('montant_ttc', 10, 2);
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->enum('statut', ['en_attente', 'payee', 'partielle', 'retard', 'annulee'])->default('en_attente');
            $table->enum('mode_paiement', ['carte', 'virement', 'especes', 'card' , 'wave' , 'mobile_money'])->nullable();
            $table->string('reference_paiement')->nullable();
            $table->date('date_paiement')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('facturations');
    }
};
