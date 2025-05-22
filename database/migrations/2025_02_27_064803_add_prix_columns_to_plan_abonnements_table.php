<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plan_abonnements', function (Blueprint $table) {
            // Vérifier si la colonne prix existe et la supprimer
            if (Schema::hasColumn('plan_abonnements', 'prix')) {
                // Sauvegarder les valeurs de prix avant de supprimer la colonne
                $plans = DB::table('plan_abonnements')->select('id', 'prix')->get();
                
                // Ajouter les nouvelles colonnes
                $table->decimal('prix_mensuel', 10, 2)->nullable();
                $table->decimal('prix_annuel', 10, 2)->nullable();
                
                // Supprimer l'ancienne colonne
                $table->dropColumn('prix');
                
                // Mettre à jour les nouvelles colonnes avec les anciennes valeurs
                foreach ($plans as $plan) {
                    if ($plan->prix) {
                        DB::table('plan_abonnements')
                            ->where('id', $plan->id)
                            ->update([
                                'prix_mensuel' => $plan->prix,
                                'prix_annuel' => $plan->prix * 10, // Prix annuel = prix mensuel * 10 (réduction de 2 mois)
                            ]);
                    }
                }
            } else {
                // Si la colonne prix n'existe pas, ajouter simplement les nouvelles colonnes
                if (!Schema::hasColumn('plan_abonnements', 'prix_mensuel')) {
                    $table->decimal('prix_mensuel', 10, 2)->nullable();
                }
                
                if (!Schema::hasColumn('plan_abonnements', 'prix_annuel')) {
                    $table->decimal('prix_annuel', 10, 2)->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_abonnements', function (Blueprint $table) {
            // Vérifier si les colonnes existent avant de les supprimer
            if (Schema::hasColumn('plan_abonnements', 'prix_mensuel')) {
                $table->dropColumn('prix_mensuel');
            }
            
            if (Schema::hasColumn('plan_abonnements', 'prix_annuel')) {
                $table->dropColumn('prix_annuel');
            }
            
            // Recréer la colonne prix originale
            if (!Schema::hasColumn('plan_abonnements', 'prix')) {
                $table->decimal('prix', 10, 2)->nullable();
            }
        });
    }
};
