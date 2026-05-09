<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
    protected $fillable = ['session_id','user_id','machine_id','material_selected','ai_detected_type','ai_confidence','is_valid','weight_grams','points_earned','points_deducted','image_path'];
    public function session() { return $this->belongsTo(RecyclingSession::class, 'session_id'); }
    public function user()    { return $this->belongsTo(User::class); }
    public function machine() { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
}
