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
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('type', ['financier', 'presence', 'performance', 'absence', 'heures_supp', 'conge', 'autre']);
            $table->enum('format', ['pdf', 'excel', 'csv', 'html', 'json'])->default('pdf');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->date('date_generation')->nullable();
            $table->unsignedBigInteger('employeur_id')->nullable();
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('generateur_id')->nullable();
            $table->json('parametres')->nullable();
            $table->json('donnees')->nullable();
            $table->string('fichier_path')->nullable();
            $table->string('statut')->default('généré');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
