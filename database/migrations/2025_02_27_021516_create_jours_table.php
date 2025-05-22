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
        Schema::create('jours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jour_travail_id')->constrained('jour_travails');
            $table->foreignUuid('employeur_id')->constrained('employeurs');
            $table->foreignUuid('plage_horaire_id')->constrained('plage_horaires');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jours');
    }
};
