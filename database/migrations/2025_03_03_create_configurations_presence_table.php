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
        if (!Schema::hasTable('configurations_presence')) {
            Schema::create('configurations_presence', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('entreprise_id');
                
                // Configuration des heures supplémentaires
                $table->boolean('heures_supplementaires_actives')->default(true);
                
                // Configuration des pointages
                $table->integer('nombre_pointages_par_jour')->default(2);
                $table->boolean('pauses_actives')->default(true);
                
                // Configuration de l'annulation des présences
                $table->boolean('annuler_presence_sans_sortie')->default(true);
                $table->integer('delai_annulation_heures')->default(24);
                
                // Configuration des notifications
                $table->boolean('notifications_actives')->default(true);
                $table->boolean('notification_absence')->default(true);
                $table->boolean('notification_retard')->default(true);
                $table->boolean('notification_conge')->default(true);
                
                // Messages personnalisés
                $table->text('message_absence')->nullable();
                $table->text('message_retard')->nullable();
                $table->text('message_conge')->nullable();
                
                // Configuration avancée (stockée en JSON)
                $table->json('configuration_avancee')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations_presence');
    }
};
