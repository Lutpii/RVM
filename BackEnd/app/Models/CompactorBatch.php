<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompactorBatch extends Model
{
    protected $fillable = [
        'user_id', 'machine_id', 'plastic_count', 'aluminum_count', 'non_target_count',
        'points_earned', 'carbon_kg', 'is_demo', 'created_at',
    ];

    protected $casts = ['carbon_kg' => 'decimal:3', 'is_demo' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(RvmMachine::class, 'machine_id');
    }
}
