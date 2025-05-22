<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parametres_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('entreprise_id')->constrained()->cascadeOnDelete();
            $table->boolean('notifier_absences')->default(true);
            $table->boolean('notifier_retards')->default(true);
            $table->boolean('notifier_conges')->default(true);
            $table->boolean('notifier_heures_supplementaires')->default(true);
            $table->boolean('notifier_permutations')->default(true);
            $table->boolean('activer_notifications_email')->default(true);
            $table->boolean('activer_notifications_sms')->default(false);
            $table->boolean('activer_notifications_push')->default(true);
            $table->json('modeles_email')->nullable();
            $table->json('modeles_sms')->nullable();
            $table->json('configuration_email')->nullable();
            $table->json('configuration_sms')->nullable();
            $table->json('destinataires_supplementaires')->nullable();
            $table->json('regles_notification')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('entreprise_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('parametres_notifications');
    }
};
