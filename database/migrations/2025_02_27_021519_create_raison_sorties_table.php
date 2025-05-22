<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('raison_sorties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('motif');
            $table->text('description')->nullable();
            $table->enum('type', ['pause', 'conge', 'mission', 'autre'])->default('autre');
            $table->integer('duree_max')->nullable(); // en minutes
            $table->boolean('necessite_validation')->default(false);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->json('configuration')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('raison_sorties');
    }
};
