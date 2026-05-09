<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RecyclingSession extends Model {
    protected $table    = 'recycling_sessions';
    protected $fillable = ['session_code','user_id','machine_id','status','start_points','end_points','points_earned','total_items','started_at','ended_at'];
    protected $casts    = ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    public function user()         { return $this->belongsTo(User::class); }
    public function machine()      { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
    public function transactions() { return $this->hasMany(Transaction::class, 'session_id'); }
}
