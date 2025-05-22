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
        Schema::create('filiales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->string('nom');
            $table->string('code')->unique();
            $table->string('adresse')->nullable();
            $table->string('ville');
            $table->string('pays');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->json('configuration')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entreprise_id')
                  ->references('id')
                  ->on('entreprises')
                  ->onDelete('cascade');
        });

        // Table pivot pour les responsables de filiales
        Schema::create('filiale_responsables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('filiale_id');
            $table->uuid('employeur_id');
            $table->enum('role', ['responsable_principal', 'responsable_adjoint']);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('filiale_id')
                  ->references('id')
                  ->on('filiales')
                  ->onDelete('cascade');
            
            $table->foreign('employeur_id')
                  ->references('id')
                  ->on('employeurs')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filiale_responsables');
        Schema::dropIfExists('filiales');
    }
};
