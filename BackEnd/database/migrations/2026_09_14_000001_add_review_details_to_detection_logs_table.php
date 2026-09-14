<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detection_logs', function (Blueprint $table) {
            $table->string('ground_truth_label', 20)->nullable()->after('ground_truth_correct');
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
