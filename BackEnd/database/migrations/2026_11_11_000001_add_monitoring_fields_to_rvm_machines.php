<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One ALTER per column — TiDB (the cloud DB) rejects some multi-column
        // ALTERs that MySQL accepts. These back machine auth + heartbeat (A02/A05).
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->string('api_key_hash', 64)->nullable()->unique();
        });
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable();
        });
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->boolean('fault_active')->default(false);
        });
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->string('fault_reason', 255)->nullable();
        });
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->timestamp('fault_since')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->dropUnique(['api_key_hash']);
        });
        foreach (['api_key_hash', 'last_seen_at', 'fault_active', 'fault_reason', 'fault_since'] as $col) {
            Schema::table('rvm_machines', function (Blueprint $table) use ($col) {
                $table->dropColumn($col);
            });
        }
    }
};
