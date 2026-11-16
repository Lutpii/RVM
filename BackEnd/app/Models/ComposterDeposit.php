<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComposterDeposit extends Model
{
    protected $fillable = [
        'user_id', 'machine_id', 'weight_kg', 'points_earned', 'carbon_kg', 'is_demo', 'created_at',
    ];

    protected $casts = ['weight_kg' => 'decimal:2', 'carbon_kg' => 'decimal:3', 'is_demo' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(RvmMachine::class, 'machine_id');
    }
}
