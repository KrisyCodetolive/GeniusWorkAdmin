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
        Schema::create('elements_paie', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bulletin_paie_id');
            $table->string('code', 10);
            $table->string('libelle');
            $table->string('type'); // salaire, indemnite, prime, retenue_salariale, charge_patronale
            $table->string('categorie');
            $table->decimal('base', 15, 2)->nullable();
            $table->decimal('taux', 8, 4)->nullable();
            $table->decimal('montant', 15, 2);
            $table->boolean('imposable')->default(true);
            $table->integer('ordre')->default(0);
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('bulletin_paie_id')->references('id')->on('bulletins_paie')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elements_paie');
    }
};
