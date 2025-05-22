<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Vérifier si les colonnes existent avant de les ajouter
            if (!Schema::hasColumn('notifications', 'status')) {
                $table->string('status')->default('created'); // created, sent, failed, delivered, read
            }
            
            if (!Schema::hasColumn('notifications', 'canaux_envoyes')) {
                $table->json('canaux_envoyes')->nullable(); // ['email', 'sms', 'app']
            }
            
            if (!Schema::hasColumn('notifications', 'date_envoi')) {
                $table->timestamp('date_envoi')->nullable();
            }
            
            if (!Schema::hasColumn('notifications', 'date_lecture')) {
                $table->timestamp('date_lecture')->nullable();
            }
            
            if (!Schema::hasColumn('notifications', 'erreur')) {
                $table->text('erreur')->nullable(); // Stocke les erreurs d'envoi éventuelles
            }
            
            if (!Schema::hasColumn('notifications', 'type_notification')) {
                $table->string('type_notification', 50)->nullable(); // retard, absence, sortie_manquante, etc.
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $columns = [
                'status',
                'canaux_envoyes',
                'date_envoi',
                'date_lecture',
                'erreur',
                'type_notification'
            ];
            
            // Vérifier si chaque colonne existe avant de la supprimer
            foreach ($columns as $column) {
                if (Schema::hasColumn('notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
