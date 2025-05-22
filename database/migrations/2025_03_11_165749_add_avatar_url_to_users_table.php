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
        Schema::table('users', function (Blueprint $table) {
            // Vérifier si la colonne photo existe déjà
            if (!Schema::hasColumn('users', config('filament-edit-profile.avatar_column', 'photo'))) {
                $table->string(config('filament-edit-profile.avatar_column', 'photo'))->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ne pas supprimer la colonne photo car elle existait déjà dans l'application
        // Nous la conservons pour maintenir la compatibilité
    }
};
