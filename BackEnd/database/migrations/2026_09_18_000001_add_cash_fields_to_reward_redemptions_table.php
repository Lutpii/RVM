<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->decimal('cash_amount_rm', 10, 2)->nullable()->after('points_spent');
            $table->string('ewallet_provider', 50)->nullable()->after('cash_amount_rm');
            $table->string('ewallet_account', 50)->nullable()->after('ewallet_provider');
        });
    }

    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropColumn(['cash_amount_rm', 'ewallet_provider', 'ewallet_account']);
        });
    }
};
