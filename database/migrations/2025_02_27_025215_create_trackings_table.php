<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trackings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employeur_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('presence_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['entree', 'sortie', 'pause_debut', 'pause_fin']);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('adresse')->nullable();
            $table->datetime('date_heure');
            $table->string('methode_pointage');
            $table->string('appareil')->nullable();
            $table->string('adresse_ip')->nullable();
            $table->json('meta_donnees')->nullable();
            $table->enum('statut', ['valide', 'invalide', 'suspect'])->default('valide');
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trackings');
    }
};
