<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('entreprise_id')->nullable();
            $table->string('notification_type');
            $table->string('phone_number');
            $table->text('message');
            $table->string('status'); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->uuid('notifiable_id')->nullable();
            $table->string('notifiable_type')->nullable();
            $table->timestamps();

            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('set null');
            $table->index(['status', 'attempts']);
            $table->index(['notifiable_id', 'notifiable_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sms_logs');
    }
};
