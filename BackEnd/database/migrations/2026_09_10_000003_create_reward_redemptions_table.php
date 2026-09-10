<?php
// BackEnd/database/migrations/2026_09_10_000003_create_reward_redemptions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reward_item_id')->nullable()->constrained('reward_items')->nullOnDelete();
            $table->string('reward_name', 150);
            $table->unsignedInteger('points_spent');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};
