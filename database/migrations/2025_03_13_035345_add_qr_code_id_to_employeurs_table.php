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
            $table->unsignedBigInteger('qr_code_id')->nullable()->after('qr_code_active');
            $table->index('qr_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employeurs', function (Blueprint $table) {
            $table->dropColumn('qr_code_id');
        });
    }
};
