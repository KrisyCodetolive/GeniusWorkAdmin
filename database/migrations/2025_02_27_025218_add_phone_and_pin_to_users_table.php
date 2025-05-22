<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('phone_verified_at')->nullable()->after('phone');
            $table->string('pin')->nullable()->after('password');
            $table->timestamp('pin_changed_at')->nullable()->after('pin');
            $table->boolean('require_pin_change')->default(false)->after('pin_changed_at');
            $table->integer('pin_attempts')->default(0)->after('require_pin_change');
            $table->timestamp('pin_locked_until')->nullable()->after('pin_attempts');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'pin',
                'pin_changed_at',
                'require_pin_change',
                'pin_attempts',
                'pin_locked_until'
            ]);
        });
    }
};
