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
        Schema::table('employeurs', function (Blueprint $table) {
            $table->foreignUuid('filiale_id')->nullable()->after('departement_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employeurs', function (Blueprint $table) {
            $table->dropForeign(['filiale_id']);
            $table->dropColumn('filiale_id');
        });
    }
};
