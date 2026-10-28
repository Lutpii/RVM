<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One ALTER per column: TiDB (the cloud DB) rejects ->after() on a
        // column added earlier in the same statement; MySQL doesn't mind.
        Schema::table('detection_logs', function (Blueprint $table) {
            $table->string('ground_truth_label', 20)->nullable()->after('ground_truth_correct');
        });
        Schema::table('detection_logs', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->after('ground_truth_label')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detection_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('ground_truth_label');
        });
    }
};
