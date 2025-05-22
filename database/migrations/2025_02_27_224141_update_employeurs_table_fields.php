<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employeurs', function (Blueprint $table) {
            // Renommer sexe en genre
            $table->renameColumn('sexe', 'genre');
            
            // Renommer fonction en poste
            $table->renameColumn('fonction', 'poste');
            
            // Ajouter meta_donnees
            $table->json('meta_donnees')->nullable()->after('configuration');
        });
    }

    public function down()
    {
        Schema::table('employeurs', function (Blueprint $table) {
            $table->renameColumn('genre', 'sexe');
            $table->renameColumn('poste', 'fonction');
            $table->dropColumn('meta_donnees');
        });
    }
};
