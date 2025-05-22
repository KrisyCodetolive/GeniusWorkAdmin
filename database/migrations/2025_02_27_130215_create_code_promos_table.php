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
        Schema::create('code_promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->decimal('reduction', 5, 2); // Pourcentage de réduction (ex: 15.00 pour 15%)
            $table->dateTime('date_debut');
            $table->dateTime('date_expiration');
            $table->integer('nombre_utilisations_max')->nullable(); // Nombre maximum d'utilisations, null = illimité
            $table->integer('nombre_utilisations')->default(0); // Nombre d'utilisations actuelles
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Pour ne pas supprimer définitivement les codes utilisés
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_promos');
    }
};
