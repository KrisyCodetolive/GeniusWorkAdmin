<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('methode_pointages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('necessite_photo')->default(false);
            $table->boolean('necessite_geolocalisation')->default(true);
            $table->boolean('necessite_signature')->default(false);
            $table->boolean('necessite_validation')->default(false);
            $table->boolean('autoriser_hors_site')->default(false);
            $table->integer('rayon_geofencing')->default(100); // en mètres
            $table->json('configuration')->nullable();
            $table->json('validation_regles')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('methode_pointages');
    }
};
