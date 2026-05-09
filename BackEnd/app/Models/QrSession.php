<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QrSession extends Model {
    protected $table    = 'qr_sessions';
    protected $fillable = ['machine_id','qr_token','kiosk_token','status','scanned_by','expires_at'];
    protected $casts    = ['expires_at' => 'datetime'];
    public function machine()     { return $this->belongsTo(RvmMachine::class, 'machine_id'); }
    public function scannedUser() { return $this->belongsTo(User::class, 'scanned_by'); }
}
