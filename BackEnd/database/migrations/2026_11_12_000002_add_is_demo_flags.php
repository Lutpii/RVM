<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Demo kiosk: a machine can be marked demo, and the sessions and transactions
// recorded on it inherit the flag (set by the models' creating hooks) so demo
// data stays real in the database but can be filtered apart from real data.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false);
        });
        Schema::table('recycling_sessions', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_demo')->default(false)->index();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['is_demo']);
            $table->dropColumn('is_demo');
        });
        Schema::table('recycling_sessions', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
        Schema::table('rvm_machines', function (Blueprint $table) {
            $table->dropColumn('is_demo');
        });
    }
};
