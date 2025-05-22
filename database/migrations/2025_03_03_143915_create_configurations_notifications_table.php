<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('configurations_notifications')) {
            Schema::create('configurations_notifications', function (Blueprint $table) {
                $table->id();
                $table->uuid('entreprise_id');
                $table->string('type', 50); // retard, absence, sortie_manquante, conge_approuve, conge_refuse, etc.
                $table->text('message_template');
                $table->boolean('actif')->default(true);
                $table->boolean('email_actif')->default(false);
                $table->boolean('sms_actif')->default(false);
                $table->boolean('app_actif')->default(true);
                $table->timestamps();
                
                $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
                $table->unique(['entreprise_id', 'type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configurations_notifications');
    }
};
