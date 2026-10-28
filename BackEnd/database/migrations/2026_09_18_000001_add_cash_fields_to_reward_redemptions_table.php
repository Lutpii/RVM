<?php
// BackEnd/database/migrations/2026_09_18_000001_add_cash_fields_to_reward_redemptions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One ALTER per column: TiDB (the cloud DB) rejects ->after() on a
        // column added earlier in the same statement; MySQL doesn't mind.
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->decimal('cash_amount_rm', 10, 2)->nullable()->after('points_spent');
        });
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->string('ewallet_provider', 50)->nullable()->after('cash_amount_rm');
        });
        Schema::table('reward_redemptions', function (Blueprint $table) {
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
