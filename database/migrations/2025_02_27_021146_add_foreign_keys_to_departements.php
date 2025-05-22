<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->foreignUuid('responsable_id')->nullable()->constrained('employeurs')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('departements', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            $table->dropColumn('responsable_id');
        });
    }
};
