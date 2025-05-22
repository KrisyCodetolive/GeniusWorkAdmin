<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employeur_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('type_conge_id')->constrained('type_conges')->restrictOnDelete();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_jours');
            $table->text('motif')->nullable();
            $table->string('justificatif')->nullable();
            $table->enum('statut', ['en_attente', 'approuve', 'rejete', 'annule'])->default('en_attente');
            $table->foreignUuid('validateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('date_validation')->nullable();
            $table->text('commentaire_validation')->nullable();
            $table->boolean('est_paye')->default(true);
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conges');
    }
};
