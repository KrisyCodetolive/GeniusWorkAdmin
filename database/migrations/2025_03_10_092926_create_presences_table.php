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
        // Vérifier si la table existe déjà
        if (!Schema::hasTable('presences')) {
            Schema::create('presences', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('employeur_id');
                $table->uuid('user_id');
                $table->uuid('jour_id')->nullable();
                $table->uuid('raison_sortie_id')->nullable();
                $table->dateTime('date_heure_entree')->nullable();
                $table->dateTime('date_heure_sortie')->nullable();
                $table->decimal('latitude_entree', 10, 8)->nullable();
                $table->decimal('longitude_entree', 11, 8)->nullable();
                $table->decimal('latitude_sortie', 10, 8)->nullable();
                $table->decimal('longitude_sortie', 11, 8)->nullable();
                $table->string('adresse_ip_entree', 255)->nullable();
                $table->string('adresse_ip_sortie', 255)->nullable();
                $table->string('appareil_entree', 255)->nullable();
                $table->string('appareil_sortie', 255)->nullable();
                $table->integer('duree_effective')->nullable();
                $table->integer('retard')->nullable();
                $table->integer('depart_anticipe')->nullable();
                $table->enum('statut', ['present', 'absent', 'retard', 'sortie', 'conge'])->default('present');
                $table->enum('statut_validation', ['en_attente', 'approve', 'rejete'])->default('en_attente');
                $table->uuid('validateur_id')->nullable();
                $table->dateTime('date_validation')->nullable();
                $table->text('commentaire')->nullable();
                $table->longText('meta_donnees')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                // Clés étrangères
                $table->foreign('employeur_id')->references('id')->on('employeurs');
                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('jour_id')->references('id')->on('jours');
                $table->foreign('raison_sortie_id')->references('id')->on('raison_sorties');
                $table->foreign('validateur_id')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};
