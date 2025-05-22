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
        Schema::table('abonnements', function (Blueprint $table) {
            $table->foreignId('code_promo_id')->nullable()->after('plan_abonnement_id')->constrained('code_promos')->nullOnDelete();
            $table->decimal('reduction_code_promo', 5, 2)->nullable()->after('montant')->comment('Pourcentage de réduction appliqué par le code promo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('abonnements', function (Blueprint $table) {
            $table->dropForeign(['code_promo_id']);
            $table->dropColumn(['code_promo_id', 'reduction_code_promo']);
        });
    }
};
