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
        Schema::table('jours', function (Blueprint $table) {
            $table->uuid('entreprise_id')->nullable()->after('plage_horaire_id');
            $table->string('jour_semaine')->nullable()->after('entreprise_id');
            
            // Ajouter la clé étrangère pour entreprise_id
            $table->foreign('entreprise_id')
                ->references('id')
                ->on('entreprises')
                ->onDelete('cascade');
                
            // Rendre jour_travail_id nullable car il ne sera plus obligatoire
            $table->uuid('jour_travail_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jours', function (Blueprint $table) {
            // Vérifier si la clé étrangère existe avant de la supprimer
            // Utiliser try-catch pour éviter les erreurs si la contrainte n'existe pas
            try {
                $table->dropForeign(['entreprise_id']);
            } catch (\Exception $e) {
                // La contrainte n'existe pas, on continue
            }
            
            // Supprimer les colonnes si elles existent
            if (Schema::hasColumn('jours', 'entreprise_id')) {
                $table->dropColumn('entreprise_id');
            }
            
            if (Schema::hasColumn('jours', 'jour_semaine')) {
                $table->dropColumn('jour_semaine');
            }
            
            // Note: Nous ne rendons pas jour_travail_id non nullable
            // car cela causerait des erreurs avec les données existantes
            // qui peuvent contenir des valeurs NULL
        });
    }
};
