<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One ALTER per column, like the 2026_09_1x migrations — TiDB (the
        // cloud DB) rejects some multi-column ALTERs that MySQL accepts.
        // voucher_code stays null on cash redemptions (no voucher to show).
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->string('voucher_code', 20)->nullable()->unique();
        });
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable();
        });
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reward_redemptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('used_by');
            $table->dropColumn(['voucher_code', 'used_at']);
        });
    }
};
