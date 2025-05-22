<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('appareil_biometriques')) {
            Schema::create('appareil_biometriques', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('site_id')->nullable()->constrained()->nullOnDelete();
                $table->string('nom');
                $table->string('modele');
                $table->string('fabricant');
                $table->string('numero_serie')->nullable();
                $table->string('adresse_ip');
                $table->integer('port')->default(4370); // Port par défaut pour de nombreux appareils biométriques
                $table->string('protocole')->default('TCP'); // TCP, UDP, HTTP, etc.
                $table->string('identifiant_connexion')->nullable();
                $table->string('mot_de_passe')->nullable();
                $table->string('cle_api')->nullable();
                $table->json('configuration')->nullable();
                $table->timestamp('dernier_sync')->nullable();
                $table->enum('statut', ['actif', 'inactif', 'maintenance', 'erreur'])->default('actif');
                $table->string('version_firmware')->nullable();
                $table->integer('capacite_empreintes')->nullable();
                $table->integer('capacite_visages')->nullable();
                $table->integer('capacite_cartes')->nullable();
                $table->integer('capacite_logs')->nullable();
                $table->string('type_authentification')->default('multiple'); // empreinte, visage, carte, code, multiple
                $table->json('options_disponibles')->nullable();
                $table->json('parametres_avances')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('log_appareil_biometriques')) {
            Schema::create('log_appareil_biometriques', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('appareil_biometrique_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type_evenement'); // connexion, deconnexion, erreur, synchronisation, pointage, configuration, etc.
                $table->json('details')->nullable();
                $table->enum('statut', ['success', 'error', 'warning', 'info'])->default('info');
                $table->timestamp('date_evenement');
                $table->json('donnees_brutes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('appareil_biometrique_user')) {
            Schema::create('appareil_biometrique_user', function (Blueprint $table) {
                $table->id();
                $table->foreignUuid('appareil_biometrique_id')->constrained()->cascadeOnDelete();
                $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
                $table->string('identifiant_biometrique'); // ID unique de l'utilisateur sur l'appareil
                $table->enum('type_donnee', ['empreinte', 'visage', 'carte', 'code', 'multiple'])->default('empreinte');
                $table->timestamp('date_enregistrement');
                $table->enum('statut', ['actif', 'inactif'])->default('actif');
                $table->timestamps();
                
                // Utiliser un nom plus court pour l'index unique
                $table->unique(['appareil_biometrique_id', 'user_id', 'type_donnee'], 'appareil_bio_user_unique');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('appareil_biometrique_user');
        Schema::dropIfExists('log_appareil_biometriques');
        Schema::dropIfExists('appareil_biometriques');
    }
};
