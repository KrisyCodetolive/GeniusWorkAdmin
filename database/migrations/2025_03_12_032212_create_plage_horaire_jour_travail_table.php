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
        Schema::create('plage_horaire_jour_travail', function (Blueprint $table) {
            $table->uuid('plage_horaire_id');
            $table->uuid('jour_travail_id');
            $table->timestamps();

            $table->primary(['plage_horaire_id', 'jour_travail_id']);
            
            $table->foreign('plage_horaire_id')
                ->references('id')
                ->on('plage_horaires')
                ->onDelete('cascade');
                
            $table->foreign('jour_travail_id')
                ->references('id')
                ->on('jour_travails')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plage_horaire_jour_travail');
    }
};
