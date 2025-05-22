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
        Schema::create('permutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employeur_1_id')->constrained('employeurs')->cascadeOnDelete();
            $table->foreignUuid('employeur_2_id')->constrained('employeurs')->cascadeOnDelete();
            $table->foreignUuid('plage_horaire_1_id')->constrained('plage_horaires')->cascadeOnDelete();
            $table->foreignUuid('plage_horaire_2_id')->constrained('plage_horaires')->cascadeOnDelete();
            $table->date('date');
            $table->string('motif');
            $table->enum('statut', ['en_attente', 'approuve', 'rejete', 'annule'])->default('en_attente');
            $table->text('commentaire')->nullable();
            $table->foreignUuid('validateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('date_validation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permutations');
    }
};
