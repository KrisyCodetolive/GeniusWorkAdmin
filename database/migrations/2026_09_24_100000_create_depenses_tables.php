<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Circuit des sorties d'argent : demande -> comptabilité -> CEO (au-delà du seuil) -> décaissement.
     */
    public function up(): void
    {
        Schema::create('categories_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->string('nom');
            $table->string('code_comptable')->nullable();
            $table->text('description')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
        });

        Schema::create('parametres_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id')->unique();
            $table->decimal('seuil_validation_ceo', 15, 2)->default(500000);
            $table->uuid('ceo_user_id')->nullable();
            $table->string('prefixe_reference', 10)->default('DEP');
            $table->string('devise', 10)->default('FCFA');
            $table->timestamps();

            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('ceo_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('demandes_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('entreprise_id');
            $table->string('reference');
            $table->uuid('demandeur_id')->nullable();
            $table->uuid('cree_par_user_id');
            $table->uuid('categorie_depense_id')->nullable();
            $table->uuid('departement_id')->nullable();
            $table->string('objet');
            $table->text('description')->nullable();
            $table->decimal('montant', 15, 2);
            $table->string('devise', 10)->default('FCFA');
            $table->string('beneficiaire');
            $table->date('date_besoin')->nullable();
            $table->string('statut')->default('brouillon')->index();
            $table->string('mode_paiement')->nullable();
            $table->string('reference_paiement')->nullable();
            $table->date('date_paiement')->nullable();
            $table->uuid('payee_par_user_id')->nullable();
            $table->string('preuve_paiement')->nullable();
            $table->string('pdf_bon_sortie')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entreprise_id', 'reference']);
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('demandeur_id')->references('id')->on('employeurs')->nullOnDelete();
            $table->foreign('cree_par_user_id')->references('id')->on('users');
            $table->foreign('categorie_depense_id')->references('id')->on('categories_depense')->nullOnDelete();
            $table->foreign('departement_id')->references('id')->on('departements')->nullOnDelete();
            $table->foreign('payee_par_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('justificatifs_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('demande_depense_id');
            $table->string('fichier');
            $table->string('nom_original')->nullable();
            $table->string('type')->default('autre');
            $table->timestamps();

            $table->foreign('demande_depense_id')->references('id')->on('demandes_depense')->onDelete('cascade');
        });

        // Journal des étapes : on n'y fait que des insertions, jamais de modification ni de suppression.
        Schema::create('validations_depense', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('demande_depense_id');
            $table->string('etape');
            $table->string('decision');
            $table->uuid('user_id');
            $table->text('commentaire')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('hash_document', 64)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('signe_le');
            $table->timestamps();

            $table->foreign('demande_depense_id')->references('id')->on('demandes_depense');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validations_depense');
        Schema::dropIfExists('justificatifs_depense');
        Schema::dropIfExists('demandes_depense');
        Schema::dropIfExists('parametres_depense');
        Schema::dropIfExists('categories_depense');
    }
};
