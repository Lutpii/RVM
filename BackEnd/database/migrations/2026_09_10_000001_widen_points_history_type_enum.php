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
        DB::statement("ALTER TABLE points_history MODIFY COLUMN type ENUM('earned', 'deducted') NOT NULL");
    }
};
