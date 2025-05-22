<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->nullableUuidMorphs('notifiable');
            $table->string('type');
            $table->text('message');
            $table->json('data')->nullable();
            $table->datetime('date_envoi')->nullable();
            $table->datetime('date_lecture')->nullable();
            $table->enum('canal', ['email', 'sms', 'push', 'interne'])->default('interne');
            $table->enum('priorite', ['basse', 'normale', 'haute'])->default('normale');
            $table->enum('statut', ['en_attente', 'envoye', 'echec', 'lu'])->default('en_attente');
            $table->text('erreur')->nullable();
            $table->integer('tentatives')->default(0);
            $table->datetime('prochaine_tentative')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
