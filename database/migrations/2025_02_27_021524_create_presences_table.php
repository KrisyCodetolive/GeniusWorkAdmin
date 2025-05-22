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
        Schema::create('presences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employeur_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('jour_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('raison_sortie_id')->nullable()->constrained()->nullOnDelete();
            $table->datetime('date_heure_entree');
            $table->datetime('date_heure_sortie')->nullable();
            $table->decimal('latitude_entree', 10, 8)->nullable();
            $table->decimal('longitude_entree', 11, 8)->nullable();
            $table->decimal('latitude_sortie', 10, 8)->nullable();
            $table->decimal('longitude_sortie', 11, 8)->nullable();
            $table->string('adresse_ip_entree')->nullable();
            $table->string('adresse_ip_sortie')->nullable();
            $table->string('appareil_entree')->nullable();
            $table->string('appareil_sortie')->nullable();
            $table->integer('duree_effective')->nullable(); // en minutes
            $table->integer('retard')->nullable(); // en minutes
            $table->integer('depart_anticipe')->nullable(); // en minutes
            $table->enum('statut', ['present', 'absent', 'retard', 'sortie', 'conge'])->default('present');
            $table->enum('statut_validation', ['en_attente', 'approuve', 'rejete'])->default('en_attente');
            $table->foreignUuid('validateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('date_validation')->nullable();
            $table->text('commentaire')->nullable();
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
