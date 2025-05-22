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
        Schema::table('abonnements', function (Blueprint $table) {
            // Vérifier si les colonnes existent avant de les ajouter
            if (!Schema::hasColumn('abonnements', 'type_periode')) {
                $table->string('type_periode')->after('montant')->nullable();
            }
            
            if (!Schema::hasColumn('abonnements', 'periode_facturation')) {
                $table->string('periode_facturation')->after('type_periode')->nullable();
            }
            
            if (!Schema::hasColumn('abonnements', 'facture_automatique')) {
                $table->boolean('facture_automatique')->after('periode_facturation')->default(true);
            }
            
            if (!Schema::hasColumn('abonnements', 'nombre_personnels')) {
                $table->integer('nombre_personnels')->after('facture_automatique')->default(1);
            }
            
            if (!Schema::hasColumn('abonnements', 'reduction_code_promo')) {
                $table->decimal('reduction_code_promo', 5, 2)->after('montant')->nullable();
            }
            
            // Renommer le champ renouvellement_auto en renouvellement_automatique s'il existe
            if (Schema::hasColumn('abonnements', 'renouvellement_auto') && !Schema::hasColumn('abonnements', 'renouvellement_automatique')) {
                $table->renameColumn('renouvellement_auto', 'renouvellement_automatique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            // Supprimer les colonnes si elles existent
            if (Schema::hasColumn('abonnements', 'type_periode')) {
                $table->dropColumn('type_periode');
            }
            
            if (Schema::hasColumn('abonnements', 'periode_facturation')) {
                $table->dropColumn('periode_facturation');
            }
            
            if (Schema::hasColumn('abonnements', 'facture_automatique')) {
                $table->dropColumn('facture_automatique');
            }
            
            if (Schema::hasColumn('abonnements', 'nombre_personnels')) {
                $table->dropColumn('nombre_personnels');
            }
            
            if (Schema::hasColumn('abonnements', 'reduction_code_promo')) {
                $table->dropColumn('reduction_code_promo');
            }
            
            // Rétablir le nom original du champ s'il existe
            if (Schema::hasColumn('abonnements', 'renouvellement_automatique') && !Schema::hasColumn('abonnements', 'renouvellement_auto')) {
                $table->renameColumn('renouvellement_automatique', 'renouvellement_auto');
            }
        });
    }
};
