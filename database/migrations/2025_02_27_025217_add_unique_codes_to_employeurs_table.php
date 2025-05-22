<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employeurs', function (Blueprint $table) {
            $table->string('code_employe')->unique()->after('id');
            $table->string('qr_code_secret')->unique()->after('code_employe');
            $table->timestamp('qr_code_expires_at')->nullable()->after('qr_code_secret');
            $table->boolean('qr_code_active')->default(true)->after('qr_code_expires_at');
        });
    }

    public function down()
    {
        Schema::table('employeurs', function (Blueprint $table) {
            $table->dropColumn(['code_employe', 'qr_code_secret', 'qr_code_expires_at', 'qr_code_active']);
        });
    }
};
