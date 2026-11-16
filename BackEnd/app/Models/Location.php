<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name', 'latitude', 'longitude'];

    public function machines(): HasMany
    {
        return $this->hasMany(RvmMachine::class);
    }
}
