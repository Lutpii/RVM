<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Reward cards are written in Malay (name / description / category). These
// optional columns hold the English and Indonesian versions; an empty one
// falls back to the Malay text on the page.
//
// No ->after(): production runs on TiDB, which rejects an ALTER that places a
// new column after another column added in the same statement (MySQL allows
// it). Each column is added on its own and skipped if it already exists, so a
// retried deploy never trips over a half-applied run.
return new class extends Migration
{
    private const COLUMNS = [
        'name_en'        => ['string', 150],
        'name_id'        => ['string', 150],
        'description_en' => ['text', null],
        'description_id' => ['text', null],
        'category_en'    => ['string', 50],
        'category_id'    => ['string', 50],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $column => [$type, $length]) {
            if (Schema::hasColumn('reward_items', $column)) {
                continue;
            }
            Schema::table('reward_items', function (Blueprint $table) use ($column, $type, $length) {
                $type === 'text'
                    ? $table->text($column)->nullable()
                    : $table->string($column, $length)->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::COLUMNS) as $column) {
            if (Schema::hasColumn('reward_items', $column)) {
                Schema::table('reward_items', fn (Blueprint $table) => $table->dropColumn($column));
            }
        }
    }
};
