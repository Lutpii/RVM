<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reward photos live in the database, not on disk: the free cloud host
        // (Render) wipes its disk on every deploy. A separate table keeps the
        // bytes out of every reward_items list query.
        Schema::create('reward_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_item_id')->unique()->constrained('reward_items')->cascadeOnDelete();
            $table->string('mime', 50);
            $table->timestamps();
        });
        // Laravel 10's binary() is a 64 KB BLOB — too small for a cropped 800x600 JPEG.
        DB::statement('ALTER TABLE reward_item_images ADD data MEDIUMBLOB NOT NULL');

        // Set when a database image is saved: lets image_url be built (and
        // cache-busted) without loading the image row.
        Schema::table('reward_items', function (Blueprint $table) {
            $table->timestamp('image_stored_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reward_items', function (Blueprint $table) {
            $table->dropColumn('image_stored_at');
        });
        Schema::dropIfExists('reward_item_images');
    }
};
