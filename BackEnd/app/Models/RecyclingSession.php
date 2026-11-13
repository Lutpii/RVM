<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecyclingSession extends Model {
    // How long an item already in flight may still finish after the session ended.
    public const IN_FLIGHT_GRACE_SECONDS = 120;

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

    // Totals recalculated from the session's saved transactions.
    public function refreshTotalsFromTransactions(int $endPoints): void
    {
        $txns       = $this->transactions()->get();
        $earned     = $txns->where('is_valid', true)->sum('points_earned');
        $deducted   = $txns->where('is_valid', false)->sum('points_deducted');
        $this->update([
            'end_points'    => $endPoints,
            'points_earned' => $earned - $deducted,
            'total_items'   => $txns->where('is_valid', true)->count(),
        ]);
    }

    // The kiosk token of this session's scanned QR lives only for the grace
    // window after the session ends (the QR row's expires_at is the token TTL).
    // Matches every still-live 'scanned' QR of this user on this machine.
    public function endKioskToken(): void
    {
        QrSession::where('scanned_by', $this->user_id)
            ->where('machine_id', $this->machine_id)
            ->where('status', 'scanned')
            ->update(['expires_at' => now()->addSeconds(self::IN_FLIGHT_GRACE_SECONDS)]);
    }
}
