<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password_hash')->nullable();
            $table->string('google_id')->nullable()->unique();
            $table->string('avatar_url')->nullable();
            $table->unsignedInteger('total_points')->default(0);
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->tinyInteger('is_verified')->default(0);
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('preferred_language')->nullable();
            $table->string('theme_preference')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
