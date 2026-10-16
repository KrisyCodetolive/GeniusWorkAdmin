<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Changements à envoyer à l'autre instance (vidée par sync:pousser)
        Schema::create('sync_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('record_id');
            $table->unsignedInteger('tentatives')->default(0);
            $table->text('derniere_erreur')->nullable();
            $table->timestamp('envoye_at')->nullable();
            $table->timestamps();

            $table->index(['envoye_at', 'id']);
            $table->index(['table_name', 'record_id']);
        });

        // Dernier updated_at reçu par table lors de la récupération depuis le cloud
        Schema::create('sync_curseurs', function (Blueprint $table) {
            $table->string('table_name')->primary();
            $table->timestamp('since')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_curseurs');
        Schema::dropIfExists('sync_outbox');
    }
};
