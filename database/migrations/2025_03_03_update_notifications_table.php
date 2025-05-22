<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier d'abord si la table notifications existe
        if (!Schema::hasTable('notifications')) {
            // Si la table n'existe pas, on ne fait rien
            return;
        }
        
        // Vérifier si les colonnes existent déjà
        $addUserColumn = !Schema::hasColumn('notifications', 'user_id');
        $addEntrepriseColumn = !Schema::hasColumn('notifications', 'entreprise_id');
        $addEmailEnvoyeColumn = !Schema::hasColumn('notifications', 'email_envoye');
        $addSmsEnvoyeColumn = !Schema::hasColumn('notifications', 'sms_envoye');
        $addLuColumn = !Schema::hasColumn('notifications', 'lu');
        
        // Ajouter les colonnes sans contraintes de clé étrangère d'abord
        if ($addUserColumn || $addEntrepriseColumn || $addEmailEnvoyeColumn || $addSmsEnvoyeColumn || $addLuColumn) {
            Schema::table('notifications', function (Blueprint $table) use ($addUserColumn, $addEntrepriseColumn, $addEmailEnvoyeColumn, $addSmsEnvoyeColumn, $addLuColumn) {
                if ($addUserColumn) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('id');
                }
                
                if ($addEntrepriseColumn) {
                    $table->unsignedBigInteger('entreprise_id')->nullable()->after('user_id');
                }
                
                if ($addEmailEnvoyeColumn) {
                    $table->boolean('email_envoye')->default(false)->after('data');
                }
                
                if ($addSmsEnvoyeColumn) {
                    $table->boolean('sms_envoye')->default(false)->after('email_envoye');
                }
                
                if ($addLuColumn) {
                    $table->boolean('lu')->default(false)->after('sms_envoye');
                }
            });
        }
        
        // Nous n'ajoutons pas de contraintes de clé étrangère pour éviter les problèmes
        // Les relations seront gérées au niveau du modèle Laravel
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vérifier si la table notifications existe
        if (!Schema::hasTable('notifications')) {
            return;
        }
        
        // Supprimer les colonnes
        Schema::table('notifications', function (Blueprint $table) {
            $columns = [];
            
            if (Schema::hasColumn('notifications', 'user_id')) {
                $columns[] = 'user_id';
            }
            
            if (Schema::hasColumn('notifications', 'entreprise_id')) {
                $columns[] = 'entreprise_id';
            }
            
            if (Schema::hasColumn('notifications', 'email_envoye')) {
                $columns[] = 'email_envoye';
            }
            
            if (Schema::hasColumn('notifications', 'sms_envoye')) {
                $columns[] = 'sms_envoye';
            }
            
            if (Schema::hasColumn('notifications', 'lu')) {
                $columns[] = 'lu';
            }
            
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
