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
        Schema::table('appareil_biometriques', function (Blueprint $table) {
            // Champs pour la synchronisation automatique
            $table->boolean('sync_auto_enabled')->default(false)->after('statut');
            $table->timestamp('derniere_sync_auto')->nullable()->after('dernier_sync');
            
            // Intervalles de synchronisation en minutes
            $table->integer('sync_logs_interval')->default(60)->after('sync_auto_enabled'); // 1 heure par défaut
            $table->integer('sync_users_interval')->default(1440)->after('sync_logs_interval'); // 24 heures par défaut
            $table->integer('sync_time_interval')->default(1440)->after('sync_users_interval'); // 24 heures par défaut
            
            // Options de synchronisation
            $table->json('sync_options')->nullable()->after('sync_time_interval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appareil_biometriques', function (Blueprint $table) {
            $table->dropColumn([
                'sync_auto_enabled',
                'derniere_sync_auto',
                'sync_logs_interval',
                'sync_users_interval',
                'sync_time_interval',
                'sync_options'
            ]);
        });
    }
};
