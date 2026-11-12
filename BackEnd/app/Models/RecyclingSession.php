<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecyclingSession extends Model {
    protected $table    = 'recycling_sessions';
    protected $fillable = ['session_code','user_id','machine_id','status','start_points','end_points','points_earned','total_items','started_at','ended_at'];
    protected $casts    = ['started_at' => 'datetime', 'ended_at' => 'datetime', 'is_demo' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->is_demo = (bool) ($model->machine_id && RvmMachine::find($model->machine_id)?->is_demo);
        });
    }

    // 'real' -> non-demo rows, 'demo' -> demo rows, anything else -> no filter.
    public function scopeDataScope(Builder $query, string $scope): Builder
    {
        return match ($scope) {
            'real'  => $query->where('is_demo', false),
            'demo'  => $query->where('is_demo', true),
            default => $query,
        };
    }
    public function user()         { return $this->belongsTo(User::class); }
    public function machine()      { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
    public function transactions() { return $this->hasMany(Transaction::class, 'session_id'); }
}
