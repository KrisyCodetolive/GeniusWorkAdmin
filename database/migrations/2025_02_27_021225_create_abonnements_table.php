<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('abonnements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('plan_abonnement_id')->constrained()->restrictOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_renouvellement')->nullable();
            $table->decimal('montant', 10, 2);
            $table->enum('periodicite', ['mensuel', 'annuel'])->default('mensuel');
            $table->enum('mode_paiement', ['carte', 'virement', 'especes', 'non_specifie', 'card' , 'wave' , 'mobile_money'])->default('carte');
            $table->enum('statut', ['actif', 'inactif', 'essai', 'expire', 'resilie'])->default('actif');
            $table->json('fonctionnalites_activees')->nullable();
            $table->json('limitations_specifiques')->nullable();
            $table->json('configuration')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('renouvellement_auto')->default(true);
            $table->date('date_resiliation')->nullable();
            $table->string('motif_resiliation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('abonnements');
    }
};
