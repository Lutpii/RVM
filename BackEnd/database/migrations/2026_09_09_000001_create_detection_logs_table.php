<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detection_logs', function (Blueprint $table) {
            $table->id();
            $table->string('image_path')->nullable();
            $table->string('ai_detected_type', 20)->nullable();
            $table->decimal('ai_confidence', 5, 4)->nullable();
            $table->boolean('is_mock')->default(false);
            $table->boolean('is_guest')->default(false);
            $table->foreignId('session_id')->nullable()->constrained('recycling_sessions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained('rvm_machines')->nullOnDelete();
            $table->boolean('ground_truth_correct')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detection_logs');
    }
};
