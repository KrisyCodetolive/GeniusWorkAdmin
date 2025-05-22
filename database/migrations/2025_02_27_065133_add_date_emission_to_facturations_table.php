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
        Schema::table('facturations', function (Blueprint $table) {
            if (!Schema::hasColumn('facturations', 'date_emission')) {
                $table->date('date_emission')->nullable()->after('numero_facture');
            }
            
            // Vérifier si d'autres colonnes mentionnées dans le FacturationResource existent
            if (!Schema::hasColumn('facturations', 'date_echeance')) {
                $table->date('date_echeance')->nullable()->after('date_emission');
            }
            
            if (!Schema::hasColumn('facturations', 'montant_ttc')) {
                $table->decimal('montant_ttc', 10, 2)->nullable()->after('taux_tva');
            }
            
            if (!Schema::hasColumn('facturations', 'montant_ht')) {
                $table->decimal('montant_ht', 10, 2)->nullable()->after('date_echeance');
            }
            
            if (!Schema::hasColumn('facturations', 'taux_tva')) {
                $table->decimal('taux_tva', 5, 2)->nullable()->after('montant_ht');
            }
            
            if (!Schema::hasColumn('facturations', 'mode_paiement')) {
                $table->string('mode_paiement')->nullable()->after('statut');
            }
            
            if (!Schema::hasColumn('facturations', 'envoyer_automatiquement')) {
                $table->boolean('envoyer_automatiquement')->default(false)->after('mode_paiement');
            }
            
            if (!Schema::hasColumn('facturations', 'notes')) {
                $table->text('notes')->nullable()->after('envoyer_automatiquement');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('facturations', function (Blueprint $table) {
            $table->dropColumn([
                'date_emission',
                'date_echeance',
                'montant_ht',
                'taux_tva',
                'montant_ttc',
                'mode_paiement',
                'envoyer_automatiquement',
                'notes'
            ]);
        });
    }
};
