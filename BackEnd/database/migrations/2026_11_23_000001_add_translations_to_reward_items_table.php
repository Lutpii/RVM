<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Reward cards are written in Malay (name / description / category). These
// optional columns hold the English and Indonesian versions; an empty one
// falls back to the Malay text on the page.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reward_items', function (Blueprint $table) {
            $table->string('name_en', 150)->nullable()->after('name');
            $table->string('name_id', 150)->nullable()->after('name_en');
            $table->text('description_en')->nullable()->after('description');
            $table->text('description_id')->nullable()->after('description_en');
            $table->string('category_en', 50)->nullable()->after('category');
            $table->string('category_id', 50)->nullable()->after('category_en');
        });
    }

    public function down(): void
    {
        Schema::table('reward_items', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_id', 'description_en', 'description_id', 'category_en', 'category_id']);
        });
    }
};
