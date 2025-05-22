<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('adresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('adressable');
            $table->string('type_adresse')->default('principale');
            $table->string('nom_rue')->nullable();
            $table->string('numero_rue')->nullable();
            $table->string('complement')->nullable();
            $table->string('quartier')->nullable();
            $table->string('ville');
            $table->string('code_postal')->nullable();
            $table->string('region')->nullable();
            $table->string('pays')->default('Cameroun');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('est_principale')->default(false);
            $table->boolean('est_facturation')->default(false);
            $table->boolean('est_livraison')->default(false);
            $table->json('meta_donnees')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('adresses');
    }
};
