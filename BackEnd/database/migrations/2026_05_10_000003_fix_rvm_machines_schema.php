<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make location_name nullable and add 'maintenance' to status enum
        DB::statement("ALTER TABLE rvm_machines MODIFY COLUMN location_name VARCHAR(255) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE rvm_machines MODIFY COLUMN status ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE rvm_machines SET status = 'inactive' WHERE status = 'maintenance'");
        DB::statement("ALTER TABLE rvm_machines MODIFY COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active'");
        DB::statement("ALTER TABLE rvm_machines MODIFY COLUMN location_name VARCHAR(255) NOT NULL DEFAULT ''");
    }
};
