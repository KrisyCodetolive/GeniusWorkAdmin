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
        Schema::table('departements', function (Blueprint $table) {
            $table->uuid('filiale_id')->nullable()->after('entreprise_id');
            
            // Ajouter la contrainte de clé étrangère
            $table->foreign('filiale_id')
                  ->references('id')
                  ->on('filiales')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->dropForeign(['filiale_id']);
            $table->dropColumn('filiale_id');
        });
    }
};
