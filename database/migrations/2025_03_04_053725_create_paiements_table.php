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
        // Vérifier si la table existe déjà
        if (!Schema::hasTable('paiements')) {
            Schema::create('paiements', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('facturation_id')->nullable();
                $table->uuid('abonnement_id')->nullable();
                $table->uuid('entreprise_id')->nullable();
                $table->uuid('initiateur_id')->nullable();
                $table->uuid('validateur_id')->nullable();
                
                $table->string('reference')->unique();
                $table->string('reference_externe')->nullable()->unique();
                $table->string('passerelle')->nullable();
                $table->string('methode');
                $table->decimal('montant', 12, 2);
                $table->decimal('montant_recu', 12, 2)->nullable();
                $table->string('devise')->default('FCFA');
                $table->string('statut');
                $table->text('commentaire')->nullable();
                $table->json('metadata')->nullable();
                
                $table->timestamp('date_validation')->nullable();
                $table->timestamp('date_annulation')->nullable();
                $table->timestamp('date_remboursement')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('facturation_id')
                    ->references('id')
                    ->on('facturations')
                    ->onDelete('set null');
                    
                $table->foreign('abonnement_id')
                    ->references('id')
                    ->on('abonnements')
                    ->onDelete('set null');
                    
                $table->foreign('entreprise_id')
                    ->references('id')
                    ->on('entreprises')
                    ->onDelete('set null');
                    
                $table->foreign('initiateur_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
                    
                $table->foreign('validateur_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        } else {
            // Si la table existe déjà, vérifier et ajouter les colonnes manquantes
            Schema::table('paiements', function (Blueprint $table) {
                if (!Schema::hasColumn('paiements', 'facturation_id')) {
                    $table->uuid('facturation_id')->nullable();
                    
                    $table->foreign('facturation_id')
                        ->references('id')
                        ->on('facturations')
                        ->onDelete('set null');
                }
                
                if (!Schema::hasColumn('paiements', 'abonnement_id')) {
                    $table->uuid('abonnement_id')->nullable();
                    
                    $table->foreign('abonnement_id')
                        ->references('id')
                        ->on('abonnements')
                        ->onDelete('set null');
                }
                
                if (!Schema::hasColumn('paiements', 'entreprise_id')) {
                    $table->uuid('entreprise_id')->nullable();
                    
                    $table->foreign('entreprise_id')
                        ->references('id')
                        ->on('entreprises')
                        ->onDelete('set null');
                }
                
                if (!Schema::hasColumn('paiements', 'initiateur_id')) {
                    $table->uuid('initiateur_id')->nullable();
                    
                    $table->foreign('initiateur_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                }
                
                if (!Schema::hasColumn('paiements', 'validateur_id')) {
                    $table->uuid('validateur_id')->nullable();
                    
                    $table->foreign('validateur_id')
                        ->references('id')
                        ->on('users')
                        ->onDelete('set null');
                }
                
                if (!Schema::hasColumn('paiements', 'reference')) {
                    $table->string('reference')->unique();
                }
                
                if (!Schema::hasColumn('paiements', 'reference_externe')) {
                    $table->string('reference_externe')->nullable()->unique();
                }
                
                if (!Schema::hasColumn('paiements', 'passerelle')) {
                    $table->string('passerelle')->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'methode')) {
                    $table->string('methode');
                }
                
                if (!Schema::hasColumn('paiements', 'montant')) {
                    $table->decimal('montant', 12, 2);
                }
                
                if (!Schema::hasColumn('paiements', 'montant_recu')) {
                    $table->decimal('montant_recu', 12, 2)->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'devise')) {
                    $table->string('devise')->default('FCFA');
                }
                
                if (!Schema::hasColumn('paiements', 'statut')) {
                    $table->string('statut');
                }
                
                if (!Schema::hasColumn('paiements', 'commentaire')) {
                    $table->text('commentaire')->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'date_validation')) {
                    $table->timestamp('date_validation')->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'date_annulation')) {
                    $table->timestamp('date_annulation')->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'date_remboursement')) {
                    $table->timestamp('date_remboursement')->nullable();
                }
                
                if (!Schema::hasColumn('paiements', 'created_at')) {
                    $table->timestamps();
                }
                
                if (!Schema::hasColumn('paiements', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
