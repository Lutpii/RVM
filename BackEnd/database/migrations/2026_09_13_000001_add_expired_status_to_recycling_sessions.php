<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE recycling_sessions MODIFY COLUMN status ENUM('active', 'completed', 'expired') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Strict mode rejects narrowing the enum while any 'expired' row exists
        // (Data truncated for column 'status'); remap first so rollback never
        // fails or silently corrupts rows once the feature has actually run.
        DB::statement("UPDATE recycling_sessions SET status = 'completed' WHERE status = 'expired'");
        DB::statement("ALTER TABLE recycling_sessions MODIFY COLUMN status ENUM('active', 'completed') NOT NULL DEFAULT 'active'");
    }
};
