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
            // Champs de géolocalisation
            if (!Schema::hasColumn('presences', 'precision_geo')) {
                $table->decimal('precision_geo', 10, 2)->nullable()->after('longitude_sortie');
            }
            if (!Schema::hasColumn('presences', 'distance_site')) {
                $table->decimal('distance_site', 10, 2)->nullable()->after('precision_geo');
            }
            
            // Champs d'identification de l'appareil
            if (!Schema::hasColumn('presences', 'navigateur')) {
                $table->string('navigateur')->nullable()->after('appareil_sortie');
            }
            
            // Champs de vérification
            if (!Schema::hasColumn('presences', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('navigateur');
            }
            if (!Schema::hasColumn('presences', 'signature_url')) {
                $table->string('signature_url')->nullable()->after('photo_url');
            }
            if (!Schema::hasColumn('presences', 'qr_code')) {
                $table->string('qr_code')->nullable()->after('signature_url');
            }
            if (!Schema::hasColumn('presences', 'nfc_tag')) {
                $table->string('nfc_tag')->nullable()->after('qr_code');
            }
            if (!Schema::hasColumn('presences', 'source')) {
                $table->string('source')->nullable()->after('nfc_tag');
            }
            if (!Schema::hasColumn('presences', 'verification_data')) {
                $table->json('verification_data')->nullable()->after('source');
            }
            if (!Schema::hasColumn('presences', 'webauthn_credential_id')) {
                $table->uuid('webauthn_credential_id')->nullable()->after('verification_data');
            }
            if (!Schema::hasColumn('presences', 'appareil_id')) {
                $table->uuid('appareil_id')->nullable()->after('raison_sortie_id');
            }
            
            // Champs pour les métadonnées
            if (!Schema::hasColumn('presences', 'meta_donnees')) {
                $table->json('meta_donnees')->nullable()->after('commentaire');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            $table->dropColumn([
                'precision_geo',
                'distance_site',
                'navigateur',
                'photo_url',
                'signature_url',
                'qr_code',
                'nfc_tag',
                'source',
                'verification_data',
                'webauthn_credential_id',
                'appareil_id',
                'meta_donnees'
            ]);
        });
    }
};
