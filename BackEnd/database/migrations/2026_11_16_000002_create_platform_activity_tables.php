<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compactor_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->constrained('rvm_machines')->restrictOnDelete();
            $table->unsignedInteger('plastic_count');
            $table->unsignedInteger('aluminum_count');
            $table->unsignedInteger('non_target_count')->default(0);
            $table->unsignedInteger('points_earned');
            $table->decimal('carbon_kg', 10, 3);
            $table->boolean('is_demo')->default(true);
            $table->timestamps();
            $table->index(['machine_id', 'created_at']);
        });

        Schema::create('composter_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->constrained('rvm_machines')->restrictOnDelete();
            $table->decimal('weight_kg', 8, 2);
            $table->unsignedInteger('points_earned');
            $table->decimal('carbon_kg', 10, 3);
            $table->boolean('is_demo')->default(true);
            $table->timestamps();
            $table->index(['machine_id', 'created_at']);
        });

        Schema::table('points_history', function (Blueprint $table) {
            $table->string('source', 20)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->index(['source', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('points_history', function (Blueprint $table) {
            $table->dropIndex(['source', 'source_id']);
            $table->dropColumn(['source', 'source_id']);
        });
        Schema::dropIfExists('composter_deposits');
        Schema::dropIfExists('compactor_batches');
    }
};
