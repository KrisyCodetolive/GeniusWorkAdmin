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
        Schema::table('presences', function (Blueprint $table) {
            if (!Schema::hasColumn('presences', 'supplementaire_id')) {
                $table->uuid('supplementaire_id')->nullable()->after('minutes_supplementaires');
                $table->foreign('supplementaire_id')
                      ->references('id')
                      ->on('supplementaires')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            if (Schema::hasColumn('presences', 'supplementaire_id')) {
                $table->dropForeign(['supplementaire_id']);
                $table->dropColumn('supplementaire_id');
            }
        });
    }
};
