<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Small, readable user numbers for the admin (USR-0042) in registration
// order. TiDB hands out AUTO_INCREMENT ids in ~30000-id batches per node, so
// users.id is neither small nor in time order: number by created_at instead.
// New accounts get theirs in User::assignDisplayNo(). Never renumbered.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('display_no')->nullable()->after('id');
        });

        $this->backfill();

        Schema::table('users', function (Blueprint $table) {
            $table->unique('display_no', 'users_display_no_unique');
        });
    }

    // Public so a test can run it on rows inserted after the migration.
    public function backfill(): void
    {
        $next = (int) DB::table('users')->max('display_no') + 1;

        DB::table('users')->whereNull('display_no')
            ->orderBy('created_at')->orderBy('id')
            ->select('id')
            ->get()
            ->each(function ($row) use (&$next) {
                DB::table('users')->where('id', $row->id)->update(['display_no' => $next++]);
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_display_no_unique');
            $table->dropColumn('display_no');
        });
    }
};
