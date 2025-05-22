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
        // Vérifions d'abord si la table existe et si la colonne id existe déjà
        if (Schema::hasTable('presences')) {
            // Si la colonne id n'existe pas, on l'ajoute
            if (!Schema::hasColumn('presences', 'id')) {
                Schema::table('presences', function (Blueprint $table) {
                    $table->uuid('id')->primary();
                });
            }
            
            // Ajout des autres colonnes si elles n'existent pas
            Schema::table('presences', function (Blueprint $table) {
                if (!Schema::hasColumn('presences', 'entreprise_id')) {
                    $table->foreignUuid('entreprise_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('presences', 'user_id')) {
                    $table->foreignUuid('user_id')->nullable()->after('entreprise_id')->constrained()->onDelete('set null');
                }
                if (!Schema::hasColumn('presences', 'site_id')) {
                    $table->foreignUuid('site_id')->nullable()->after('user_id')->constrained()->onDelete('set null');
                }
                if (!Schema::hasColumn('presences', 'methode_pointage_id')) {
                    $table->foreignUuid('methode_pointage_id')->nullable()->after('site_id')->constrained('methode_pointages')->onDelete('set null');
                }
                if (!Schema::hasColumn('presences', 'type')) {
                    $table->enum('type', ['entree', 'sortie', 'pause_debut', 'pause_fin']);
                }
                if (!Schema::hasColumn('presences', 'latitude')) {
                    $table->decimal('latitude', 10, 6)->nullable();
                }
                if (!Schema::hasColumn('presences', 'longitude')) {
                    $table->decimal('longitude', 10, 6)->nullable();
                }
                if (!Schema::hasColumn('presences', 'precision_geo')) {
                    $table->decimal('precision_geo', 10, 2)->nullable()->after('longitude');
                }
                if (!Schema::hasColumn('presences', 'date_heure')) {
                    $table->dateTime('date_heure');
                }
                if (!Schema::hasColumn('presences', 'adresse_ip')) {
                    $table->string('adresse_ip')->nullable();
                }
                if (!Schema::hasColumn('presences', 'appareil')) {
                    $table->string('appareil')->nullable();
                }
                if (!Schema::hasColumn('presences', 'navigateur')) {
                    $table->string('navigateur')->nullable()->after('appareil');
                }
                if (!Schema::hasColumn('presences', 'source')) {
                    $table->string('source')->default('web');
                }
                if (!Schema::hasColumn('presences', 'statut')) {
                    $table->string('statut')->default('enregistre');
                }
                if (!Schema::hasColumn('presences', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
                if (!Schema::hasColumn('presences', 'photo_path')) {
                    $table->string('photo_path')->nullable();
                }
                if (!Schema::hasColumn('presences', 'photo_url')) {
                    $table->string('photo_url')->nullable()->after('photo_path');
                }
                if (!Schema::hasColumn('presences', 'signature_path')) {
                    $table->string('signature_path')->nullable();
                }
                if (!Schema::hasColumn('presences', 'signature_url')) {
                    $table->string('signature_url')->nullable()->after('signature_path');
                }
                if (!Schema::hasColumn('presences', 'qr_code')) {
                    $table->string('qr_code')->nullable()->after('signature_url');
                }
                if (!Schema::hasColumn('presences', 'nfc_tag')) {
                    $table->string('nfc_tag')->nullable()->after('qr_code');
                }
                if (!Schema::hasColumn('presences', 'validateur_id')) {
                    $table->foreignUuid('validateur_id')->nullable()->constrained('users')->onDelete('set null');
                }
                if (!Schema::hasColumn('presences', 'date_validation')) {
                    $table->dateTime('date_validation')->nullable();
                }
                if (!Schema::hasColumn('presences', 'distance_site')) {
                    $table->decimal('distance_site', 10, 2)->nullable()->after('date_validation');
                }
                if (!Schema::hasColumn('presences', 'verification_data')) {
                    $table->json('verification_data')->nullable()->after('distance_site');
                }
                if (!Schema::hasColumn('presences', 'webauthn_credential_id')) {
                    $table->foreignUuid('webauthn_credential_id')->nullable()->after('verification_data');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration modifie une table existante, donc la suppression complète
        // n'est pas recommandée. On peut supprimer certaines colonnes si nécessaire.
        if (Schema::hasTable('presences')) {
            Schema::table('presences', function (Blueprint $table) {
                // Supprimer les colonnes ajoutées si nécessaire
                // $table->dropColumn('precision_geo');
                // etc.
            });
        }
    }
};
