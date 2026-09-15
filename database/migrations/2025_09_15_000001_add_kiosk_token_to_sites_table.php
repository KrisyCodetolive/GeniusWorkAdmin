<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('kiosk_token', 64)->nullable()->unique()->after('qr_token');
            $table->timestamp('kiosk_activated_at')->nullable()->after('kiosk_token');
        });

        // Générer un kiosk_token pour les sites existants
        foreach (\App\Models\Site::whereNull('kiosk_token')->get() as $site) {
            $site->kiosk_token = Str::random(48);
            $site->save();
        }
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['kiosk_token', 'kiosk_activated_at']);
        });
    }
};
