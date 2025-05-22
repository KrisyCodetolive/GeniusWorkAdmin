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
        Schema::table('jours', function (Blueprint $table) {
            $table->dropForeign(['jour_travail_id']);
            $table->dropColumn('jour_travail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jours', function (Blueprint $table) {
            $table->uuid('jour_travail_id')->nullable()->after('id');
            $table->foreign('jour_travail_id')->references('id')->on('jour_travails')->onDelete('set null');
        });
    }
};
