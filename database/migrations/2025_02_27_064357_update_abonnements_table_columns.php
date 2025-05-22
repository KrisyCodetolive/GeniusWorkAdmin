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
        Schema::table('abonnements', function (Blueprint $table) {
            // Ajouter la nouvelle colonne renouvellement_automatique
            $table->boolean('renouvellement_automatique')->default(true)->after('notes');
            
            // Ajouter la nouvelle colonne periode_facturation
            $table->enum('periode_facturation', ['mensuel', 'trimestriel', 'semestriel', 'annuel'])
                ->default('mensuel')
                ->after('montant');
                
            // Ajouter la nouvelle colonne methode_paiement
            $table->enum('methode_paiement', ['carte', 'virement', 'prelevement'])
                ->default('carte')
                ->after('periode_facturation');
                
            // Ajouter les colonnes manquantes du formulaire
            $table->string('reference_client')->nullable()->after('methode_paiement');
            $table->boolean('facture_automatique')->default(true)->after('methode_paiement');
            $table->text('notes_facturation')->nullable()->after('reference_client');
        });
        
        // Copier les données des anciennes colonnes vers les nouvelles
        DB::statement('UPDATE abonnements SET renouvellement_automatique = renouvellement_auto');
        DB::statement("UPDATE abonnements SET periode_facturation = 
            CASE 
                WHEN periodicite = 'mensuel' THEN 'mensuel' 
                WHEN periodicite = 'annuel' THEN 'annuel' 
                ELSE 'mensuel' 
            END");
        DB::statement("UPDATE abonnements SET methode_paiement = 
            CASE 
                WHEN mode_paiement = 'carte' THEN 'carte' 
                WHEN mode_paiement = 'virement' THEN 'virement' 
                ELSE 'carte' 
            END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropColumn('renouvellement_automatique');
            $table->dropColumn('periode_facturation');
            $table->dropColumn('methode_paiement');
            $table->dropColumn('reference_client');
            $table->dropColumn('facture_automatique');
            $table->dropColumn('notes_facturation');
        });
    }
};
