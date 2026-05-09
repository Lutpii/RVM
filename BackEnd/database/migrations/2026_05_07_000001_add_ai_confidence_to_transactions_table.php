<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) return;
        if (Schema::hasColumn('transactions', 'ai_confidence')) return;

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('ai_confidence', 5, 4)->nullable()->after('ai_detected_type');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('transactions')) return;
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('ai_confidence');
        });
    }
};
