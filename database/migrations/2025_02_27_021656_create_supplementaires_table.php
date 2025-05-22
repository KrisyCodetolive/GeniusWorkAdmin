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
        Schema::create('supplementaires', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employeur_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->datetime('heure_debut');
            $table->datetime('heure_fin');
            $table->decimal('nombre_heures', 5, 2);
            $table->decimal('taux_majoration', 5, 2)->default(50); // 50% par défaut
            $table->decimal('montant', 10, 2);
            $table->string('motif');
            $table->enum('statut', ['en_attente', 'approuve', 'rejete', 'annule'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->foreignUuid('validateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('date_validation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplementaires');
    }
};
