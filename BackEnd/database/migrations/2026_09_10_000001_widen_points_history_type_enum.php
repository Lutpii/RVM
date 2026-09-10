<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE points_history MODIFY COLUMN type ENUM('earned', 'deducted', 'redeemed') NOT NULL");
    }

    public function down(): void
    {
        // Strict mode rejects narrowing the enum while any 'redeemed' row exists
        // (Data truncated for column 'type'); remap first so rollback never fails
        // or silently corrupts rows once the feature has actually been used.
        DB::statement("UPDATE points_history SET type = 'deducted' WHERE type = 'redeemed'");
        DB::statement("ALTER TABLE points_history MODIFY COLUMN type ENUM('earned', 'deducted') NOT NULL");
    }
};
