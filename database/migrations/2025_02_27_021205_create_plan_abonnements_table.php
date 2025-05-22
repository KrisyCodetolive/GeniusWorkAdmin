<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('plan_abonnements')) {
            Schema::create('plan_abonnements', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->decimal('prix_mensuel', 10, 2);
                $table->decimal('prix_annuel', 10, 2)->nullable();
                $table->integer('duree_essai')->default(0);
                $table->integer('nombre_employes_min')->nullable();
                $table->integer('nombre_employes_max')->nullable();
                $table->decimal('cout_par_employe', 10, 2)->nullable();
                $table->string('devise', 3)->default('XOF');
                $table->integer('priorite')->default(0);
                $table->enum('statut', ['actif', 'inactif'])->default('actif');
                $table->json('fonctionnalites')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('plan_abonnements');
    }
};
