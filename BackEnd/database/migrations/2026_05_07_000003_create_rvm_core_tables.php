<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // qr_sessions
        if (!Schema::hasTable('qr_sessions')) {
            Schema::create('qr_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('machine_id')->constrained('rvm_machines')->onDelete('cascade');
                $table->string('qr_token', 80)->unique();
                $table->enum('status', ['pending', 'scanned', 'expired'])->default('pending');
                $table->unsignedBigInteger('scanned_by')->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();
            });
        }

        // recycling_sessions
        if (!Schema::hasTable('recycling_sessions')) {
            Schema::create('recycling_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_code', 20)->unique();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('machine_id')->constrained('rvm_machines')->onDelete('cascade');
                $table->enum('status', ['active', 'completed'])->default('active');
                $table->integer('start_points')->default(0);
                $table->integer('end_points')->default(0);
                $table->integer('points_earned')->default(0);
                $table->integer('total_items')->default(0);
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->timestamps();
            });
        }

        // transactions
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('recycling_sessions')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('machine_id')->constrained('rvm_machines')->onDelete('cascade');
                $table->string('material_selected', 20);
                $table->string('ai_detected_type', 20)->nullable();
                $table->decimal('ai_confidence', 5, 4)->nullable();
                $table->boolean('is_valid')->default(false);
                $table->integer('weight_grams')->default(0);
                $table->integer('points_earned')->default(0);
                $table->integer('points_deducted')->default(0);
                $table->string('image_path')->nullable();
                $table->timestamps();
            });
        }

        // points_history
        if (!Schema::hasTable('points_history')) {
            Schema::create('points_history', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->unsignedBigInteger('transaction_id')->nullable();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->integer('points_change');
                $table->integer('balance_after');
                $table->enum('type', ['earned', 'deducted']);
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // admin_logs
        if (!Schema::hasTable('admin_logs')) {
            Schema::create('admin_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
                $table->string('action');
                $table->string('target_type')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->json('details')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('points_history');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('recycling_sessions');
        Schema::dropIfExists('qr_sessions');
    }
};
