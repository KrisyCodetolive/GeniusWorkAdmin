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
        Schema::create('security_keys', function (Blueprint $table) {
            $table->id();
            $table->text('key_encrypted')->comment('Clé chiffrée');
            $table->timestamp('generated_at')->useCurrent()->comment('Date de génération');
            $table->timestamp('expires_at')->nullable()->comment('Date d\'expiration');
            $table->boolean('is_active')->default(true)->comment('Statut d\'activité');
            $table->string('generated_by')->nullable()->comment('ID de l\'utilisateur ayant généré la clé');
            $table->timestamps();
            
            // Index pour accélérer les recherches
            $table->index('is_active');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_keys');
    }
};
