<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = ['session_id','user_id','machine_id','material_selected','ai_detected_type','ai_confidence','is_valid','weight_grams','points_earned','points_deducted','image_path','idempotency_key'];
    protected $casts = ['is_demo' => 'boolean'];

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
    public function session() { return $this->belongsTo(RecyclingSession::class, 'session_id'); }
    public function user()    { return $this->belongsTo(User::class); }
    public function machine() { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
}
