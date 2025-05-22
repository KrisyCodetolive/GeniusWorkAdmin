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
        if (!Schema::hasTable('type_conges')) {
            Schema::create('type_conges', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('employeur_id')->constrained('employeurs')->onDelete('cascade');
                $table->string('code', 10)->index();
                $table->string('libelle');
                $table->text('description')->nullable();
                $table->string('couleur', 20)->nullable();
                $table->string('icone', 50)->nullable();
                $table->decimal('duree_max', 8, 2)->nullable();
                $table->decimal('duree_min', 8, 2)->default(0.5);
                $table->integer('delai_prevenance')->default(0);
                $table->boolean('justificatif_requis')->default(false);
                $table->boolean('deductible')->default(true);
                $table->boolean('fractionnable')->default(true);
                $table->boolean('report_autorise')->default(false);
                $table->json('workflow_validation')->nullable();
                $table->json('restrictions')->nullable();
                $table->json('regle_acquisition')->nullable();
                $table->boolean('actif')->default(true);
                $table->timestamps();
                $table->softDeletes();
                
                // Contrainte d'unicité sur le code par employeur
                $table->unique(['employeur_id', 'code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_conges');
    }
};
