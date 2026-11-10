<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A kiosk retry after a lost response sends the same key and gets the
    // first result back instead of an error or a second credit (U04, §6).
    // One change per ALTER (TiDB).
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable();
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->unique(['session_id', 'idempotency_key'], 'transactions_session_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('transactions_session_idempotency_unique');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('idempotency_key');
        });
    }
};
