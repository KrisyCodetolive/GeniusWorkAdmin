<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignUuid('entreprise_id')->nullable()->constrained();
            $table->string('description')->nullable();
            $table->json('meta_data')->nullable();
            $table->boolean('is_system')->default(false);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->softDeletes();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->string('groupe')->nullable();
            $table->json('meta_data')->nullable();
            $table->boolean('is_system')->default(false);
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['entreprise_id']);
            $table->dropColumn([
                'entreprise_id',
                'description',
                'meta_data',
                'is_system',
                'statut',
                'deleted_at'
            ]);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'groupe',
                'meta_data',
                'is_system',
                'statut',
                'deleted_at'
            ]);
        });
    }
};
